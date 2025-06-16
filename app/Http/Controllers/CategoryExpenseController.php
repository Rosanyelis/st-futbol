<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryExpense;
use Yajra\DataTables\DataTables;
use App\Http\Requests\CategoryExpense\StoreCategoryExpenseRequest;
use App\Http\Requests\CategoryExpense\UpdateCategoryExpenseRequest;

class CategoryExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CategoryExpense::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('category-expenses.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('category-expenses.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category-expenses.create');   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryExpenseRequest $request)
    {
        try {   
            CategoryExpense::create($request->all());
            return redirect()->route('category-expense.index')->with('success', 'Categoría de gasto creada correctamente'); 
        } catch (\Exception $e) {
            return redirect()->route('category-expense.index')->with('error', 'Error al crear la categoría de gasto');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($categoryExpense)
    {
        $categoryExpense = CategoryExpense::find($categoryExpense);
        return view('category-expenses.edit', compact('categoryExpense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryExpenseRequest $request, $categoryExpense)
    {
        try {
            $categoryExpense = CategoryExpense::find($categoryExpense);
            $categoryExpense->update($request->all());
            return redirect()->route('category-expense.index')->with('success', 'Categoría de gasto actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('category-expense.index')->with('error', 'Error al actualizar la categoría de gasto');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($categoryExpense)
    {
        try {
            $categoryExpense = CategoryExpense::find($categoryExpense);
            if ($categoryExpense->subcategoryExpenses->isEmpty()) {
                $categoryExpense->delete();
                return redirect()->route('category-expense.index')->with('success', 'Categoría de gasto eliminada correctamente');
            } else {
                return redirect()->route('category-expense.index')->with('error', 'No se puede eliminar la categoría de gasto porque tiene subcategorías asociadas');
            }
        } catch (\Exception $e) {
            return redirect()->route('category-expense.index')->with('error', 'Error al eliminar la categoría de gasto');
        }
    }
}
