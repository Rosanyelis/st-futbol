<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use Illuminate\Http\Request;
use App\Models\MethodPayment;
use App\Models\Currency;
use App\Models\CategoryMethodPayment;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\MethodPayment\StoreMethodPaymentRequest;
use App\Http\Requests\MethodPayment\UpdateMethodPaymentRequest;

class MethodPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = MethodPayment::with('categoryMethodPayment', 'entity', 'currency')->get();
            return DataTables::of($data)
                ->addColumn('category_method_payment_name', function ($data) {
                    return $data->categoryMethodPayment->name;
                })
                ->addColumn('entity_name', function ($data) {
                    return $data->entity->name;
                })
                ->addColumn('initial_balance', function ($data) {
                    return number_format($data->initial_balance, 0, ',', '.') . ' ' . $data->currency->symbol;
                })
                ->addColumn('current_balance', function ($data) {
                    return number_format($data->current_balance, 0, ',', '.') . ' ' . $data->currency->symbol;
                })
                ->addColumn('actions', function ($data) {
                    return view('method-payments.actions', ['id' => $data->id]);
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('method-payments.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entities = Entity::all();
        $currencies = Currency::all();
        $categoryMethodPayments = CategoryMethodPayment::all();
        return view('method-payments.create', ['entities' => $entities, 'currencies' => $currencies, 'categoryMethodPayments' => $categoryMethodPayments]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMethodPaymentRequest $request)
    {
        try {
            $data = $request->validated();
            $data['current_balance'] = $data['initial_balance'];    
            $methodPayment = MethodPayment::create($data);
            return redirect()->route('method-payment.index')->with('success', 'Método de pago creado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('method-payment.index')->with('error', 'Error al crear el método de pago');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($methodPayment)
    {
        $methodPayment = MethodPayment::find($methodPayment);
        $entities = Entity::all();
        $categoryMethodPayments = CategoryMethodPayment::all();
        return view('method-payments.edit', ['methodPayment' => $methodPayment, 'entities' => $entities, 'categoryMethodPayments' => $categoryMethodPayments]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($methodPayment)
    {
        try {
            $methodPayment = MethodPayment::find($methodPayment);
            $entities = Entity::all();
            $currencies = Currency::all();
            $categoryMethodPayments = CategoryMethodPayment::all();
            return view('method-payments.edit', ['methodPayment' => $methodPayment, 'entities' => $entities, 'currencies' => $currencies, 'categoryMethodPayments' => $categoryMethodPayments]);
        } catch (\Exception $e) {
            return redirect()->route('method-payment.index')->with('error', 'Error al editar el método de pago');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMethodPaymentRequest $request, $methodPayment)
    {
        try {
            $methodPayment = MethodPayment::find($methodPayment);   
            $data = $request->validated();
            $data['current_balance'] = $data['initial_balance'];
            $methodPayment->update($data);
            return redirect()->route('method-payment.index')->with('success', 'Método de pago actualizado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('method-payment.index')->with('error', 'Error al actualizar el método de pago');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($methodPayment)
    {
        try {
            $methodPayment = MethodPayment::find($methodPayment);
            if (!$methodPayment->event_movements->isEmpty()) {
                return redirect()->route('method-payment.index')->with('error', 'No puede ser eliminado. El método de pago tiene movimientos asociados');
            }
            $methodPayment->delete();
            return redirect()->route('method-payment.index')->with('success', 'Método de pago eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('method-payment.index')->with('error', 'Error al eliminar el método de pago');
        }
    }
}
