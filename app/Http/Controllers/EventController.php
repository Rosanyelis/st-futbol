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
use App\Models\ClubPayment;
use App\Models\SupplierPayment;
use Illuminate\Support\Facades\DB;
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
                ->make(true);
        }
    }

    public function history($event)
    {
        $event = Event::find($event);
        $clubs = Club::all();
        $suppliers = Supplier::all();
        $expenses = Expense::all();
        $currencies = Currency::all();
        return view('events.history', compact('event', 'clubs', 'suppliers', 'expenses', 'currencies'));
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

    public function storeTransaction(Request $request, $event)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $data['event_id'] = $event;
            $data['date'] = date('Y-m-d H:i:s');
            $data['status'] = 'Pagado';

            // Crear el movimiento del evento
            $movement = EventMovement::create($data);

            // Actualizar el balance del método de pago
            if ($data['method_payment_id']) {
                $this->updatePaymentMethodBalance($data['method_payment_id'], $data['amount'], $data['type']);
            }

            // Si es un ingreso de club, crear el movimiento de abono
            if ($data['type'] === 'Ingreso' && $data['type_income'] === 'Club' && isset($data['club_id'])) {
                $this->createClubPayment($data);
            }

            // si es un egreso de proveedor, crear el movimiento de gasto
            if ($data['type'] === 'Egreso' && $data['type_expense'] === 'Proveedor' && isset($data['supplier_id'])) {
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
            'amount' => $data['amount'],
        ]);
    }

    private function createSupplierPayment($data)
    {
        SupplierPayment::create([
            'supplier_id' => $data['supplier_id'],
            'currency_id' => $data['currency_id'],
            'amount' => $data['amount'],
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
}
