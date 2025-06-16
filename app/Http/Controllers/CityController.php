<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\City\StoreCityRequest;
use App\Http\Requests\City\UpdateCityRequest;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $cities = City::with('province');
            return DataTables::of($cities)
                ->addColumn('actions', function ($city) {
                    return view('cities.actions', compact('city'));
                })
                ->make(true);
        }
        return view('cities.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $provinces = Province::all();
        return view('cities.create', compact('provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCityRequest $request)
    {
        try {
            $city = City::create($request->all());
            return redirect()->route('city.index')->with('success', 'Ciudad creada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('city.index')->with('error', 'Error al crear la ciudad');
        }
    }

    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $city = City::find($id);
        $provinces = Province::all();
        return view('cities.edit', compact('city', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCityRequest $request, string $id)
    {
        try {
            $city = City::find($id);
            $city->update($request->all());
            return redirect()->route('city.index')->with('success', 'Ciudad actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('city.index')->with('error', 'Error al actualizar la ciudad');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $city = City::find($id);
            if ($city->clubs->count() > 0) {
                return redirect()->route('city.index')->with('error', 'No se puede eliminar la ciudad porque tiene clubes asociados');
            }
            $city->delete();
            return redirect()->route('city.index')->with('success', 'Ciudad eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('city.index')->with('error', 'Error al eliminar la ciudad');
        }
    }
}
