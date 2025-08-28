<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class EventClubController extends Controller
{
    /**
     * Mostrar la vista para asignar clubs a un evento
     */
    public function assignClubs($eventId)
    {
        $event = Event::findOrFail($eventId);
        $clubs = Club::orderBy('name', 'asc')->get();
        $assignedClubs = $event->clubs()->get();
        
        return view('events.assign-clubs', compact('event', 'clubs', 'assignedClubs'));
    }

    /**
     * Obtener los clubs asignados a un evento (AJAX)
     */
    public function getAssignedClubs($eventId, Request $request)
    {
        if ($request->ajax()) {
            $event = Event::findOrFail($eventId);
            $assignedClubs = $event->clubs()->get();
            
            return DataTables::of($assignedClubs)
                ->addColumn('actions', function ($club) use ($eventId) {
                    return view('events.assigned-club-actions', [
                        'eventId' => $eventId, 
                        'clubId' => $club->id, 
                    ]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }

    /**
     * Asignar un club a un evento
     */
    public function store(Request $request, $eventId)
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
        ]);

        try {
            DB::beginTransaction();
            
            $event = Event::findOrFail($eventId);
            $club = Club::findOrFail($request->club_id);
            
            // Verificar si ya existe la relación para ese año
            $existingRelation = $event->clubs()
                ->wherePivot('club_id', $club->id)
                ->exists();
            
            if ($existingRelation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este club ya está asignado a este evento'
                ], 422);
            }
            
            // Asignar el club al evento
            $event->assignClub($club);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Club asignado correctamente al evento'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar el club: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desasignar un club de un evento
     */
    public function destroy($eventId, $clubId, Request $request)
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
        ]);

        try {
            DB::beginTransaction();
            
            $event = Event::findOrFail($eventId);
            $club = Club::findOrFail($clubId);
            
            // Desasignar el club del evento
            $event->detachClub($club);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Club desasignado correctamente del evento'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al desasignar el club: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clubs disponibles para asignar (AJAX)
     */
    public function getAvailableClubs($eventId, Request $request)
    {
        $event = Event::findOrFail($eventId);
        
        // Obtener clubs que ya están asignados
        $assignedClubIds = $event->clubs()->pluck('clubs.id')->toArray();
        
        // Obtener todos los clubs excepto los ya asignados
        $availableClubs = Club::whereNotIn('id', $assignedClubIds)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);
        
        return response()->json($availableClubs);
    }
} 