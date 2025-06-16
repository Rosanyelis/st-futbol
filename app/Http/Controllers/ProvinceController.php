<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Province;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Province\StoreProvinceRequest;
use App\Http\Requests\Province\UpdateProvinceRequest;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $provinces = Province::with('country');
            return DataTables::of($provinces)
                ->addColumn('actions', function ($province) {
                    return view('provinces.actions', compact('province'));
                })
                ->make(true);
        }
        return view('provinces.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $countries = Country::all();
        return view('provinces.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProvinceRequest $request)
    {
        try {
            $province = Province::create($request->all());
            return redirect()->route('province.index')->with('success', 'Provincia creada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('province.index')->with('error', 'Error al crear la provincia');
        }
    }

    
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $province = Province::find($id);
        $countries = Country::all();
        return view('provinces.edit', compact('province', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProvinceRequest $request, string $id)
    {
        try {
            $province = Province::find($id);
            $province->update($request->all());
            return redirect()->route('province.index')->with('success', 'Provincia actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('province.index')->with('error', 'Error al actualizar la provincia');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $province = Province::find($id);
            if ($province->cities->count() > 0) {
                return redirect()->route('province.index')->with('error', 'No se puede eliminar la provincia porque tiene ciudades asociadas');
            }
            $province->delete();
            return redirect()->route('province.index')->with('success', 'Provincia eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('province.index')->with('error', 'Error al eliminar la provincia');
        }
    }
}
