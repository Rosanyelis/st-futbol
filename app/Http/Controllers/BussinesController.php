<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Expense;
use App\Models\Bussines;
use App\Models\Currency;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\EventMovement;
use App\Models\MethodPayment;
use App\Models\CategoryEgress;
use App\Models\CategoryIncome;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class BussinesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business = Bussines::find(1); // Asumiendo que el negocio tiene ID 1
        $clubs = Club::all();
        $suppliers = Supplier::all();
        $expenses = Expense::with('categoryExpense', 'subcategoryExpense')->get();
        $currencies = Currency::all();
        $categoryIncomes = CategoryIncome::all();
        $categoryEgress = CategoryEgress::all();

        return view('bussines.index', compact('business', 'clubs', 'suppliers', 'expenses', 'currencies', 'categoryIncomes', 'categoryEgress'));
    }

    public function historyJson(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with('club', 'currency', 'methodPayment', 'methodPayment.entity', 'supplier')
            ->where('bussines_id', '1');

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
                ->make(true);
        }
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

    public function storeTransaction(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            $data = $request->all();

            $data['bussines_id'] = 1; // ID del negocio, se puede cambiar según sea necesario
            $data['event_id'] = null;
            $data['date'] = date('Y-m-d H:i:s');
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
            if ($data['type'] === 'Ingreso' && $data['type_income'] === 1 && isset($data['club_id'])) {
                $this->createClubPayment($data);
            }

            // Si es un egreso de proveedor, crear el movimiento de gasto
            if ($data['type'] === 'Egreso' && $data['type_expense'] === 2 && isset($data['supplier_id'])) {
                $this->createSupplierPayment($data);
            }

            DB::commit();
            return redirect()->route('bussines.index')->with('success', 'Movimiento creado correctamente');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('bussines.index')->with('error', 'Error al crear el movimiento: ' . $e->getMessage());
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
