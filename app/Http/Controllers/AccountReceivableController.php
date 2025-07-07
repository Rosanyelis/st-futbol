<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Bussines;
use App\Models\Currency;
use App\Models\ClubPayment;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryIncome;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\AccountReceivable\ProcessPaymentRequest;

class AccountReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Club::join('events', 'clubs.event_id', '=', 'events.id')
                ->join('currencies', 'clubs.currency_id', '=', 'currencies.id')
                ->select('clubs.*', 'events.name as event_name', 'currencies.name as currency_name');
            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('clubs.name', 'like', "%{$search}%")
                                ->orWhere('events.name', 'like', "%{$search}%")
                                ->orWhere('currencies.name', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('saldo', function ($row) {
                    // obtiene la suma de lo abonado de la tabla club_payments
                    $saldo = ClubPayment::where('club_id', $row->id)->sum('amount');
                    return $saldo;
                })
                ->addColumn('pendiente', function ($row) {
                    $saldo = ClubPayment::where('club_id', $row->id)->sum('amount');
                    return $row->total_amount - $saldo;
                })
                ->addColumn('actions', function ($row) {
                    $saldo = ClubPayment::where('club_id', $row->id)->sum('amount');
                    $pendiente = $row->total_amount - $saldo;
                    return view('account-receivable.actions', compact('row', 'pendiente'));
                })

                ->make(true);
        }

        $paymentMethods = MethodPayment::all();
        // obtener los totales pendientes por moneda y las que no tengan datos declararlas en cero
        $currencies = Currency::all();
        return view('account-receivable.index', compact('paymentMethods', 'currencies'));
    }


    /**
     * Procesa el pago de una cuenta por cobrar
     */
    public function processPayment(ProcessPaymentRequest $request)
    {
        
        DB::beginTransaction();
        
        try {
            
            // Obtener el club y validar que exista
            $club = Club::findOrFail($request->club_id);
            
            // Obtener el método de pago y validar que exista
            $methodPayment = MethodPayment::findOrFail($request->method_payment_id);
            
            // Validar que las monedas coincidan
            if ($club->currency_id !== $methodPayment->currency_id) {
                DB::rollback();
                throw new \Exception('La moneda del club no coincide con la moneda del método de pago');
            }

            // Calcular el saldo actual del club
            $saldoPagado = ClubPayment::where('club_id', $club->id)->sum('amount');
            $saldoPendiente = $club->total_amount - $saldoPagado;

            // Validar que el monto no exceda el saldo pendiente
            if ($request->amount > $saldoPendiente) {
                DB::rollback();
                return $this->respondWithError($request, 'El monto del pago excede el saldo pendiente de la cuenta');
            }

            // Crear el registro de pago del club
            $clubPayment = ClubPayment::create([
                'club_id' => $club->id,
                'date' => $request->date,
                'currency_id' => $club->currency_id,
                'method_payment_id' => $request->method_payment_id,
                'amount' => $request->amount,
                'description' => $request->description ?? "Pago de cuenta por cobrar del club {$club->name}",
            ]);

    

            // Crear el registro en EventMovement usando category_income_id = 1 por defecto para pagos de club
            $eventMovement = EventMovement::create([
                'event_id' => $club->event_id,
                'club_id' => $club->id,
                'method_payment_id' => $request->method_payment_id,
                'category_income_id' => 1, // ID fijo para pagos de club
                'currency_id' => $club->currency_id,
                'amount' => $request->amount,
                'date' => now()->format('Y-m-d'),
                'description' => $request->description ?? "Pago de cuenta por cobrar del club {$club->name}",
                'type' => 'Ingreso',
            ]);

            // Actualizar el saldo actual del método de pago
            $methodPayment->current_balance = ($methodPayment->current_balance ?? 0) + $request->amount;
            $methodPayment->save();

            DB::commit();

            return redirect()->route('account-receivable.index')->with('success', 'Pago procesado correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->route('account-receivable.index')->with('error', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

}
