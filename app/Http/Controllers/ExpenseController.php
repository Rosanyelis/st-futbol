<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Models\CategoryExpense;
use App\Models\CategoryEgress;
use App\Models\Expense;
use App\Models\SubcategoryExpense;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Expense::with('categoryExpense', 'subcategoryExpense')->get();
            return DataTables::of($data)
                ->addColumn('actions', function ($row) {
                    return view('expenses.actions', compact('row'));
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('expenses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()    
    {
        $categoryExpenses = CategoryExpense::all();
        return view('expenses.create', compact('categoryExpenses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request)
    {
        try {
            $data = $request->all();
            $data['category_egress_id'] = 1; // Egreso de gastos
            $expense = Expense::create($data);
            return redirect()->route('expense.index')->with('success', 'Gasto creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('expense.create')->with('error', 'Error al crear el gasto: ' . $e->getMessage());
        }
    }

    public function getSubcategoryExpenses(Request $request)
    {
        $subcategoryExpenses = SubcategoryExpense::where('category_expense_id', $request->category_expense_id)->get();
        return response()->json($subcategoryExpenses);
    }

    /**
     * Display the specified resource.
     */
    public function show($expense)
    {
        $expense = Expense::find($expense);
        $categoryExpenses = CategoryExpense::orderBy('name', 'asc')->get();
        $subcategoryExpenses = SubcategoryExpense::orderBy('name', 'asc')->get();
        return view('expenses.edit', compact('expense', 'categoryExpenses', 'subcategoryExpenses'));
    }

    public function edit($expense)
    {
        $expense = Expense::find($expense);
        $categoryExpenses = CategoryExpense::orderBy('name', 'asc')->get();
        return view('expenses.edit', compact('expense', 'categoryExpenses'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(UpdateExpenseRequest $request, $expense)
    {
        try {
            $expense = Expense::find($expense);
            $data = $request->all();
            $expense->update($data);
            return redirect()->route('expense.index')->with('success', 'Gasto actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('expense.edit', $expense)->with('error', 'Error al actualizar el gasto');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function destroy($expense)
    {
        try {
            $expense = Expense::find($expense);
            $expense->delete();
            return redirect()->route('expense.index')->with('success', 'Gasto eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('expense.index')->with('error', 'Error al eliminar el gasto');
        }
    }

}
