<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EventSupplierController extends Controller
{
    /**
     * Mostrar la vista para asignar proveedores a un evento
     */
    public function assignSuppliers($eventId)
    {
        $event = Event::findOrFail($eventId);
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $assignedSuppliers = $event->suppliers()->get();
        
        return view('events.assign-suppliers', compact('event', 'suppliers', 'assignedSuppliers'));
    }

    /**
     * Obtener los proveedores asignados a un evento (AJAX)
     */
    public function getAssignedSuppliers($eventId, Request $request)
    {
        if ($request->ajax()) {
            $event = Event::findOrFail($eventId);
            $assignedSuppliers = $event->suppliers()->get();
            
            return DataTables::of($assignedSuppliers)
                ->addColumn('actions', function ($supplier) use ($eventId) {
                    return view('events.assigned-supplier-actions', [
                        'eventId' => $eventId, 
                        'supplierId' => $supplier->id, 
                    ]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }

    /**
     * Asignar un proveedor a un evento
     */
    public function store(Request $request, $eventId)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        try {
            DB::beginTransaction();
            
            $event = Event::findOrFail($eventId);
            $supplier = Supplier::findOrFail($request->supplier_id);
            
            // Verificar si ya existe la relación
            $existingRelation = $event->suppliers()
                ->wherePivot('supplier_id', $supplier->id)
                ->exists();
            
            if ($existingRelation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este proveedor ya está asignado a este evento'
                ], 422);
            }
            
            // Asignar el proveedor al evento
            $event->assignSupplier($supplier);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Proveedor asignado correctamente al evento'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desasignar un proveedor de un evento
     */
    public function destroy($eventId, $supplierId, Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
        ]);

        try {
            DB::beginTransaction();
            
            $event = Event::findOrFail($eventId);
            $supplier = Supplier::findOrFail($supplierId);
            
            // Desasignar el proveedor del evento
            $event->detachSupplier($supplier);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Proveedor desasignado correctamente del evento'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar el proveedor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proveedores disponibles para asignar (AJAX)
     */
    public function getAvailableSuppliers($eventId, Request $request)
    {
        $event = Event::findOrFail($eventId);
        
        // Obtener proveedores que ya están asignados
        $assignedSupplierIds = $event->suppliers()->pluck('suppliers.id')->toArray();
        
        // Obtener todos los proveedores excepto los ya asignados
        $availableSuppliers = Supplier::whereNotIn('id', $assignedSupplierIds)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
        
        return response()->json($availableSuppliers);
    }
}
