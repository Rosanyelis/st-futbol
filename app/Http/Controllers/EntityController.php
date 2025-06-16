<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Entity\StoreEntityRequest;
use App\Http\Requests\Entity\UpdateEntityRequest;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Entity::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('entities.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('entities.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('entities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEntityRequest $request)
    {
        try {
            $entity = Entity::create($request->all());
            return redirect()->route('entity.index')->with('success', 'Entidad creada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('entity.index')->with('error', 'Error al crear la entidad');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($entity)
    {
        $entity = Entity::find($entity);
        return view('entities.edit', ['entity' => $entity]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEntityRequest $request, $entity)
    {
        try {
            $entity = Entity::find($entity);
            $entity->update($request->all());
            return redirect()->route('entity.index')->with('success', 'Entidad actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('entity.index')->with('error', 'Error al actualizar la entidad');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($entity)
    {
        try {
            $entity = Entity::find($entity);
            $entity->delete();
            return redirect()->route('entity.index')->with('success', 'Entidad eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('entity.index')->with('error', 'Error al eliminar la entidad');
        }
    }
}
