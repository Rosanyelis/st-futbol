<?php

namespace App\Http\Controllers;

use App\Models\HistoryChangeCurrency;
use App\Models\Currency;
use App\Models\MethodPayment;
use App\Models\BussinesMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class HistoryChangeCurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = HistoryChangeCurrency::with(['currency', 'methodPayment', 'currencyReceptor', 'methodPaymentReceptor']);
            return DataTables::of($data)
                ->addColumn('origin_currency', function ($row) {
                    return $row->getOriginCurrencyName();
                })
                ->addColumn('origin_method', function ($row) {
                    return $row->getOriginMethodPaymentName();
                })
                ->addColumn('destination_currency', function ($row) {
                    return $row->getDestinationCurrencyName();
                })
                ->addColumn('destination_method', function ($row) {
                    return $row->getDestinationMethodPaymentName();
                })
                ->addColumn('formatted_amount', function ($row) {
                    return number_format($row->amount, 2, ',', '.');
                })
                ->addColumn('formatted_amount_converted', function ($row) {
                    return number_format($row->amount_converted, 2, ',', '.');
                })
                ->addColumn('formatted_exchange_rate', function ($row) {
                    return number_format($row->exchange_rate, 2, ',', '.');
                })
                ->addColumn('actions', function ($row) {
                    return view('history-change-currency.actions', compact('row'));
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        
        return view('history-change-currency.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currencies = Currency::all();
        $methodPayments = MethodPayment::all();
        
        return view('history-change-currency.create', compact('currencies', 'methodPayments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'method_payment_id' => 'required|exists:method_payments,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'method_payment_receptor_id' => 'required|exists:method_payments,id',
            'currency_receptor_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Limpiar formato de números antes de guardar
            $amount = str_replace(['.', ','], ['', '.'], $request->amount);
            $exchangeRate = str_replace(['.', ','], ['', '.'], $request->exchange_rate);
            
            // Calcular monto convertido
            $amountConverted = (float) $amount * (float) $exchangeRate;
            
            // Obtener métodos de pago
            $originMethod = MethodPayment::findOrFail($request->method_payment_id);
            $destinationMethod = MethodPayment::findOrFail($request->method_payment_receptor_id);
            
            // Verificar saldo suficiente en el método de pago origen
            if ($originMethod->current_balance < (float) $amount) {
                throw new \Exception('El método de pago origen no tiene saldo suficiente');
            }
            
            // Crear el registro de cambio de moneda
            $historyChangeCurrency = HistoryChangeCurrency::create([
                'currency_id' => $request->currency_id,
                'method_payment_id' => $request->method_payment_id,
                'date' => $request->date,
                'amount' => (float) $amount,
                'method_payment_receptor_id' => $request->method_payment_receptor_id,
                'currency_receptor_id' => $request->currency_receptor_id,
                'exchange_rate' => (float) $exchangeRate,
                'amount_converted' => $amountConverted,
                'description' => $request->description,
            ]);

            // Decrementar saldo del método de pago origen
            $originMethod->update([
                'current_balance' => $originMethod->current_balance - (float) $amount
            ]);

            // Incrementar saldo del método de pago destino
            $destinationMethod->update([
                'current_balance' => $destinationMethod->current_balance + $amountConverted
            ]);

            // Registrar movimiento de salida en el método origen
            BussinesMovement::create([
                'bussines_id' => 1, // ID del negocio
                'method_payment_id' => $originMethod->id,
                'currency_id' => $request->currency_id,
                'user_id' => auth()->id(), // Usuario que realiza la operación
                'amount' => (float) $amount,
                'date' => $request->date,
                'description' => 'Cambio de moneda - Salida: ' . ($request->description ?: 'Sin descripción') . 
                                ' | Moneda origen: ' . $originMethod->currency->name . 
                                ' | Método: ' . $originMethod->account_holder,
                'type' => 'Egreso',
            ]);

            // Registrar movimiento de entrada en el método destino
            BussinesMovement::create([
                'bussines_id' => 1, // ID del negocio
                'method_payment_id' => $destinationMethod->id,
                'currency_id' => $request->currency_receptor_id,
                'user_id' => auth()->id(), // Usuario que realiza la operación
                'amount' => $amountConverted,
                'date' => $request->date,
                'description' => 'Cambio de moneda - Entrada: ' . ($request->description ?: 'Sin descripción') . 
                                ' | Moneda destino: ' . $destinationMethod->currency->name . 
                                ' | Método: ' . $destinationMethod->account_holder . 
                                ' | Tasa: ' . number_format($exchangeRate, 2),
                'type' => 'Ingreso',
            ]);

            DB::commit();

            return redirect()->route('history-change-currency.index')
                ->with('success', 'Cambio de moneda realizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al realizar el cambio de moneda: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $historyChangeCurrency = HistoryChangeCurrency::findOrFail($id);
        $currencies = Currency::all();
        $methodPayments = MethodPayment::all();
        
        return view('history-change-currency.edit', compact('historyChangeCurrency', 'currencies', 'methodPayments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $historyChangeCurrency = HistoryChangeCurrency::findOrFail($id);
        
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'method_payment_id' => 'required|exists:method_payments,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'method_payment_receptor_id' => 'required|exists:method_payments,id',
            'currency_receptor_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        
        try {
            // Limpiar formato de números antes de guardar
            $amount = str_replace(['.', ','], ['', '.'], $request->amount);
            $exchangeRate = str_replace(['.', ','], ['', '.'], $request->exchange_rate);
            
            // Calcular monto convertido
            $amountConverted = (float) $amount * (float) $exchangeRate;
            
            // Obtener métodos de pago
            $originMethod = MethodPayment::findOrFail($request->method_payment_id);
            $destinationMethod = MethodPayment::findOrFail($request->method_payment_receptor_id);
            
            // Revertir cambios anteriores
            $oldOriginMethod = MethodPayment::find($historyChangeCurrency->method_payment_id);
            $oldDestinationMethod = MethodPayment::find($historyChangeCurrency->method_payment_receptor_id);
            
            if ($oldOriginMethod) {
                $oldOriginMethod->update([
                    'current_balance' => $oldOriginMethod->current_balance + $historyChangeCurrency->amount
                ]);
            }
            
            if ($oldDestinationMethod) {
                $oldDestinationMethod->update([
                    'current_balance' => $oldDestinationMethod->current_balance - $historyChangeCurrency->amount_converted
                ]);
            }
            
            // Verificar saldo suficiente en el método de pago origen
            if ($originMethod->current_balance < (float) $amount) {
                throw new \Exception('El método de pago origen no tiene saldo suficiente');
            }
            
            // Actualizar el registro
            $historyChangeCurrency->update([
                'currency_id' => $request->currency_id,
                'method_payment_id' => $request->method_payment_id,
                'date' => $request->date,
                'amount' => (float) $amount,
                'method_payment_receptor_id' => $request->method_payment_receptor_id,
                'currency_receptor_id' => $request->currency_receptor_id,
                'exchange_rate' => (float) $exchangeRate,
                'amount_converted' => $amountConverted,
                'description' => $request->description,
            ]);

            // Aplicar nuevos cambios
            $originMethod->update([
                'current_balance' => $originMethod->current_balance - (float) $amount
            ]);

            $destinationMethod->update([
                'current_balance' => $destinationMethod->current_balance + $amountConverted
            ]);

            // Registrar nuevos movimientos en BussinesMovement
            BussinesMovement::create([
                'bussines_id' => 1, // ID del negocio
                'method_payment_id' => $originMethod->id,
                'currency_id' => $request->currency_id,
                'user_id' => auth()->id(), // Usuario que realiza la operación
                'amount' => (float) $amount,
                'date' => $request->date,
                'description' => 'Cambio de moneda actualizado - Salida: ' . ($request->description ?: 'Sin descripción') . 
                                ' | Moneda origen: ' . $originMethod->currency->name . 
                                ' | Método: ' . $originMethod->account_holder,
                'type' => 'Egreso',
            ]);

            BussinesMovement::create([
                'bussines_id' => 1, // ID del negocio
                'method_payment_id' => $destinationMethod->id,
                'currency_id' => $request->currency_receptor_id,
                'user_id' => auth()->id(), // Usuario que realiza la operación
                'amount' => $amountConverted,
                'date' => $request->date,
                'description' => 'Cambio de moneda actualizado - Entrada: ' . ($request->description ?: 'Sin descripción') . 
                                ' | Moneda destino: ' . $destinationMethod->currency->name . 
                                ' | Método: ' . $destinationMethod->account_holder . 
                                ' | Tasa: ' . number_format($exchangeRate, 2),
                'type' => 'Ingreso',
            ]);

            DB::commit();

            return redirect()->route('history-change-currency.index')
                ->with('success', 'Cambio de moneda actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error al actualizar el cambio de moneda: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $historyChangeCurrency = HistoryChangeCurrency::findOrFail($id);

        DB::beginTransaction();
        
        try {
            // Revertir cambios en los métodos de pago
            $originMethod = MethodPayment::find($historyChangeCurrency->method_payment_id);
            $destinationMethod = MethodPayment::find($historyChangeCurrency->method_payment_receptor_id);
            
            if ($originMethod) {
                $originMethod->update([
                    'current_balance' => $originMethod->current_balance + $historyChangeCurrency->amount
                ]);
            }
            
            if ($destinationMethod) {
                $destinationMethod->update([
                    'current_balance' => $destinationMethod->current_balance - $historyChangeCurrency->amount_converted
                ]);
            }

            // Registrar movimientos de reversión en BussinesMovement
            if ($originMethod) {
                BussinesMovement::create([
                    'bussines_id' => 1, // ID del negocio
                    'method_payment_id' => $originMethod->id,
                    'currency_id' => $historyChangeCurrency->currency_id,
                    'user_id' => auth()->id(), // Usuario que realiza la operación
                    'amount' => $historyChangeCurrency->amount,
                    'date' => now(),
                    'description' => 'Reversión de cambio de moneda - Devolución: ' . ($historyChangeCurrency->description ?: 'Sin descripción') . 
                                    ' | Moneda: ' . $historyChangeCurrency->currency->name . 
                                    ' | Método: ' . $originMethod->account_holder,
                    'type' => 'Ingreso',
                ]);
            }

            if ($destinationMethod) {
                BussinesMovement::create([
                    'bussines_id' => 1, // ID del negocio
                    'method_payment_id' => $destinationMethod->id,
                    'currency_id' => $historyChangeCurrency->currency_receptor_id,
                    'user_id' => auth()->id(), // Usuario que realiza la operación
                    'amount' => $historyChangeCurrency->amount_converted,
                    'date' => now(),
                    'description' => 'Reversión de cambio de moneda - Devolución: ' . ($historyChangeCurrency->description ?: 'Sin descripción') . 
                                    ' | Moneda: ' . $historyChangeCurrency->currencyReceptor->name . 
                                    ' | Método: ' . $destinationMethod->account_holder,
                    'type' => 'Egreso',
                ]);
            }
            
            // Eliminar el registro
            $historyChangeCurrency->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cambio de moneda cancelado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el cambio de moneda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener métodos de pago por moneda
     */
    public function getMethodPaymentsByCurrency($currencyId)
    {
        $methodPayments = MethodPayment::where('currency_id', $currencyId)->get(['id', 'account_holder']);
        return response()->json($methodPayments);
    }
}
