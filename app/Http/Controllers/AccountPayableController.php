<?php

namespace App\Http\Controllers;

use App\Models\AccountPayable;
use App\Models\Currency;
use App\Models\Event;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\AccountPayablePayment;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\AccountPayable\ProcessPaymentRequest;
use Carbon\Carbon;

class AccountPayableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = AccountPayable::with(['supplier', 'event', 'currency', 'payments']);
            return DataTables::of($data)
                ->addColumn('event_name', function ($row) {
                    return $row->event ? $row->event->name : 'N/A';
                })
                ->addColumn('supplier_name', function ($row) {
                    return $row->supplier ? $row->supplier->name : 'N/A';
                })
                ->addColumn('currency_name', function ($row) {
                    return $row->currency ? $row->currency->name : 'N/A';
                })
                ->addColumn('paid_amount', function ($row) {
                    return $row->getPaidAmount();
                })
                ->addColumn('pending_amount', function ($row) {
                    return $row->getPendingAmount();
                })
                ->addColumn('payment_percentage', function ($row) {
                    return $row->getPaymentPercentage();
                })
                ->addColumn('status', function ($row) {
                    return $row->status ?? 'Pendiente';
                })
                ->addColumn('actions', function ($row) {
                    return view('account-payable.actions', compact('row'));
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        $paymentMethods = MethodPayment::all();
        $currencies = Currency::all();
        return view('account-payable.index', compact('paymentMethods', 'currencies'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $accountPayable = AccountPayable::with(['supplier', 'event', 'currency', 'payments'])->findOrFail($id);
        return view('account-payable.show', compact('accountPayable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = Event::all();
        $currencies = Currency::all();
        return view('account-payable.create', compact('events', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Limpiar formato de números antes de guardar
            $amount = str_replace(['.', ','], ['', '.'], $request->amount);
            
            $accountPayable = AccountPayable::create([
                'supplier_id' => $request->supplier_id,
                'event_id' => $request->event_id,
                'currency_id' => $request->currency_id,
                'date' => Carbon::now(),
                'amount' => (float) $amount,
                'description' => $request->description,
                'status' => 'Pendiente',
            ]);

            DB::commit();

            return redirect()->route('account-payable.index')
                ->with('success', 'Cuenta por pagar creada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al crear la cuenta por pagar: ' . $e->getMessage());
        }
    }

    /**
     * Obtener proveedores asignados a un evento
     */
    public function getSuppliersByEvent($eventId)
    {
        $suppliers = Supplier::whereHas('events', function($query) use ($eventId) {
            $query->where('events.id', $eventId);
        })->orderBy('name', 'asc')->get(['id', 'name']);

        return response()->json($suppliers);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $accountPayable = AccountPayable::with(['supplier', 'event', 'currency', 'payments'])->findOrFail($id);
        
        $events = Event::all();
        $currencies = Currency::all();
        
        return view('account-payable.edit', compact('accountPayable', 'events', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $accountPayable = AccountPayable::with('payments')->findOrFail($id);

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Limpiar formato de números antes de guardar
            $amount = str_replace(['.', ','], ['', '.'], $request->amount);
            
            $accountPayable->update([
                'supplier_id' => $request->supplier_id,
                'event_id' => $request->event_id,
                'currency_id' => $request->currency_id,
                'amount' => (float) $amount,
                'description' => $request->description,
            ]);

            DB::commit();

            return redirect()->route('account-payable.index')
                ->with('success', 'Cuenta por pagar actualizada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al actualizar la cuenta por pagar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $accountPayable = AccountPayable::with('payments')->findOrFail($id);
        
        // Verificar si tiene pagos
        if ($accountPayable->payments->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una cuenta por pagar que tiene pagos registrados'
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $accountPayable->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cuenta por pagar eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cuenta por pagar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processPayment(ProcessPaymentRequest $request)
    {
        // Log de inicio para debugging
        \Log::info('Iniciando procesamiento de pago', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);
        
        DB::beginTransaction();
        
        try {
            // Obtener la cuenta por pagar
            $accountPayable = AccountPayable::findOrFail($request->account_payable_id);
            \Log::info('Cuenta por pagar encontrada', ['account_payable' => $accountPayable->toArray()]);
            
            // Obtener el método de pago
            $methodPayment = MethodPayment::findOrFail($request->method_payment_id);
            \Log::info('Método de pago encontrado', ['method_payment' => $methodPayment->toArray()]);
            
            // Validar que las monedas coincidan
            if ($accountPayable->currency_id !== $methodPayment->currency_id) {
                \Log::warning('Error de monedas no coincidentes', [
                    'account_payable_currency_id' => $accountPayable->currency_id,
                    'method_payment_currency_id' => $methodPayment->currency_id
                ]);
                DB::rollback();
                throw new \Exception('La moneda de la cuenta por pagar no coincide con la moneda del método de pago');
            }
            
            // Validar saldo suficiente en el método de pago
            if ($methodPayment->current_balance < $request->amount) {
                \Log::warning('Saldo insuficiente en método de pago', [
                    'method_payment_balance' => $methodPayment->current_balance,
                    'requested_amount' => $request->amount
                ]);
                DB::rollback();
                throw new \Exception('El método de pago no tiene saldo suficiente');
            }
            
            // Validar que el monto no exceda el saldo pendiente
            $saldoPagado = $accountPayable->getPaidAmount();
            $saldoPendiente = $accountPayable->getPendingAmount();
            
            \Log::info('Saldos calculados', [
                'saldo_pagado' => $saldoPagado,
                'saldo_pendiente' => $saldoPendiente,
                'monto_solicitado' => $request->amount
            ]);
            
            if ($request->amount > $saldoPendiente) {
                \Log::warning('Monto excede saldo pendiente', [
                    'monto_solicitado' => $request->amount,
                    'saldo_pendiente' => $saldoPendiente
                ]);
                DB::rollback();
                throw new \Exception('El monto excede el saldo pendiente de esta cuenta por pagar');
            }
            
            // Crear el registro en EventMovement PRIMERO
            $eventMovement = EventMovement::create([
                'bussines_id' => 1, // ID del negocio
                'event_id' => $accountPayable->event_id,
                'supplier_id' => $accountPayable->supplier_id,
                'account_payable_id' => $accountPayable->id,
                'method_payment_id' => $methodPayment->id,
                'category_egress_id' => 2, // ID para proveedores
                'currency_id' => $accountPayable->currency_id,
                'type' => 'Egreso',
                'amount' => $request->amount,
                'description' => 'Pago a proveedor: ' . $accountPayable->supplier->name,
                'date' => $request->date ?? now()->format('Y-m-d'),
                'user_id' => auth()->id(),
            ]);
            \Log::info('Movimiento registrado en EventMovement', ['event_movement' => $eventMovement->toArray()]);

            // El pago se crea automáticamente a través del EventMovementObserver
            // Obtener el pago creado por el observer
            $accountPayablePayment = AccountPayablePayment::where('account_payable_id', $accountPayable->id)
                ->where('amount', $request->amount)
                ->where('date', $request->date ?? now()->format('Y-m-d'))
                ->latest()
                ->first();
            \Log::info('Pago creado por observer', ['account_payable_payment' => $accountPayablePayment ? $accountPayablePayment->toArray() : 'No encontrado']);
            
            // Actualizar saldo del método de pago
            $oldBalance = $methodPayment->current_balance;
            $methodPayment->update([
                'current_balance' => $methodPayment->current_balance - $request->amount
            ]);
            \Log::info('Saldo del método de pago actualizado', [
                'old_balance' => $oldBalance,
                'new_balance' => $methodPayment->current_balance,
                'amount_deducted' => $request->amount
            ]);
            
            DB::commit();
            \Log::info('Pago procesado exitosamente');
            
            return redirect()->route('account-payable.index')
                ->with('success', 'Pago procesado exitosamente');
            
        } catch (\Exception $e) {
            \Log::error('Error al procesar pago', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            DB::rollback();
            return redirect()->route('account-payable.index')
                ->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Generar PDF del detalle de cuenta por pagar
     */
    public function generatePdf($id)
    {
        $accountPayable = AccountPayable::with(['supplier', 'event', 'currency', 'payments'])->findOrFail($id);
        
        $pdf = \PDF::loadView('account-payable.pdf', compact('accountPayable'));
        
        $filename = 'cuenta-por-pagar-' . $accountPayable->id . '-' . date('Y-m-d-H-i-s') . '.pdf';
        
        return $pdf->stream($filename);
    }
}
