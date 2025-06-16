<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryExpense;
use App\Models\SubcategoryExpense;
use Yajra\DataTables\DataTables;
use App\Http\Requests\SubcategoryExpense\StoreSubcategoryExpenseRequest;
use App\Http\Requests\SubcategoryExpense\UpdateSubcategoryExpenseRequest;

class SubcategoryExpenseController extends Controller
{
     /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubcategoryExpense::with('categoryExpense')->get();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('subcategory-expenses.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('subcategory-expenses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categoryExpenses = CategoryExpense::all();
        return view('subcategory-expenses.create', compact('categoryExpenses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubcategoryExpenseRequest $request)
    {
        try {
            SubcategoryExpense::create($request->validated());
            return redirect()->route('subcategory-expense.index')->with('success', 'Subcategoría de gasto creada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('subcategory-expense.index')->with('error', 'Error al crear la subcategoría de gasto');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($subcategoryExpense)
    {
        $subcategoryExpense = SubcategoryExpense::with('categoryExpense')->find($subcategoryExpense);
        $categoryExpenses = CategoryExpense::all();
        return view('subcategory-expenses.edit', compact('subcategoryExpense', 'categoryExpenses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubcategoryExpenseRequest $request, $subcategoryExpense)
    {
        try {
            $subcategoryExpense = SubcategoryExpense::find($subcategoryExpense);
            $subcategoryExpense->update($request->validated());
            return redirect()->route('subcategory-expense.index')->with('success', 'Subcategoría de gasto actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('subcategory-expense.index')->with('error', 'Error al actualizar la subcategoría de gasto');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $subcategoryExpense)
    {
        try {
            $subcategoryExpense = SubcategoryExpense::find($subcategoryExpense);
            if ($subcategoryExpense->expenses->count() == 0) {
                $subcategoryExpense->delete();
                return redirect()->route('subcategory-expense.index')->with('success', 'Subcategoría de gasto eliminada correctamente');
            } else {
                return redirect()->route('subcategory-expense.index')->with('error', 'No se puede eliminar la subcategoría de gasto porque tiene gastos asociados');
            }
        } catch (\Exception $e) {
            return redirect()->route('subcategory-expense.index')->with('error', 'Error al eliminar la subcategoría de gasto');
        }
    }
}
