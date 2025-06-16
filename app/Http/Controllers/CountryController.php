<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Country\StoreCountryRequest;
use App\Http\Requests\Country\UpdateCountryRequest;

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $countries = Country::select('id', 'name', 'code');
            return DataTables::of($countries)
                ->addColumn('actions', function ($country) {
                    return view('countries.actions', compact('country'));
                })
                ->make(true);
        }
        return view('countries.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('countries.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request)
    {
        try {
            $country = Country::create($request->all());
            return redirect()->route('country.index')->with('success', 'País creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('country.index')->with('error', 'Error al crear el país');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $country = Country::find($id);
        return view('countries.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCountryRequest $request, string $id)
    {
        try {
            $country = Country::find($id);
            $country->update($request->all());
            return redirect()->route('country.index')->with('success', 'País actualizado correctamente');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $country = Country::find($id);
            if ($country->provinces->count() > 0) {
                return response()->json(['error' => 'No se puede eliminar el país porque tiene provincias asociadas'], 400);
            }
            $country->delete();
            return response()->json(['message' => 'País eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
