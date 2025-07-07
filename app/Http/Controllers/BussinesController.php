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

    // Obtener movimientos históricos para DataTables
    public function historyJson(Request $request)
    {
        if ($request->ajax()) {
            $data = EventMovement::with('club', 'currency', 'methodPayment', 'methodPayment.entity', 'supplier')
                ->where('bussines_id', 1);

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->filled('currency_id')) {
                        $query->where('currency_id', $request->get('currency_id'));
                    }
                    if ($request->filled('start_date')) {
                        $query->where('date', '>=', $request->get('start_date'));
                    }
                    if ($request->filled('end_date')) {
                        $query->where('date', '<=', $request->get('end_date'));
                    }
                    if ($request->has('search') && !empty($request->get('search')['value'])) {
                        $searchValue = $request->get('search')['value'];
                        $query->where(function ($subQuery) use ($searchValue) {
                            $subQuery->where('date', 'like', "%{$searchValue}%")
                                ->orWhere('type', 'like', "%{$searchValue}%")
                                ->orWhere('amount', 'like', "%{$searchValue}%")
                                ->orWhere('description', 'like', "%{$searchValue}%");
                            $subQuery->orWhereHas('currency', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });
                            $subQuery->orWhereHas('club', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });
                            $subQuery->orWhereHas('supplier', function ($q) use ($searchValue) {
                                $q->where('name', 'like', "%{$searchValue}%");
                            });
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
                    return view('bussines.actions', ['data' => $data]);
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

    // Cargar datos de un movimiento para edición (AJAX)
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

    // Guardar un nuevo movimiento
    public function storeTransaction(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            // Limpiar comas del monto antes de guardar
            $data['amount'] = str_replace(',', '', $data['amount']);

            $data['bussines_id'] = 1;
            $data['event_id'] = null;
            $data['date'] = date('Y-m-d H:i:s');
            $data['category_egress_id'] = $data['type_expense'] ?? null; 
            $data['category_income_id'] = $data['type_income'] ?? null;
            $data['user_id'] = Auth::user()->id;

            $movement = EventMovement::create($data);

            // Actualizar el balance del método de pago
            if (!empty($data['method_payment_id'])) {
                $this->updatePaymentMethodBalance($data['method_payment_id'], $data['amount'], $data['type']);
            }

            // Si es un ingreso de club, crear el movimiento de abono
            if ($data['type'] === 'Ingreso' && $data['type_income'] == 1 && isset($data['club_id'])) {
                $this->createClubPayment($data);
            }

            // Si es un egreso de proveedor, crear el movimiento de gasto
            if ($data['type'] === 'Egreso' && $data['type_expense'] == 2 && isset($data['supplier_id'])) {
                $this->createSupplierPayment($data);
            }

            DB::commit();
            return redirect()->route('bussines.index')->with('success', 'Movimiento creado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('bussines.index')->with('error', 'Error al crear el movimiento: ' . $e->getMessage());
        }
    }

    // Actualizar un movimiento existente
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
                $methodPayment = MethodPayment::find($oldMethodPaymentId);
                if ($methodPayment) {
                    if ($oldType === 'Ingreso') {
                        $methodPayment->current_balance -= $oldAmount;
                    } elseif ($oldType === 'Egreso') {
                        $methodPayment->current_balance += $oldAmount;
                    }
                    $methodPayment->save();
                }
            }

            // 2. Actualizar el movimiento con los nuevos datos
            $movement->update($data);

            // 3. Aplicar el nuevo saldo si tiene método de pago
            if (!empty($data['method_payment_id'])) {
                $newMethodPayment = MethodPayment::find($data['method_payment_id']);
                $newAmount = floatval($data['amount']);
                $newType = $data['type'];

                if ($newMethodPayment) {
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
            }

            DB::commit();
            return redirect()->route('bussines.index')->with('success', 'Movimiento actualizado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('bussines.index')->with('error', 'Error al actualizar el movimiento: ' . $e->getMessage());
        }
    }

    // Eliminar un movimiento y devolver saldo a la cuenta
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
            }

            $movement->delete();

            DB::commit();
            return redirect()->route('bussines.index')->with('success', 'Movimiento eliminado correctamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('bussines.index')->with('error', 'Error al eliminar el movimiento: ' . $e->getMessage());
        }
    }

    // Métodos auxiliares (puedes dejarlos privados)
    private function updatePaymentMethodBalance($paymentMethodId, $amount, $type)
    {
        $methodPayment = MethodPayment::findOrFail($paymentMethodId);
        $amount = floatval($amount);

        if ($type === 'Ingreso') {
            $methodPayment->current_balance += $amount;
        } else if ($type === 'Egreso') {
            if ($methodPayment->current_balance < $amount) {
                throw new \Exception('Saldo insuficiente en el método de pago');
            }
            $methodPayment->current_balance -= $amount;
        }
        $methodPayment->save();
    }

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
}
