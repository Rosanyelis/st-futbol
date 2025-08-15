<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Bussines;
use App\Models\Currency;
use App\Models\ClubPayment;
use App\Models\ClubAccountReceivable;
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
            $data = ClubAccountReceivable::with(['club', 'event', 'currency'])
                ->select('club_account_receivables.*');
                
            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->whereHas('club', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })->orWhereHas('event', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })->orWhereHas('currency', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }

                    // Filtro por evento
                    if ($request->filled('event_id')) {
                        $query->where('event_id', $request->get('event_id'));
                    }

                    // Filtro por estado
                    if ($request->filled('status')) {
                        $query->where('status', $request->get('status'));
                    }
                })
                ->addColumn('club_name', function ($row) {
                    return $row->club->name ?? '';
                })
                ->addColumn('event_name', function ($row) {
                    return $row->event->name ?? '';
                })
                ->addColumn('currency_name', function ($row) {
                    return $row->currency->name ?? '';
                })
                ->addColumn('payment_percentage', function ($row) {
                    return $row->getPaymentPercentage() . '%';
                })
                ->addColumn('days_overdue', function ($row) {
                    return $row->getDaysOverdue();
                })
                ->addColumn('actions', function ($row) {
                    return view('account-receivable.actions', compact('row'));
                })
                ->rawColumns(['actions'])
                ->make(true);
        }

        $paymentMethods = MethodPayment::all();
        $currencies = Currency::all();
        $events = Event::orderBy('name', 'asc')->get();
        $statuses = ['Pendiente', 'Parcial', 'Pagado', 'Vencido'];
        
        return view('account-receivable.index', compact('paymentMethods', 'currencies', 'events', 'statuses'));
    }


    /**
     * Procesa el pago de una cuenta por cobrar
     */
    public function processPayment(ProcessPaymentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            $receivable = ClubAccountReceivable::findOrFail($data['receivable_id']);
            
            // Limpiar comas del monto antes de guardar
            $data['amount'] = str_replace(',', '', $data['amount']);

            // Verificar que el monto no exceda el pendiente
            if ($data['amount'] > $receivable->pending_amount) {
                throw new \Exception('El monto del pago no puede exceder el monto pendiente');
            }

            // Registrar el pago en la cuenta por cobrar
            $payment = $receivable->recordPayment(
                $data['amount'],
                $data['date'],
                $data['payment_reference'] ?? null,
                $data['description'] ?? null
            );

            // Actualizar el método de pago si se especifica
            if (isset($data['method_payment_id'])) {
                $payment->update(['method_payment_id' => $data['method_payment_id']]);
                
                // Actualizar el balance del método de pago
                $this->updatePaymentMethodBalance($data['method_payment_id'], $data['amount'], 'Ingreso');
            }

            // Crear el registro en EventMovement
            EventMovement::create([
                'event_id' => $receivable->event_id,
                'club_id' => $receivable->club_id,
                'method_payment_id' => $data['method_payment_id'] ?? null,
                'category_income_id' => 1, // ID fijo para pagos de club
                'currency_id' => $receivable->currency_id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'] ?? "Pago de cuenta por cobrar #{$receivable->id}",
                'type' => 'Ingreso',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago procesado correctamente',
                'data' => [
                    'pending_amount' => $receivable->pending_amount,
                    'status' => $receivable->status,
                    'payment_percentage' => $receivable->getPaymentPercentage()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualiza el balance del método de pago según el tipo de movimiento
     */
    private function updatePaymentMethodBalance($paymentMethodId, $amount, $type)
    {
        $methodPayment = MethodPayment::find($paymentMethodId);
        if ($methodPayment) {
            if ($type === 'Ingreso') {
                $methodPayment->current_balance = ($methodPayment->current_balance ?? 0) + $amount;
            } else {
                $methodPayment->current_balance = ($methodPayment->current_balance ?? 0) - $amount;
            }
            $methodPayment->save();
        }
    }
}
