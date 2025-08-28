<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Event;
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
            $data = Supplier::withCount('events')->get();
            return DataTables::of($data)
                ->addColumn('category_supplier_name', function ($data) {
                    return $data->categorySupplier->name ?? '';
                })
                ->addColumn('subcategory_supplier_name', function ($data) {
                    return $data->subcategorySupplier->name ?? '';
                })
                ->addColumn('events_count', function ($data) {
                    return $data->events_count ?? 0;
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
        $categorySuppliers = CategorySupplier::orderBy('name', 'asc')->get();
        return view('suppliers.create', compact('categorySuppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        try {
            $data = $request->all();
            $supplier = Supplier::create($data);
            return redirect()->route('supplier.index')->with('success', 'Proveedor creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('supplier.index')->with('error', 'Error al crear el proveedor: ' . $e->getMessage());
        }
    }

    public function getSubcategorySuppliers(Request $request)
    {
        $subcategorySuppliers = SubcategorySupplier::where('category_supplier_id', $request->category_supplier_id)->orderBy('name', 'asc')->get();
        return response()->json($subcategorySuppliers);
    }

    /**
     * Display the specified resource.
     */
    public function show($supplier)
    {
        $supplier = Supplier::find($supplier);
        $supplier->load(['categorySupplier', 'subcategorySupplier']);
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($supplier)
    {
        $supplier = Supplier::find($supplier);
        $categorySuppliers = CategorySupplier::all();
        return view('suppliers.edit', compact('supplier', 'categorySuppliers'));    
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

    /**
     * Obtener eventos disponibles para asignar a un proveedor
     */
    public function getAvailableEvents($supplierId)
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);
            
            // Obtener todos los eventos
            $allEvents = Event::orderBy('name', 'asc')->get();
            
            // Obtener eventos ya asignados al proveedor
            $assignedEvents = $supplier->events()->pluck('events.id')->toArray();
            
            // Filtrar eventos no asignados
            $availableEvents = $allEvents->whereNotIn('id', $assignedEvents);
            
            return response()->json($availableEvents->values());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener eventos disponibles'], 500);
        }
    }

    /**
     * Asignar un evento a un proveedor
     */
    public function assignEventToSupplier(Request $request, $supplierId)
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);
            $eventId = $request->input('event_id');
            
            // Validar que el evento existe
            $event = Event::findOrFail($eventId);
            
            // Verificar si ya está asignado
            $existingAssignment = $supplier->events()
                ->where('events.id', $eventId)
                ->exists();
            
            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este evento ya está asignado al proveedor'
                ], 400);
            }
            
            // Asignar el evento al proveedor
            $supplier->assignEvent($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Evento asignado correctamente al proveedor'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el evento: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Obtener eventos asignados a un proveedor
     */
    public function getAssignedEvents($supplierId, Request $request)
    {
        if ($request->ajax()) {
            $supplier = Supplier::findOrFail($supplierId);
            $assignedEvents = $supplier->events()->get();
            
            return DataTables::of($assignedEvents)
                ->addColumn('payables_count', function ($event) use ($supplierId) {
                    // Contar cuentas por pagar del proveedor para este evento
                    return \App\Models\AccountPayable::where('supplier_id', $supplierId)
                        ->where('event_id', $event->id)
                        ->count();
                })
                ->addColumn('can_delete', function ($event) use ($supplierId) {
                    // Verificar si se puede eliminar (no tiene cuentas por pagar)
                    $payablesCount = \App\Models\AccountPayable::where('supplier_id', $supplierId)
                        ->where('event_id', $event->id)
                        ->count();
                    return $payablesCount === 0;
                })
                ->addColumn('actions', function ($event) use ($supplierId) {
                    $payablesCount = \App\Models\AccountPayable::where('supplier_id', $supplierId)
                        ->where('event_id', $event->id)
                        ->count();
                    $canDelete = $payablesCount === 0;
                    
                    return view('suppliers.assigned-event-actions', [
                        'supplierId' => $supplierId, 
                        'eventId' => $event->id,
                        'canDelete' => $canDelete
                    ]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }

    /**
     * Eliminar asignación de evento a proveedor
     */
    public function deleteEventAssignment($supplierId, $eventId, Request $request)
    {
        try {
            $supplier = Supplier::findOrFail($supplierId);
            $event = Event::findOrFail($eventId);
            
            // Verificar si tiene cuentas por pagar
            $payablesCount = \App\Models\AccountPayable::where('supplier_id', $supplierId)
                ->where('event_id', $eventId)
                ->count();
            
            if ($payablesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la asignación porque el proveedor tiene cuentas por pagar en este evento'
                ], 400);
            }
            
            // Eliminar la asignación
            $supplier->detachEvent($event);
            
            return response()->json([
                'success' => true,
                'message' => 'Asignación eliminada correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la asignación: ' . $e->getMessage()
            ], 500);
        }
    }
}
