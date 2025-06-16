<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryIncome;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\CategoryIncome\StoreCategoryIncomeRequest;
use App\Http\Requests\CategoryIncome\UpdateCategoryIncomeRequest;

class CategoryIncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CategoryIncome::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('category-incomes.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('category-incomes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category-incomes.create');   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryIncomeRequest $request)
    {
        try {   
            CategoryIncome::create($request->all());
            return redirect()->route('category-income.index')->with('success', 'Categoría de ingreso creada correctamente'); 
        } catch (\Exception $e) {
            return redirect()->route('category-income.index')->with('error', 'Error al crear la categoría de ingreso');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($categoryIncome)
    {
        $categoryIncome = CategoryIncome::find($categoryIncome);
        return view('category-incomes.edit', compact('categoryIncome'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryIncomeRequest $request, $categoryIncome)
    {
        try {
            $categoryIncome = CategoryIncome::find($categoryIncome);
            $categoryIncome->update($request->all());
            return redirect()->route('category-income.index')->with('success', 'Categoría de ingreso actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('category-income.index')->with('error', 'Error al actualizar la categoría de ingreso');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($categoryIncome)
    {
        try {
            $categoryIncome = CategoryIncome::find($categoryIncome);
            if ($categoryIncome->eventMovements->isEmpty()) {
                $categoryIncome->delete();
                return redirect()->route('category-income.index')->with('success', 'Categoría de ingreso eliminada correctamente');
            } else {
                return redirect()->route('category-income.index')->with('error', 'No se puede eliminar la categoría de ingreso porque tiene subcategorías asociadas');
            }
        } catch (\Exception $e) {
            return redirect()->route('category-income.index')->with('error', 'Error al eliminar la categoría de ingreso');
        }
    }
}
