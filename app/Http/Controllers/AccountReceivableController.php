<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use App\Models\Bussines;
use App\Models\Currency;
use App\Models\Supplier;
use App\Models\ClubPayment;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryIncome;
use App\Models\AccountReceivable;
use Illuminate\Support\Facades\DB;
use App\Models\ClubAccountReceivable;
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
            $data = AccountReceivable::with(['club', 'event', 'currency', 'payments'])
                ->select('account_receivables.*');
                
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
                ->addColumn('total_amount', function ($row) {
                    return number_format($row->total_amount, 2, ',', '.');
                })
                ->addColumn('total_amount_raw', function ($row) {
                    return $row->total_amount;
                })
                ->addColumn('paid_amount', function ($row) {
                    $paidAmount = $row->payments->sum('amount');
                    return number_format($paidAmount, 2, ',', '.');
                })
                ->addColumn('paid_amount_raw', function ($row) {
                    return $row->payments->sum('amount');
                })
                ->addColumn('pending_amount', function ($row) {
                    $paidAmount = $row->payments->sum('amount');
                    $pendingAmount = $row->total_amount - $paidAmount;
                    return number_format($pendingAmount, 2, ',', '.');
                })
                ->addColumn('pending_amount_raw', function ($row) {
                    $paidAmount = $row->payments->sum('amount');
                    return $row->total_amount - $paidAmount;
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

    public function create()
    {
        $events = Event::orderBy('name', 'asc')->get();
        $clubs = Club::orderBy('name', 'asc')->get();
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $currencies = Currency::orderBy('name', 'asc')->get();
        return view('account-receivable.create', compact('events', 'clubs', 'suppliers', 'currencies'));
    }

    /**
     * Obtener clubs asignados a un evento
     */
    public function getClubsByEvent($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            $clubs = $event->clubs()->orderBy('name', 'asc')->get();
            
            return response()->json($clubs);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener clubs'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($accountReceivable)
    {
        $accountReceivable = AccountReceivable::findOrFail($accountReceivable);
        // Verificar que la cuenta por cobrar tenga un club válido
        if (!$accountReceivable->club) {
            return redirect()->route('account-receivable.index')
                ->with('error', 'La cuenta por cobrar no tiene un club asociado válido.');
        }
        
        $accountReceivable->load(['club', 'event', 'currency', 'supplier', 'payments']);
        
        return view('account-receivable.show', compact('accountReceivable'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $accountReceivable = AccountReceivable::with(['club', 'event', 'currency', 'supplier'])->findOrFail($id);
        
        // Verificar que no tenga pagos registrados
        if ($accountReceivable->payments->count() > 0) {
            return redirect()->route('account-receivable.index')
                ->with('error', 'No se puede editar una cuenta por cobrar que tiene pagos registrados.');
        }
        
        $events = Event::all();
        $currencies = Currency::all();
        
        return view('account-receivable.edit', compact('accountReceivable', 'events', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $accountReceivable = AccountReceivable::findOrFail($id);
        
        // Verificar que no tenga pagos registrados
        if ($accountReceivable->payments->count() > 0) {
            return redirect()->route('account-receivable.index')
                ->with('error', 'No se puede editar una cuenta por cobrar que tiene pagos registrados.');
        }
        
        $request->validate([
            'event_id' => 'required|exists:events,id',
            'club_id' => 'required|exists:clubs,id',
            'currency_id' => 'required|exists:currencies,id',
            'has_accommodation' => 'required|boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'players_quantity' => 'required|integer|min:0',
            'player_price' => 'required|numeric|min:0',
            'teachers_quantity' => 'required|integer|min:0',
            'teacher_price' => 'required|numeric|min:0',
            'companions_quantity' => 'required|integer|min:0',
            'companion_price' => 'required|numeric|min:0',
            'drivers_quantity' => 'required|integer|min:0',
            'driver_price' => 'required|numeric|min:0',
            'liberated_quantity' => 'required|integer|min:0',
            'liberated_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Limpiar formatos de números
            $data = $request->all();
            $data['player_price'] = (float) str_replace(['.', ','], ['', '.'], $data['player_price']);
            $data['teacher_price'] = (float) str_replace(['.', ','], ['', '.'], $data['teacher_price']);
            $data['companion_price'] = (float) str_replace(['.', ','], ['', '.'], $data['companion_price']);
            $data['driver_price'] = (float) str_replace(['.', ','], ['', '.'], $data['driver_price']);
            $data['liberated_price'] = (float) str_replace(['.', ','], ['', '.'], $data['liberated_price']);
            
            $data['players_quantity'] = (int) str_replace('.', '', $data['players_quantity']);
            $data['teachers_quantity'] = (int) str_replace('.', '', $data['teachers_quantity']);
            $data['companions_quantity'] = (int) str_replace('.', '', $data['companions_quantity']);
            $data['drivers_quantity'] = (int) str_replace('.', '', $data['drivers_quantity']);
            $data['liberated_quantity'] = (int) str_replace('.', '', $data['liberated_quantity']);
            
            // Calcular totales
            $data['total_players'] = $data['players_quantity'] * $data['player_price'];
            $data['total_teachers'] = $data['teachers_quantity'] * $data['teacher_price'];
            $data['total_companions'] = $data['companions_quantity'] * $data['companion_price'];
            $data['total_drivers'] = $data['drivers_quantity'] * $data['driver_price'];
            $data['total_liberated'] = $data['liberated_quantity'] * $data['liberated_price'];
            $data['total_people'] = $data['players_quantity'] + $data['teachers_quantity'] + $data['companions_quantity'] + $data['drivers_quantity'] + $data['liberated_quantity'];
            $data['total_amount'] = $data['total_players'] + $data['total_teachers'] + $data['total_companions'] + $data['total_drivers'] + $data['total_liberated'];
            
            $accountReceivable->update($data);
            
            DB::commit();
            
            return redirect()->route('account-receivable.index')
                ->with('success', 'Cuenta por cobrar actualizada correctamente.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error al actualizar la cuenta por cobrar: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $accountReceivable = AccountReceivable::findOrFail($id);
        
        // Verificar que no tenga pagos registrados
        if ($accountReceivable->payments->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una cuenta por cobrar que tiene pagos registrados.'
            ], 400);
        }
        
        try {
            $accountReceivable->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Cuenta por cobrar eliminada correctamente.'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cuenta por cobrar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proveedores hoteleros asignados a un evento
     */
    public function getHotelSuppliersByEvent($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);
            
            // Obtener proveedores asignados al evento que sean de categoría Hotel
            $hotelSuppliers = $event->suppliers()
                ->whereHas('categorySupplier', function($query) {
                    $query->where('name', 'Hotel');
                })
                ->with('categorySupplier')
                ->orderBy('name', 'asc')
                ->get();
            
            return response()->json($hotelSuppliers);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener proveedores hoteleros'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            // Debug temporal para ver los datos recibidos
            \Log::info('AccountReceivable store data:', $request->all());
            
            // Verificar que club_id esté presente
            if (!$request->has('club_id') || empty($request->club_id)) {
                throw new \Exception('El campo club_id es requerido');
            }
            // Validar datos requeridos
            $request->validate([
                'event_id' => 'required|exists:events,id',
                'club_id' => 'required|exists:clubs,id',
                'currency_id' => 'required|exists:currencies,id',
                'has_accommodation' => 'required|in:0,1',
                'total_amount' => 'required|numeric|min:0',
            ]);

            // Limpiar puntos de miles y comas de todos los montos
            $totalAmount = str_replace(['.', ','], ['', '.'], $request->total_amount);
            $playersQuantity = str_replace('.', '', $request->players_quantity ?? 0);
            $playerPrice = str_replace(['.', ','], ['', '.'], $request->player_price ?? 0);
            $teachersQuantity = str_replace('.', '', $request->teachers_quantity ?? 0);
            $teacherPrice = str_replace(['.', ','], ['', '.'], $request->teacher_price ?? 0);
            $companionsQuantity = str_replace('.', '', $request->companions_quantity ?? 0);
            $companionPrice = str_replace(['.', ','], ['', '.'], $request->companion_price ?? 0);
            $driversQuantity = str_replace('.', '', $request->drivers_quantity ?? 0);
            $driverPrice = str_replace(['.', ','], ['', '.'], $request->driver_price ?? 0);
            $liberatedQuantity = str_replace('.', '', $request->liberated_quantity ?? 0);
            $liberatedPrice = str_replace(['.', ','], ['', '.'], $request->liberated_price ?? 0);

            // Crear la cuenta por cobrar
            $accountReceivable = AccountReceivable::create([
                'club_id' => $request->club_id,
                'event_id' => $request->event_id,
                'currency_id' => $request->currency_id,
                'supplier_id' => $request->supplier_id ?? null,
                'date' => now()->format('Y-m-d'),
                // Campos adicionales del desglose
                'has_accommodation' => $request->has_accommodation,
                'players_quantity' => (int)$playersQuantity,
                'player_price' => (float)$playerPrice,
                'total_players' => (int)$playersQuantity * (float)$playerPrice,
                'teachers_quantity' => (int)$teachersQuantity,
                'teacher_price' => (float)$teacherPrice,
                'total_teachers' => (int)$teachersQuantity * (float)$teacherPrice,
                'companions_quantity' => (int)$companionsQuantity,
                'companion_price' => (float)$companionPrice,
                'total_companions' => (int)$companionsQuantity * (float)$companionPrice,
                'drivers_quantity' => (int)$driversQuantity,
                'driver_price' => (float)$driverPrice,
                'total_drivers' => (int)$driversQuantity * (float)$driverPrice,
                'liberated_quantity' => (int)$liberatedQuantity,
                'liberated_price' => (float)$liberatedPrice,
                'total_liberated' => (int)$liberatedQuantity * (float)$liberatedPrice,
                'total_people' => (int)$playersQuantity + (int)$teachersQuantity + (int)$companionsQuantity + (int)$driversQuantity + (int)$liberatedQuantity,
                'total_amount' => (float)$totalAmount,
                'description' => $request->description ?? null,
                'status' => 'Pendiente',
            ]);

            DB::commit();

            return redirect()->route('account-receivable.index')
                ->with('success', 'Cuenta por cobrar creada correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la cuenta por cobrar: ' . $e->getMessage());
        }
    }


    /**
     * Procesa el pago de una cuenta por cobrar
     */
    public function processPayment(ProcessPaymentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            $receivable = AccountReceivable::findOrFail($data['receivable_id']);
            
            // Limpiar puntos de miles y comas del monto antes de guardar
            $data['amount'] = str_replace(['.', ','], ['', '.'], $data['amount']);

            // Verificar que el monto no exceda el pendiente
            $pendingAmount = $receivable->getPendingAmount();
            if ($data['amount'] > $pendingAmount) {
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
                'bussines_id' => 1, // ID del negocio
                'event_id' => $receivable->event_id,
                'club_id' => $receivable->club_id,
                'account_receivable_id' => $receivable->id, // Relacionar con la cuenta por cobrar
                'method_payment_id' => $data['method_payment_id'] ?? null,
                'category_income_id' => 1, // ID fijo para pagos de club
                'currency_id' => $receivable->currency_id,
                'amount' => $data['amount'],
                'date' => $data['date'],
                'description' => $data['description'] ?? "Pago de cuenta por cobrar #{$receivable->id}",
                'type' => 'Ingreso',
                'user_id' => auth()->id(), // Usuario que realiza el pago
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago procesado correctamente',
                'data' => [
                    'pending_amount' => $receivable->getPendingAmount(),
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
