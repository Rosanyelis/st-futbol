<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Currency;
use App\Models\Supplier;
use App\Models\ClubPayment;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryEgress;
use App\Models\CategoryIncome;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Event::all();
            return DataTables::of($data)
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
            $data = EventMovement::with('club', 'currency', 'methodPayment', 'methodPayment.entity', 'supplier')->where('event_id', $event);

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

    public function history($event)
    {
        $event = Event::find($event);
        $clubs = Club::all();
        $suppliers = Supplier::all();
        $expenses = Expense::with('categoryExpense', 'subcategoryExpense')->get();
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
            $clubs = Club::where('event_id', request()->get('event_id'))->get();
            return response()->json($clubs);
        }
        
        return response()->json([]);
    }

    /**
     * Obtener gastos por categoría de egreso
     */
    public function getExpensesByCategory($categoryEgressId)
    {
        if ($categoryEgressId == 1) { // ID 1 = "Gastos"
            $expenses = Expense::with('categoryExpense', 'subcategoryExpense')->where('category_egress_id', $categoryEgressId)->get();
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
            $suppliers = Supplier::where('category_egress_id', $categoryEgressId)->get();
            return response()->json($suppliers);
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
           
            // Crear el movimiento del evento
            $movement = EventMovement::create($data);

            // Actualizar el balance del método de pago
            if (!empty($data['method_payment_id'])) {
                $this->updatePaymentMethodBalance($data['method_payment_id'], $data['amount'], $data['type']);
            }

            // Si es un ingreso de club, crear el movimiento de abono
            if ($data['type'] === 'Ingreso' && $data['type_income'] === '1' && isset($data['club_id'])) {
                $this->createClubPayment($data);
            }

            // Si es un egreso de proveedor, crear el movimiento de gasto
            if ($data['type'] === 'Egreso' && $data['type_expense'] === '2' && isset($data['supplier_id'])) {
                $this->createSupplierPayment($data);
            }

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
        ClubPayment::create([
            'club_id' => $data['club_id'],
            'currency_id' => $data['currency_id'],
            'method_payment_id' => $data['method_payment_id'] ?? null,
            'date' => $data['date'],
            'amount' => $data['amount'],
        ]);
    }

    private function createSupplierPayment($data)
    {
        SupplierPayment::create([
            'supplier_id' => $data['supplier_id'],
            'currency_id' => $data['currency_id'],
            'method_payment_id' => $data['method_payment_id'] ?? null,
            'date' => $data['date'],
            'amount' => $data['amount'],
        ]);
    }

    public function editHistory($id)
    {
        $data = EventMovement::with('club', 'supplier', 'expense', 'currency', 'categoryIncome', 'categoryEgress')->find($id);

        return response()->json([
            'data' => $data,
            'clubs' => Club::all(),
            'suppliers' => Supplier::all(),
            'expenses' => Expense::with('categoryExpense', 'subcategoryExpense')->get(),
            'currencies' => Currency::all(),
            'categoryIncomes' => CategoryIncome::all(),
            'categoryEgress' => CategoryEgress::all(),
        ]);
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
}
