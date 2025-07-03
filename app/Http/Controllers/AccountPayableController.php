<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\AccountPayable\ProcessPaymentRequest;

class AccountPayableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Supplier::join('events', 'suppliers.event_id', '=', 'events.id')
                ->join('currencies', 'suppliers.currency_id', '=', 'currencies.id')
                ->select('suppliers.*', 'events.name as event_name', 'currencies.name as currency_name');
            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('suppliers.name', 'like', "%{$search}%")
                                ->orWhere('events.name', 'like', "%{$search}%")
                                ->orWhere('currencies.name', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('saldo', function ($row) {
                    // obtiene la suma de lo abonado de la tabla club_payments
                    $saldo = SupplierPayment::where('supplier_id', $row->id)->sum('amount');
                    return $saldo;
                })
                ->addColumn('pendiente', function ($row) {
                    $saldo = SupplierPayment::where('supplier_id', $row->id)->sum('amount');
                    return $row->amount - $saldo;
                })
                ->addColumn('actions', function ($row) {
                    $pendiente = $row->amount - SupplierPayment::where('supplier_id', $row->id)->sum('amount');
                    return view('account-payable.actions', compact('row', 'pendiente'));
                })
                ->make(true);
        }
        $paymentMethods = MethodPayment::all();
        // obtener los totales pendientes por moneda y las que no tengan datos declararlas en cero
        $totalesPendientes = Currency::leftJoin('suppliers', 'currencies.id', '=', 'suppliers.currency_id')
            ->leftJoin('supplier_payments', 'suppliers.id', '=', 'supplier_payments.supplier_id')
            ->select(
                'currencies.name as currency_name',
                DB::raw('COALESCE(SUM(suppliers.amount - IFNULL((SELECT SUM(amount) FROM supplier_payments sp WHERE sp.supplier_id = suppliers.id), 0)), 0) as total_pendiente')
            )
            ->groupBy('currencies.name')
            ->get();
        return view('account-payable.index', compact('paymentMethods', 'totalesPendientes'));
    }

    public function processPayment(ProcessPaymentRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Obtener el proveedor
            $supplier = Supplier::findOrFail($request->supplier_id);
            
            // Obtener el método de pago
            $methodPayment = MethodPayment::findOrFail($request->method_payment_id);
            
            // Validar que las monedas coincidan
            if ($supplier->currency_id !== $methodPayment->currency_id) {
                DB::rollback();
                throw new \Exception('La moneda del proveedor no coincide con la moneda del método de pago');
            }
            
            // Validar saldo suficiente en el método de pago
            if ($methodPayment->current_balance < $request->amount) {
                DB::rollback();
                throw new \Exception('El método de pago no tiene saldo suficiente');
            }
            
            // Validar que el monto no exceda el saldo pendiente
            $saldoPagado = SupplierPayment::where('supplier_id', $supplier->id)->sum('amount');
            $saldoPendiente = $supplier->amount - $saldoPagado;
            
            if ($request->amount > $saldoPendiente) {
                DB::rollback();
                throw new \Exception('El monto excede el saldo pendiente con este proveedor');
            }
            
            // Registrar el pago
            $supplierPayment = SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'currency_id' => $supplier->currency_id,
                'date' => $request->date ?? now()->format('Y-m-d'),
                'amount' => $request->amount,
                'method_payment_id' => $methodPayment->id
            ]);
            
            // Registrar en EventMovement
            EventMovement::create([
                'event_id' => $supplier->event_id,
                'supplier_id' => $supplier->id,
                'method_payment_id' => $methodPayment->id,
                'category_expense_id' => 1, // ID fijo para pagos a proveedores
                'currency_id' => $supplier->currency_id,
                'amount' => $request->amount,
                'date' => now()->format('Y-m-d'),
                'description' => $request->description ?? "Pago a proveedor {$supplier->name}",
                'type' => 'Egreso'
            ]);
            
            // Actualizar saldo del método de pago
            $methodPayment->current_balance -= $request->amount;
            $methodPayment->save();
            
            DB::commit();
            
            return redirect()->route('account-payable.index')->with('success', 'Pago procesado correctamente');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('account-payable.index')->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }
}
