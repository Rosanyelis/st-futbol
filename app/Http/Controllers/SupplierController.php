<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Currency;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\CategorySupplier;
use App\Models\SubcategorySupplier;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::table('suppliers')
                ->leftJoin('category_suppliers', 'suppliers.category_supplier_id', '=', 'category_suppliers.id')
                ->leftJoin('subcategory_suppliers', 'suppliers.subcategory_supplier_id', '=', 'subcategory_suppliers.id')
                ->leftJoin('currencies', 'suppliers.currency_id', '=', 'currencies.id')
                ->select(
                    'suppliers.*',
                    'category_suppliers.name as category_supplier_name',
                    'subcategory_suppliers.name as subcategory_supplier_name',
                    DB::raw('CONCAT(currencies.name, " (", currencies.symbol, ")") as currency_name'),
                );

            return DataTables::of($data)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->search['value'] != '') {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('suppliers.name', 'like', "%{$search}%")
                                ->orWhere('category_suppliers.name', 'like', "%{$search}%")
                                ->orWhere('subcategory_suppliers.name', 'like', "%{$search}%")
                                ->orWhere('currencies.name', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('actions', function ($data) {
                    return view('suppliers.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('suppliers.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $events = Event::all();
        $currencies = Currency::all();
        $categorySuppliers = CategorySupplier::all();
        return view('suppliers.create', compact('events', 'currencies', 'categorySuppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            $data = $request->all();
            $data['amount'] = str_replace(',', '', $data['amount']);
            $data['amount'] = str_replace('.', '', $data['amount']);
            $data['amount'] = floatval($data['amount']);
            $data['category_egress_id'] = 2; // Egreso de proveedores
            $supplier = Supplier::create($data);
            return redirect()->route('supplier.index')->with('success', 'Proveedor creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
    }

    public function getSubcategorySuppliers(Request $request)
    {
        $subcategorySuppliers = SubcategorySupplier::where('category_supplier_id', $request->category_supplier_id)->get();
        return response()->json($subcategorySuppliers);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($supplier)
    {
        $supplier = Supplier::find($supplier);
        $events = Event::all();
        $categorySuppliers = CategorySupplier::all();
        $currencies = Currency::all();
        return view('suppliers.edit', compact('supplier', 'events', 'categorySuppliers', 'currencies'));    
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, $supplier)
    {
        try {
            $supplier = Supplier::find($supplier);
            $supplier->update($request->validated());
            return redirect()->route('supplier.index')->with('success', 'Proveedor actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', 'Error al actualizar el proveedor');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($supplier)
    {
        try {
            $supplier = Supplier::find($supplier);
            $supplier->delete();
            return redirect()->route('supplier.index')->with('success', 'Proveedor eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', 'Error al eliminar el proveedor');
        }
    }
}
