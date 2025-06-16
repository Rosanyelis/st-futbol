<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategorySupplier;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\CategorySupplier\StoreCategorySupplierRequest;
use App\Http\Requests\CategorySupplier\UpdateCategorySupplierRequest;

class CategorySupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CategorySupplier::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('category-suppliers.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('category-suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category-suppliers.create');   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategorySupplierRequest $request)
    {
        try {   
            CategorySupplier::create($request->all());
            return redirect()->route('category-supplier.index')->with('success', 'Categoría de proveedor creada correctamente'); 
        } catch (\Exception $e) {
            return redirect()->route('category-supplier.index')->with('error', 'Error al crear la categoría de proveedor');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($categorySupplier)
    {
        $categorySupplier = CategorySupplier::find($categorySupplier);
        return view('category-suppliers.edit', compact('categorySupplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategorySupplierRequest $request, $categorySupplier)
    {
        try {
            $categorySupplier = CategorySupplier::find($categorySupplier);
            $categorySupplier->update($request->all());
            return redirect()->route('category-supplier.index')->with('success', 'Categoría de proveedor actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('category-supplier.index')->with('error', 'Error al actualizar la categoría de proveedor');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($categorySupplier)
    {
        try {
            $categorySupplier = CategorySupplier::find($categorySupplier);
            if ($categorySupplier->subcategories->isEmpty()) {
                $categorySupplier->delete();
                return redirect()->route('category-supplier.index')->with('success', 'Categoría de proveedor eliminada correctamente');
            } else {
                return redirect()->route('category-supplier.index')->with('error', 'No se puede eliminar la categoría de proveedor porque tiene subcategorías asociadas');
            }
        } catch (\Exception $e) {
            return redirect()->route('category-supplier.index')->with('error', 'Error al eliminar la categoría de proveedor');
        }
    }
}
