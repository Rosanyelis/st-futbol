<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Events\StoreEventRequest;
use App\Http\Requests\Events\UpdateEventRequest;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Event::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('events.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('events.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEventRequest $request)
    {
        try {
            $data = $request->all();
            $data['url_images'] = $this->saveFile($request->file('url_images'), 'events/');
            Event::create($data);
            return redirect()->route('event.index')->with('success', 'Evento creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('event.index')->with('error', 'Error al crear el evento');
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Event::find($id);
        return view('events.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEventRequest $request, $event)
    {
        try {
            $event = Event::find($event);
            $data = $request->all();
            if($request->hasFile('url_images')){
                $data['url_images'] = $this->saveFile($request->file('url_images'), 'events/');
            }
            $event->update($data);
            return redirect()->route('event.index')->with('success', 'Evento actualizado correctamente');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($event)
    {
        try {
            $event = Event::find($event);
            if($event->clubs->count() > 0){
                return redirect()->route('event.index')->with('error', 'No se puede eliminar el evento porque tiene clubes asociados');
            }
            if ($event->url_images) {
                Storage::delete('public/' . $event->url_images);
            }
            $event->delete();
            return redirect()->route('event.index')->with('success', 'Evento eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('event.index')->with('error', 'Error al eliminar el evento');
        }
    }

    private function saveFile($file, $path)
    {
        try {
            if (!$file) {
                return null;
            }

            // Generar un nombre único para el archivo
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Crear el directorio si no existe
            $fullPath = storage_path('app/public/' . $path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Mover el archivo al directorio de almacenamiento
            $file->move($fullPath, $fileName);

            // Retornar la ruta relativa para guardar en la base de datos
            return $path . $fileName;
        } catch (\Exception $e) {
            throw new \Exception('Error al guardar la imagen: ' . $e->getMessage());
        }
    }
}
