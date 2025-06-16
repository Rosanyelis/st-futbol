<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Currencies\StoreCurrencyRequest;
use App\Http\Requests\Currencies\UpdateCurrencyRequest;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Currency::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('currencies.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('currencies.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCurrencyRequest $request)
    {
        try {   
            Currency::create($request->all());
            return redirect()->route('currency.index')->with('success', 'Moneda creada correctamente'); 
        } catch (\Exception $e) {
            return redirect()->route('currency.index')->with('error', 'Error al crear la moneda');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($currency)
    {
        $data = Currency::find($currency);
        return view('currencies.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrencyRequest $request, $currency)
    {
        try {
            $data = $request->all();
            $currency = Currency::find($currency);
            $currency->update($data);
            return redirect()->route('currency.index')->with('success', 'Moneda actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('currency.index')->with('error', 'Error al actualizar la moneda');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($currency)
    {
        try {
            $currency = Currency::find($currency);
            if($currency->clubs->count() > 0){
                return redirect()->route('currency.index')->with('error', 'No se puede eliminar la moneda porque tiene clubes asociados');
            }
            $currency->delete();
            return redirect()->route('currency.index')->with('success', 'Moneda eliminada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('currency.index')->with('error', 'Error al eliminar la moneda');
        }
    }
}
