<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryMethodPayment;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\CategoryPaymentMethod\StoreCategoryPaymentMethodRequest;
use App\Http\Requests\CategoryPaymentMethod\UpdateCategoryPaymentMethodRequest;

class CategoryMethodPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CategoryMethodPayment::all();
            return DataTables::of($data)
                ->addColumn('actions', function ($data) {
                    return view('category-method-payments.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }    
        return view('category-method-payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category-method-payments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryPaymentMethodRequest $request)
    {
        try {
            $categoryMethodPayment = CategoryMethodPayment::create($request->all());
            return redirect()->route('category-payment-method.index')->with('success', 'Categoría de método de pago creada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('category-payment-method.index')->with('error', 'Error al crear la categoría de método de pago');
        }
    }

   

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($categoryMethodPayment)
    {
        $categoryMethodPayment = CategoryMethodPayment::find($categoryMethodPayment);
        return view('category-method-payments.edit', ['categoryMethodPayment' => $categoryMethodPayment]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryPaymentMethodRequest $request, $categoryMethodPayment)
    {
        try {
            $categoryMethodPayment = CategoryMethodPayment::find($categoryMethodPayment);
            $categoryMethodPayment->update($request->all());
            return redirect()->route('category-payment-method.index')->with('success', 'Categoría de método de pago actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->route('category-payment-method.index')->with('error', 'Error al actualizar la categoría de método de pago');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($categoryMethodPayment)
    {
        try {
            $categoryMethodPayment = CategoryMethodPayment::find($categoryMethodPayment);
            if ($categoryMethodPayment->methodPayments->isEmpty()) {
                $categoryMethodPayment->delete();
                return redirect()->route('category-payment-method.index')->with('success', 'Categoría de método de pago eliminada correctamente');
            } else {
                return redirect()->route('category-payment-method.index')->with('error', 'No se puede eliminar la categoría de método de pago porque tiene movimientos asociados');
            }
        } catch (\Exception $e) {
            return $e;
        }
    }
}
