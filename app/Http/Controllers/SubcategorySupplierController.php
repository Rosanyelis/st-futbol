<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategorySupplier;
use App\Models\SubcategorySupplier;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\SubcategorySupplier\StoreSubcategorySupplierRequest;
use App\Http\Requests\SubcategorySupplier\UpdateSubcategorySupplierRequest;

class SubcategorySupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = SubcategorySupplier::with('categorySupplier')->get();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('subcategory-suppliers.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('subcategory-suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorySuppliers = CategorySupplier::all();
        return view('subcategory-suppliers.create', compact('categorySuppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubcategorySupplierRequest $request)
    {
        try {
            SubcategorySupplier::create($request->all());
            return redirect()->route('subcategory-supplier.index')->with('success', 'Subcategoría de proveedor creada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('subcategory-supplier.index')->with('error', 'Error al crear la subcategoría de proveedor');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($subcategorySupplier)
    {
        $subcategorySupplier = SubcategorySupplier::find($subcategorySupplier);
        $categorySuppliers = CategorySupplier::all();
        return view('subcategory-suppliers.edit', compact('subcategorySupplier', 'categorySuppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubcategorySupplierRequest $request, $subcategorySupplier)
    {
        try {
            $subcategorySupplier = SubcategorySupplier::find($subcategorySupplier);
            $subcategorySupplier->update($request->all());
            return redirect()->route('subcategory-supplier.index')->with('success', 'Subcategoría de proveedor actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('subcategory-supplier.index')->with('error', 'Error al actualizar la subcategoría de proveedor');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $subcategorySupplier)
    {
        try {
            $subcategorySupplier = SubcategorySupplier::find($subcategorySupplier);
            if ($subcategorySupplier->suppliers->count() == 0) {
                $subcategorySupplier->delete();
                return redirect()->route('subcategory-supplier.index')->with('success', 'Subcategoría de proveedor eliminada correctamente');
            } else {
                return redirect()->route('subcategory-supplier.index')->with('error', 'No se puede eliminar la subcategoría de proveedor porque tiene proveedores asociados');
            }
        } catch (\Exception $e) {
            return redirect()->route('subcategory-supplier.index')->with('error', 'Error al eliminar la subcategoría de proveedor');
        }
    }
}
