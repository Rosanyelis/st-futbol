<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Currency;
use App\Models\Supplier;

use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryEgress;
use App\Models\CategoryIncome;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;
use App\Http\Requests\StoreEventMovementRequest;
use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayable;
use App\Models\AccountPayablePayment;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Event::withCount(['clubs', 'suppliers'])->get();
            return DataTables::of($data)
                ->addColumn('clubs_count', function ($data) {
                    return $data->clubs_count ?? 0;
                })
                ->addColumn('suppliers_count', function ($data) {
                    return $data->suppliers_count ?? 0;
                })
                ->addColumn('actions', function ($data) {
                    return view('events.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('events.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        try {
            $data = $request->all();
            $data['url_images'] = $this->saveFile($request->file('url_images'), 'events/');
            Event::create($data);
            return redirect()->route('event.index')->with('success', 'Evento creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('event.index')->with('error', 'Error al crear el evento');
        }
    }

    public function historyJson(Request $request, $event)
    {
        if ($request->ajax()) {
                    $data = EventMovement::with([
            'club', 
            'currency', 
            'methodPayment', 
            'methodPayment.entity', 
            'supplier',
            'accountReceivablePayment',
            'accountPayablePayment'
        ])
            ->where('event_id', $event)
            ->where('status', '!=', 'Cancelado'); // Excluir movimientos cancelados

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    // Filtro por moneda desde el selector
                    if ($request->filled('currency_id')) {
                        $query->where('currency_id', $request->get('currency_id'));
                    }

                    if ($request->filled('start_date')) {
                        $query->where('date', '>=', $request->get('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->where('date', '<=', $request->get('end_date'));
                    }

                    // Búsqueda global
                    if ($request->has('search') && !empty($request->get('search')['value'])) {
                        $searchValue = $request->get('search')['value'];

                        $query->where(function ($subQuery) use ($searchValue) {
                            // Búsqueda en columnas directas de event_movements
                            $subQuery->where('date', 'like', "%{$searchValue}%")
                                     ->orWhere('type', 'like', "%{$searchValue}%")
                                     ->orWhere('amount', 'like', "%{$searchValue}%")
                                     ->orWhere('description', 'like', "%{$searchValue}%");

                            // Búsqueda en la relación 'currency'
                            $subQuery->orWhereHas('currency', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'club'
                            $subQuery->orWhereHas('club', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'supplier'
                            $subQuery->orWhereHas('supplier', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });

                            // Búsqueda en la relación 'methodPayment' y su anidada 'entity'
                            $subQuery->orWhereHas('methodPayment', function ($q) use ($searchValue) {
                                $q->where('account_holder', 'like', "%{$searchValue}%")
                                  ->orWhere('type_account', 'like', "%{$searchValue}%")
                                  ->orWhereHas('entity', function ($nested_q) use ($searchValue) {
                                      $nested_q->where('name', 'like', "%{$searchValue}%");
                                  });
                            });
                        });
                    }
                })
                ->addColumn('actions', function ($data) {
                    return view('events.history_actions', ['data' => $data]);
                })
                ->make(true);
        }
    }

    /**
     * Mostrar el historial de movimientos de un evento
     */
    public function history($event)
    {
        $event = Event::find($event);
        // Obtener clubs asignados al evento
        $clubs = $event->clubs()->orderBy('name', 'asc')->get();
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $expenses = Expense::with('categoryExpense')->get();
        $currencies = Currency::all();
        $categoryIncomes = CategoryIncome::all();
        $categoryEgress = CategoryEgress::all();
        
        return view('events.history', compact('event', 'clubs', 'suppliers', 'expenses', 'currencies', 'categoryIncomes', 'categoryEgress'));
    }

    public function paymentMethods($currencyId)
    {
        $method_payments = MethodPayment::with('entity')->where('currency_id', $currencyId)->get();
        return response()->json($method_payments);
    }

    public function currencies()
    {
        $currencies = Currency::all();
        return response()->json($currencies);
    }

    /**
     * Obtener clubs por categoría de ingreso
     */
    public function getClubsByCategory($categoryIncomeId)
    {
        if ($categoryIncomeId == 1) { // ID 1 = "Clubs"
            $eventId = request()->get('event_id');
            
            if (!$eventId) {
                return response()->json(['error' => 'Event ID es requerido'], 400);
            }
            
            $event = Event::find($eventId);
            
            if (!$event) {
                return response()->json(['error' => 'Evento no encontrado'], 404);
            }
            
            // Obtener cuentas por cobrar de clubs para este evento específico
            // Solo clubs que estén asignados al evento
            $accountReceivables = AccountReceivable::with(['club', 'currency'])
                ->where('event_id', $eventId)
                ->where('club_id', '!=', null) // Solo cuentas de clubs, no de proveedores
                ->where('status', '!=', 'Pagado') // Solo cuentas pendientes o parciales
                ->whereHas('club', function($query) use ($eventId) {
                    // Verificar que el club esté asignado al evento
                    $query->whereHas('events', function($subQuery) use ($eventId) {
                        $subQuery->where('events.id', $eventId);
                    });
                })
                ->orderBy('club_id')
                ->orderBy('created_at')
                ->get();
            
            // Transformar para mostrar cada cuenta por cobrar como una opción
            $clubsWithAccounts = [];
            foreach ($accountReceivables as $receivable) {
                $pendingAmount = $receivable->getPendingAmount();
                
                // Solo incluir cuentas con monto pendiente > 0
                if ($pendingAmount > 0) {
                    $clubsWithAccounts[] = [
                        'id' => $receivable->club_id, // Usar el ID del club para el select
                        'name' => $receivable->club->name . ' - Cuenta #' . $receivable->id . ' (Pendiente: ' . number_format($pendingAmount, 2) . ' ' . $receivable->currency->symbol . ')',
                        'club_id' => $receivable->club_id,
                        'pending_amount' => $pendingAmount,
                        'currency_symbol' => $receivable->currency->symbol,
                        'account_receivable_id' => $receivable->id
                    ];
                }
            }
            
            return response()->json($clubsWithAccounts);
        }
        
        return response()->json([]);
    }

    /**
     * Obtener gastos por categoría de egreso
     */
    public function getExpensesByCategory($categoryEgressId)
    {
        if ($categoryEgressId == 1) { // ID 1 = "Gastos"
            $expenses = Expense::with('categoryExpense')->where('category_egress_id', $categoryEgressId)->get();
            return response()->json($expenses);
        }
        
        return response()->json([]);
    }

    /**
     * Obtener proveedores por categoría de egreso
     */
    public function getSuppliersByCategory($categoryEgressId)
    {
        if ($categoryEgressId == 2) { // ID 2 = "Proveedores"
            $eventId = request()->get('event_id');
            
            if (!$eventId) {
                return response()->json(['error' => 'Event ID es requerido'], 400);
            }
            
            $event = Event::find($eventId);
            
            if (!$event) {
                return response()->json(['error' => 'Evento no encontrado'], 404);
            }
            
            // Obtener cuentas por pagar de proveedores para este evento específico
            $accountPayables = AccountPayable::with(['supplier', 'currency'])
                ->where('event_id', $eventId)
                ->where('supplier_id', '!=', null) // Solo cuentas de proveedores
                ->where('status', '!=', 'Pagado') // Solo cuentas pendientes o parciales
                ->orderBy('supplier_id')
                ->orderBy('created_at')
                ->get();
            
            // Transformar para mostrar cada cuenta por pagar como una opción
            $suppliersWithAccounts = [];
            foreach ($accountPayables as $payable) {
                $pendingAmount = $payable->getPendingAmount();
                
                // Solo incluir cuentas con monto pendiente > 0
                if ($pendingAmount > 0) {
                    $suppliersWithAccounts[] = [
                        'id' => $payable->supplier_id, // Usar el ID del proveedor para el select
                        'name' => $payable->supplier->name . ' - Cuenta #' . $payable->id . ' (Pendiente: ' . number_format($pendingAmount, 2) . ' ' . $payable->currency->symbol . ')',
                        'supplier_id' => $payable->supplier_id,
                        'pending_amount' => $pendingAmount,
                        'currency_symbol' => $payable->currency->symbol,
                        'account_payable_id' => $payable->id
                    ];
                }
            }
            
            return response()->json($suppliersWithAccounts);
        }
        
        return response()->json([]);
    }

    public function storeTransaction(Request $request, $event)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            // Limpiar comas del monto antes de guardar
            $data['amount'] = str_replace(',', '', $data['amount']);

            $data['event_id'] = $event;
            $data['category_egress_id'] = $data['type_expense'] ?? null; 
            $data['category_income_id'] = $data['type_income'] ?? null;
            $data['user_id'] = Auth::user()->id;

            // Validaciones adicionales de consistencia
            $this->validateMovementConsistency($data);
           
            // Si es un ingreso de club, usar account_receivable_id en lugar de club_id
            if ($data['type'] === 'Ingreso' && $data['type_income'] === '1' && isset($data['account_receivable_id'])) {
                // Obtener el club_id desde la cuenta por cobrar
                $accountReceivable = AccountReceivable::find($data['account_receivable_id']);
                if ($accountReceivable) {
                    $data['club_id'] = $accountReceivable->club_id;
                }
            }

            // Si es un egreso de proveedor, usar account_payable_id en lugar de supplier_id
            if ($data['type'] === 'Egreso' && $data['type_expense'] === '2' && isset($data['account_payable_id'])) {
                // Obtener el supplier_id desde la cuenta por pagar
                $accountPayable = AccountPayable::find($data['account_payable_id']);
                if ($accountPayable) {
                    $data['supplier_id'] = $accountPayable->supplier_id;
                }
            }
           
            // Crear el movimiento del evento
            $movement = EventMovement::create($data);

            // Actualizar el balance del método de pago
            if (!empty($data['method_payment_id'])) {
                $this->updatePaymentMethodBalance($data['method_payment_id'], $data['amount'], $data['type']);
            }

            // Los pagos se crean automáticamente a través del EventMovementObserver
            // No es necesario crear pagos manualmente aquí para evitar duplicación

            DB::commit();
            return redirect()->route('event.history', $event)->with('success', 'Movimiento creado correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('event.history', $event)->with('error', 'Error al crear el movimiento: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza el balance del método de pago según el tipo de movimiento
     */
    private function updatePaymentMethodBalance($paymentMethodId, $amount, $type)
    {
        $methodPayment = MethodPayment::findOrFail($paymentMethodId);
        
        // Convertir el monto a número
        $amount = floatval($amount);
        
        // Actualizar el balance según el tipo de movimiento
        if ($type === 'Ingreso') {
            $methodPayment->current_balance += $amount;
        } else if ($type === 'Egreso') {
            // Verificar si hay suficiente balance
            if ($methodPayment->current_balance < $amount) {
                throw new \Exception('Saldo insuficiente en el método de pago');
            }
            $methodPayment->current_balance -= $amount;
        }
        
        $methodPayment->save();
    }

    /**
     * Crea un movimiento de abono para el club
     */
    private function createClubPayment($data)
    {
        // Si tenemos account_receivable_id, registrar el pago en la cuenta por cobrar
        if (isset($data['account_receivable_id'])) {
            $accountReceivable = AccountReceivable::with(['club', 'event'])
                ->where('id', $data['account_receivable_id'])
                ->where('event_id', $data['event_id']) // Verificar que la cuenta pertenezca al evento
                ->first();
                
            if ($accountReceivable) {
                // Verificar que el club esté asignado al evento
                $clubBelongsToEvent = $accountReceivable->club->events()
                    ->where('events.id', $data['event_id'])
                    ->exists();
                    
                if ($clubBelongsToEvent) {
                    $payment = $accountReceivable->recordPayment(
                        $data['amount'],
                        $data['date'],
                        null, // reference
                        $data['description'] ?? null
                    );
                    
                    // Retornar el ID del pago para guardarlo en el movimiento
                    return $payment ? $payment->id : null;
                } else {
                    throw new \Exception('El club no está asignado a este evento');
                }
            } else {
                throw new \Exception('Cuenta por cobrar no encontrada o no pertenece a este evento');
            }
        }
        return null;
    }

    private function createSupplierPayment($data)
    {
        // Si tenemos account_payable_id, registrar el pago en la cuenta por pagar
        if (isset($data['account_payable_id'])) {
            $accountPayable = AccountPayable::with(['supplier', 'event'])
                ->where('id', $data['account_payable_id'])
                ->where('event_id', $data['event_id']) // Verificar que la cuenta pertenezca al evento
                ->first();
                
            if ($accountPayable) {
                // Verificar que el proveedor esté asignado al evento
                $supplierBelongsToEvent = $accountPayable->supplier->events()
                    ->where('events.id', $data['event_id'])
                    ->exists();
                    
                if ($supplierBelongsToEvent) {
                    $payment = $accountPayable->recordPayment(
                        $data['amount'],
                        $data['date'],
                        null, // reference
                        $data['description'] ?? null
                    );
                    
                    // Retornar el ID del pago para guardarlo en el movimiento
                    return $payment ? $payment->id : null;
                } else {
                    throw new \Exception('El proveedor no está asignado a este evento');
                }
            } else {
                throw new \Exception('Cuenta por pagar no encontrada o no pertenece a este evento');
            }
        }
        return null;
    }

    public function editHistory($id)
    {
        $data = EventMovement::with([
            'club', 
            'supplier', 
            'expense', 
            'currency', 
            'categoryIncome', 
            'categoryEgress', 
            'accountReceivable', 
            'accountPayable',
            'accountReceivablePayment',
            'accountPayablePayment'
        ])->find($id);

        if (!$data) {
            return response()->json(['error' => 'Movimiento no encontrado'], 404);
        }

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Event::find($id);
        return view('events.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, $event)
    {
        try {
            $event = Event::find($event);
            $data = $request->all();
            if($request->hasFile('url_images')){
                $data['url_images'] = $this->saveFile($request->file('url_images'), 'events/');
            }
            $event->update($data);
            return redirect()->route('event.index')->with('success', 'Evento actualizado correctamente');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($event)
    {
        try {
            $event = Event::find($event);
            if($event->clubs->count() > 0){
                return redirect()->route('event.index')->with('error', 'No se puede eliminar el evento porque tiene clubes asociados');
            }
            if ($event->url_images) {
                Storage::delete('public/' . $event->url_images);
            }
            $event->delete();
            return redirect()->route('event.index')->with('success', 'Evento eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('event.index')->with('error', 'Error al eliminar el evento');
        }
    }

    public function updateHistory(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $movement = EventMovement::findOrFail($id);
            $oldAmount = floatval($movement->amount);
            $oldType = $movement->type;
            $oldMethodPaymentId = $movement->method_payment_id;

            $data = $request->all();

            // Limpiar comas del monto antes de actualizar
            $data['amount'] = str_replace(',', '', $data['amount']);

            // Validar que el monto sea mayor a 0
            if (floatval($data['amount']) <= 0) {
                return redirect()->route('event.history', $movement->event_id)->with('error', 'El monto debe ser mayor a 0');
            }

            // 1. Revertir el saldo anterior si tenía método de pago
            if ($oldMethodPaymentId) {
                $methodPayment = MethodPayment::findOrFail($oldMethodPaymentId);
                if ($oldType === 'Ingreso') {
                    // Si era ingreso, restar el monto anterior
                    $methodPayment->current_balance -= $oldAmount;
                } elseif ($oldType === 'Egreso') {
                    // Si era egreso, sumar el monto anterior
                    $methodPayment->current_balance += $oldAmount;
                }
                $methodPayment->save();
            }

            // 2. Actualizar el movimiento con los nuevos datos
            $movement->update($data);

            // 3. Aplicar el nuevo saldo si tiene método de pago
            if (!empty($data['method_payment_id'])) {
                $newMethodPayment = MethodPayment::findOrFail($data['method_payment_id']);
                $newAmount = floatval($data['amount']);
                $newType = $data['type'];

                if ($newType === 'Ingreso') {
                    $newMethodPayment->current_balance += $newAmount;
                } elseif ($newType === 'Egreso') {
                    if ($newMethodPayment->current_balance < $newAmount) {
                        throw new \Exception('Saldo insuficiente en el método de pago');
                    }
                    $newMethodPayment->current_balance -= $newAmount;
                }
                $newMethodPayment->save();
            }
            // 4. Si es un ingreso de club, actualizar el pago en la cuenta por cobrar
            if ($data['type'] === 'Ingreso' && isset($data['type_income']) && $data['type_income'] === '1') {
                if ($movement->account_receivable_payment_id) {
                    // Actualizar el pago existente usando el ID guardado
                    $payment = AccountReceivablePayment::find($movement->account_receivable_payment_id);
                    if ($payment) {
                        // Log para debug
                        \Log::info('Actualizando pago de cuenta por cobrar', [
                            'payment_id' => $payment->id,
                            'old_amount' => $payment->amount,
                            'new_amount' => $data['amount'],
                            'movement_id' => $movement->id,
                            'club' => $movement->club->name ?? 'N/A'
                        ]);
                        
                        // Usar el método del modelo para actualizar el pago y el status automáticamente
                        $accountReceivable = $payment->accountReceivable;
                        $accountReceivable->updatePayment(
                            $payment->id,
                            $data['amount'],
                            $data['date'],
                            null, // reference
                            $data['description'] ?? null
                        );
                        
                        // Actualizar el movimiento con la referencia del pago
                        $movement->update(['account_receivable_payment_id' => $payment->id]);
                        
                        \Log::info('Pago de cuenta por cobrar actualizado exitosamente', [
                            'payment_id' => $payment->id,
                            'new_amount' => $payment->amount,
                            'movement_id' => $movement->id
                        ]);
                    } else {
                        // Si no se encuentra el pago, el observer se encargará de crearlo
                        \Log::warning('No se encontró el pago de cuenta por cobrar, el observer lo creará', [
                            'payment_id' => $movement->account_receivable_payment_id,
                            'movement_id' => $movement->id
                        ]);
                    }
                } else {
                    // El observer se encargará de crear el pago automáticamente
                    \Log::info('No hay payment_id, el observer creará el pago automáticamente');
                }
            }
            // 5. Si es un egreso de proveedor, actualizar el pago en la cuenta por pagar
            if ($data['type'] === 'Egreso' && isset($data['type_expense']) && $data['type_expense'] === '2') {
                if ($movement->account_payable_payment_id) {
                    // Actualizar el pago existente usando el ID guardado
                    $payment = AccountPayablePayment::find($movement->account_payable_payment_id);
                    if ($payment) {
                        // Log para debug
                        \Log::info('Actualizando pago de cuenta por pagar', [
                            'payment_id' => $payment->id,
                            'old_amount' => $payment->amount,
                            'new_amount' => $data['amount'],
                            'movement_id' => $movement->id,
                            'supplier' => $movement->supplier->name ?? 'N/A'
                        ]);
                        
                        // Usar el método del modelo para actualizar el pago y el status automáticamente
                        $accountPayable = $payment->accountPayable;
                        $accountPayable->updatePayment(
                            $payment->id,
                            $data['amount'],
                            $data['date'],
                            null, // reference
                            $data['description'] ?? null
                        );
                        
                        // Actualizar el movimiento con la referencia del pago
                        $movement->update(['account_payable_payment_id' => $payment->id]);
                        
                        \Log::info('Pago de cuenta por pagar actualizado exitosamente', [
                            'payment_id' => $payment->id,
                            'new_amount' => $payment->amount,
                            'movement_id' => $movement->id
                        ]);
                    } else {
                        // Si no se encuentra el pago, crear uno nuevo
                        \Log::warning('No se encontró el pago de cuenta por pagar', [
                            'payment_id' => $movement->account_payable_payment_id,
                            'movement_id' => $movement->id
                        ]);
                        // El observer se encargará de crear el pago automáticamente
                        \Log::info('No se encontró el pago de cuenta por pagar, el observer lo creará');
                    }
                } else {
                    // El observer se encargará de crear el pago automáticamente
                    \Log::info('No hay payment_id para cuenta por pagar, el observer creará el pago automáticamente');
                }
            }

            DB::commit();
            return redirect()->route('event.history', $movement->event_id)->with('success', 'Movimiento actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('event.history', $movement->event_id)->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage());
        }
    }

    public function destroyHistory($id)
    {
        DB::beginTransaction();
        try {
            $movement = EventMovement::findOrFail($id);

            // Solo si tiene método de pago
            if ($movement->method_payment_id) {
                $methodPayment = MethodPayment::find($movement->method_payment_id);
                $amount = floatval($movement->amount);

                if ($methodPayment) {
                    if ($movement->type === 'Ingreso') {
                        $methodPayment->current_balance -= $amount;
                    } elseif ($movement->type === 'Egreso') {
                        $methodPayment->current_balance += $amount;
                    }
                    $methodPayment->save();
                }
                // Si no existe el método de pago, simplemente continúa (o puedes registrar un log)
            }

            $eventId = $movement->event_id;
            $movement->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Movimiento eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }



    /**
     * Verificar y corregir relaciones de pagos específicas
     */
    public function verifyAndFixPaymentRelations($eventId)
    {
        try {
            $movements = EventMovement::with(['supplier', 'club'])
                ->where('event_id', $eventId)
                ->where(function($query) {
                    $query->whereNotNull('account_receivable_payment_id')
                          ->orWhereNotNull('account_payable_payment_id');
                })
                ->get();

            $results = [];
            $fixed = 0;

            foreach ($movements as $movement) {
                $result = [
                    'movement_id' => $movement->id,
                    'type' => $movement->type,
                    'amount' => $movement->amount,
                    'date' => $movement->date,
                    'status' => 'OK',
                    'inconsistencies' => []
                ];

                // Verificar pago de cuenta por cobrar
                if ($movement->account_receivable_payment_id) {
                    $payment = AccountReceivablePayment::find($movement->account_receivable_payment_id);
                    if ($payment) {
                        $result['account_receivable_payment'] = [
                            'id' => $payment->id,
                            'amount' => $payment->amount,
                            'date' => $payment->date,
                            'status' => 'Encontrado'
                        ];
                        
                        // Verificar si los montos coinciden
                        if (abs($payment->amount - $movement->amount) > 0.01) {
                            $result['status'] = 'INCONSISTENCIA DETECTADA';
                            $result['inconsistencies'][] = 'Monto del movimiento (' . $movement->amount . ') no coincide con pago (' . $payment->amount . ')';
                            
                            // Corregir automáticamente
                            $payment->update(['amount' => $movement->amount]);
                            $fixed++;
                            
                            \Log::info('Inconsistencia corregida automáticamente', [
                                'movement_id' => $movement->id,
                                'payment_id' => $payment->id,
                                'old_payment_amount' => $payment->amount,
                                'new_payment_amount' => $movement->amount
                            ]);
                        }
                    } else {
                        $result['status'] = 'PAGO NO ENCONTRADO';
                        $result['inconsistencies'][] = 'Pago de cuenta por cobrar no encontrado (ID: ' . $movement->account_receivable_payment_id . ')';
                    }
                }

                // Verificar pago de cuenta por pagar
                if ($movement->account_payable_payment_id) {
                    $payment = AccountPayablePayment::find($movement->account_payable_payment_id);
                    if ($payment) {
                        $result['account_payable_payment'] = [
                            'id' => $payment->id,
                            'amount' => $payment->amount,
                            'date' => $payment->date,
                            'status' => 'Encontrado'
                        ];
                        
                        // Verificar si los montos coinciden
                        if (abs($payment->amount - $movement->amount) > 0.01) {
                            $result['status'] = 'INCONSISTENCIA DETECTADA';
                            $result['inconsistencies'][] = 'Monto del movimiento (' . $movement->amount . ') no coincide con pago (' . $payment->amount . ')';
                            
                            // Corregir automáticamente
                            $payment->update(['amount' => $movement->amount]);
                            $fixed++;
                            
                            \Log::info('Inconsistencia corregida automáticamente', [
                                'movement_id' => $movement->id,
                                'payment_id' => $payment->id,
                                'old_payment_amount' => $payment->amount,
                                'new_payment_amount' => $movement->amount
                            ]);
                        }
                    } else {
                        $result['status'] = 'PAGO NO ENCONTRADO';
                        $result['inconsistencies'][] = 'Pago de cuenta por pagar no encontrado (ID: ' . $movement->account_payable_payment_id . ')';
                    }
                }

                $results[] = $result;
            }

            return response()->json([
                'success' => true,
                'data' => $results,
                'total_movements' => $movements->count(),
                'fixed_count' => $fixed,
                'message' => $fixed > 0 ? "Se corrigieron $fixed inconsistencias automáticamente" : "No se encontraron inconsistencias"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener clubs disponibles para asignar a un evento
     */
    public function getAvailableClubs($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            
            // Obtener todos los clubs
            $allClubs = Club::orderBy('name', 'asc')->get();
            
            // Obtener clubs ya asignados al evento
            $assignedClubs = $event->clubs()->pluck('clubs.id')->toArray();
            
            // Filtrar clubs no asignados
            $availableClubs = $allClubs->whereNotIn('id', $assignedClubs);
            
            return response()->json($availableClubs->values());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener clubs disponibles'], 500);
        }
    }

    /**
     * Asignar un club a un evento
     */
    public function assignClubToEvent(Request $request, $eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            $clubId = $request->input('club_id');
            
            // Validar que el club existe
            $club = Club::findOrFail($clubId);
            
            // Verificar si ya está asignado
            $existingAssignment = $event->clubs()
                ->where('clubs.id', $clubId)
                ->exists();
            
            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este club ya está asignado al evento'
                ], 400);
            }
            
            // Asignar el club al evento
            $event->assignClub($club);
            
            return response()->json([
                'success' => true,
                'message' => 'Club asignado correctamente al evento'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el club: ' . $e->getMessage()
            ], 500);
        }
    }

    private function saveFile($file, $path)
    {
        try {
            if (!$file) {
                return null;
            }

            // Generar un nombre único para el archivo
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Crear el directorio si no existe
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Mover el archivo al directorio de almacenamiento
            $file->move($fullPath, $fileName);

            // Retornar la ruta relativa para guardar en la base de datos
            return $path . $fileName;
        } catch (\Exception $e) {
            throw new \Exception('Error al guardar la imagen: ' . $e->getMessage());
        }
    }

    /**
     * Obtener proveedores disponibles para asignar a un evento
     */
    public function getAvailableSuppliers($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            
            // Obtener todos los proveedores
            $allSuppliers = Supplier::orderBy('name', 'asc')->get();
            
            // Obtener proveedores ya asignados al evento
            $assignedSuppliers = $event->suppliers()->pluck('suppliers.id')->toArray();
            
            // Filtrar proveedores no asignados
            $availableSuppliers = $allSuppliers->whereNotIn('id', $assignedSuppliers);
            
            return response()->json($availableSuppliers->values());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener proveedores disponibles'], 500);
        }
    }

    /**
     * Asignar un proveedor a un evento
     */
    public function assignSupplierToEvent(Request $request, $eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            $supplierId = $request->input('supplier_id');
            
            // Validar que el proveedor existe
            $supplier = Supplier::findOrFail($supplierId);
            
            // Verificar si ya está asignado
            $existingAssignment = $event->suppliers()
                ->where('suppliers.id', $supplierId)
                ->exists();
            
            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este proveedor ya está asignado al evento'
                ], 400);
            }
            
            // Asignar el proveedor al evento
            $event->assignSupplier($supplier);
            
            return response()->json([
                'success' => true,
                'message' => 'Proveedor asignado correctamente al evento'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clubs con cuentas por pagar filtradas por criterios específicos
     * Permite filtrar clubs con cuentas pendientes por diferentes criterios.
     */
    public function getClubsPendingAccountsFiltered($eventId, Request $request)
    {
        try {
            // Validar que el evento existe
            $event = Event::findOrFail($eventId);
            
            // Obtener filtros de la request
            $minAmount = $request->get('min_amount', 0);
            $maxAmount = $request->get('max_amount');
            $currencyId = $request->get('currency_id');
            $overdueOnly = $request->get('overdue_only', false);
            $status = $request->get('status');
            $sortBy = $request->get('sort_by', 'total_pending');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Construir la consulta base
            $query = Club::select([
                    'clubs.id',
                    'clubs.name',
                    'clubs.logo',
                    'clubs.cuit',
                    'clubs.responsible',
                    'clubs.phone',
                    'clubs.email',
                    'clubs.currency_id',
                    'clubs.country_id',
                    'clubs.province_id',
                    'clubs.city_id'
                ])
                ->join('event_clubs', 'clubs.id', '=', 'event_clubs.club_id')
                ->join('account_receivables', function($join) use ($eventId, $overdueOnly, $status) {
                    $join->on('clubs.id', '=', 'account_receivables.club_id')
                         ->where('account_receivables.event_id', '=', $eventId)
                         ->where('account_receivables.status', '!=', 'Pagado')
                         ->where('account_receivables.pending_amount', '>', 0);
                    
                    if ($overdueOnly) {
                        $join->where('account_receivables.due_date', '<', now());
                    }
                    
                    if ($status) {
                        $join->where('account_receivables.status', '=', $status);
                    }
                })
                ->where('event_clubs.event_id', $eventId);

            // Aplicar filtros adicionales
            if ($currencyId) {
                $query->where('clubs.currency_id', $currencyId);
            }

            if ($minAmount > 0) {
                $query->whereRaw('(
                    SELECT SUM(ar.pending_amount) 
                    FROM account_receivables ar 
                    WHERE ar.club_id = clubs.id 
                    AND ar.event_id = ? 
                    AND ar.status != "Pagado"
                ) >= ?', [$eventId, $minAmount]);
            }

            if ($maxAmount) {
                $query->whereRaw('(
                    SELECT SUM(ar.pending_amount) 
                    FROM account_receivables ar 
                    WHERE ar.club_id = clubs.id 
                    AND ar.event_id = ? 
                    AND ar.status != "Pagado"
                ) <= ?', [$eventId, $maxAmount]);
            }

            // Aplicar ordenamiento
            if ($sortBy === 'total_pending') {
                $query->orderByRaw('(
                    SELECT SUM(ar.pending_amount) 
                    FROM account_receivables ar 
                    WHERE ar.club_id = clubs.id 
                    AND ar.event_id = ? 
                    AND ar.status != "Pagado"
                ) ' . $sortOrder, [$eventId]);
            } elseif ($sortBy === 'name') {
                $query->orderBy('clubs.name', $sortOrder);
            } else {
                $query->orderBy('clubs.id', 'asc');
            }

            $clubs = $query->with([
                    'currency:id,name,symbol',
                    'country:id,name',
                    'province:id,name',
                    'city:id,name'
                ])
                ->get()
                ->map(function($club) use ($eventId, $overdueOnly, $status) {
                    // Obtener cuentas pendientes con filtros aplicados
                    $accountsQuery = \App\Models\AccountReceivable::where('club_id', $club->id)
                        ->where('event_id', $eventId)
                        ->where('status', '!=', 'Pagado')
                        ->where('pending_amount', '>', 0);

                    if ($overdueOnly) {
                        $accountsQuery->where('due_date', '<', now());
                    }

                    if ($status) {
                        $accountsQuery->where('status', $status);
                    }

                    $pendingAccounts = $accountsQuery->get();

                    $totalPending = $pendingAccounts->sum('pending_amount');
                    $totalReceivable = $pendingAccounts->sum('total_amount');
                    $totalPaid = $pendingAccounts->sum('paid_amount');
                    
                    $club->accounts_summary = [
                        'total_pending' => $totalPending,
                        'total_receivable' => $totalReceivable,
                        'total_paid' => $totalPaid,
                        'payment_percentage' => $totalReceivable > 0 ? round(($totalPaid / $totalReceivable) * 100, 2) : 0,
                        'accounts_count' => $pendingAccounts->count(),
                        'overdue_accounts' => $pendingAccounts->where('due_date', '<', now())->count(),
                        'accounts' => $pendingAccounts->map(function($account) {
                            return [
                                'id' => $account->id,
                                'pending_amount' => $account->pending_amount,
                                'total_amount' => $account->total_amount,
                                'status' => $account->status,
                                'due_date' => $account->due_date,
                                'is_overdue' => $account->due_date < now()
                            ];
                        })
                    ];
                    
                    return $club;
                });

            // Calcular totales filtrados
            $totalPending = $clubs->sum('accounts_summary.total_pending');
            $totalReceivable = $clubs->sum('accounts_summary.total_receivable');
            $overdueCount = $clubs->sum('accounts_summary.overdue_accounts');

            return response()->json([
                'success' => true,
                'filters' => [
                    'min_amount' => $minAmount,
                    'max_amount' => $maxAmount,
                    'currency_id' => $currencyId,
                    'overdue_only' => $overdueOnly,
                    'status' => $status,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ],
                'summary' => [
                    'total_clubs' => $clubs->count(),
                    'total_pending_amount' => $totalPending,
                    'total_receivable_amount' => $totalReceivable,
                    'overdue_accounts' => $overdueCount,
                    'payment_percentage' => $totalReceivable > 0 ? round(($totalReceivable - $totalPending) / $totalReceivable * 100, 2) : 0
                ],
                'clubs' => $clubs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener clubs filtrados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clubs con cuentas por pagar pendientes (versión simple)
     * Solo devuelve nombre del club y monto pendiente total
     */
    public function getClubsWithPendingAmounts($eventId)
    {
        try {
            // Validar que el evento existe
            $event = Event::findOrFail($eventId);
            
            // Consulta optimizada solo con datos esenciales
            $clubs = Club::select([
                    'clubs.id',
                    'clubs.name',
                    'clubs.currency_id'
                ])
                ->join('event_clubs', 'clubs.id', '=', 'event_clubs.club_id')
                ->join('account_receivables', function($join) use ($eventId) {
                    $join->on('clubs.id', '=', 'account_receivables.club_id')
                         ->where('account_receivables.event_id', '=', $eventId)
                         ->where('account_receivables.status', '!=', 'Pagado')
                         ->where('account_receivables.pending_amount', '>', 0);
                })
                ->where('event_clubs.event_id', $eventId)
                ->with([
                    'currency:id,name,symbol'
                ])
                ->get()
                ->map(function($club) use ($eventId) {
                    // Calcular solo el monto pendiente total
                    $totalPending = \App\Models\AccountReceivable::where('club_id', $club->id)
                        ->where('event_id', $eventId)
                        ->where('status', '!=', 'Pagado')
                        ->where('pending_amount', '>', 0)
                        ->sum('pending_amount');
                    
                    return [
                        'id' => $club->id,
                        'name' => $club->name,
                        'currency' => $club->currency,
                        'pending_amount' => $totalPending
                    ];
                })
                ->filter(function($club) {
                    // Solo incluir clubs con monto pendiente > 0
                    return $club['pending_amount'] > 0;
                })
                ->sortByDesc('pending_amount')
                ->values();

            // Calcular total general
            $totalPending = $clubs->sum('pending_amount');

            return response()->json([
                'success' => true,
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'year' => $event->year
                ],
                'total_pending_amount' => $totalPending,
                'total_clubs' => $clubs->count(),
                'clubs' => $clubs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al obtener clubs con montos pendientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar eventos para filtros
     */
    public function list()
    {
        $events = Event::orderBy('name', 'asc')->get();
        return response()->json($events);
    }

    /**
     * Validar consistencia del movimiento
     */
    private function validateMovementConsistency($data)
    {
        // Log para debugging
        \Log::info('Validando consistencia del movimiento', [
            'method_payment_id' => $data['method_payment_id'] ?? 'no definido',
            'currency_id' => $data['currency_id'] ?? 'no definido',
            'data_completa' => $data
        ]);

        // Validar consistencia de monedas
        if (isset($data['method_payment_id'])) {
            $methodPayment = MethodPayment::find($data['method_payment_id']);
            if ($methodPayment) {
                \Log::info('Método de pago encontrado', [
                    'method_payment_id' => $methodPayment->id,
                    'method_currency_id' => $methodPayment->currency_id,
                    'movement_currency_id' => $data['currency_id']
                ]);
                
                if ((int)$methodPayment->currency_id !== (int)$data['currency_id']) {
                    \Log::error('Inconsistencia de monedas detectada', [
                        'method_currency_id' => $methodPayment->currency_id,
                        'movement_currency_id' => $data['currency_id'],
                        'method_currency_id_int' => (int)$methodPayment->currency_id,
                        'movement_currency_id_int' => (int)$data['currency_id']
                    ]);
                    throw new \Exception('La moneda del método de pago no coincide con la moneda del movimiento');
                }
            } else {
                \Log::error('Método de pago no encontrado', ['method_payment_id' => $data['method_payment_id']]);
                throw new \Exception('El método de pago seleccionado no existe');
            }
        }

        // Validar que la cuenta por cobrar pertenezca al evento
        if (isset($data['account_receivable_id'])) {
            $accountReceivable = AccountReceivable::find($data['account_receivable_id']);
            if ($accountReceivable && $accountReceivable->event_id != $data['event_id']) {
                throw new \Exception('La cuenta por cobrar no pertenece a este evento');
            }
        }

        // Validar que la cuenta por pagar pertenezca al evento
        if (isset($data['account_payable_id'])) {
            $accountPayable = AccountPayable::find($data['account_payable_id']);
            if ($accountPayable && $accountPayable->event_id != $data['event_id']) {
                throw new \Exception('La cuenta por pagar no pertenece a este evento');
            }
        }

        // Validar que el monto no exceda el pendiente en cuentas por cobrar
        if (isset($data['account_receivable_id'])) {
            $accountReceivable = AccountReceivable::find($data['account_receivable_id']);
            if ($accountReceivable) {
                $pendingAmount = $accountReceivable->getPendingAmount();
                if ($data['amount'] > $pendingAmount) {
                    throw new \Exception('El monto excede el saldo pendiente de la cuenta por cobrar');
                }
            }
        }

        // Validar que el monto no exceda el pendiente en cuentas por pagar
        if (isset($data['account_payable_id'])) {
            $accountPayable = AccountPayable::find($data['account_payable_id']);
            if ($accountPayable) {
                $pendingAmount = $accountPayable->getPendingAmount();
                if ($data['amount'] > $pendingAmount) {
                    throw new \Exception('El monto excede el saldo pendiente de la cuenta por pagar');
                }
            }
        }
    }
}
