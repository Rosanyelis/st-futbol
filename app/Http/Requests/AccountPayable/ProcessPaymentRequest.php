<?php

namespace App\Http\Requests\AccountPayable;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:0.01',
            'method_payment_id' => 'required|exists:method_payments,id',
        ];
    }

    public function messages()
    {
        return [
            'supplier_id.required' => 'El proveedor es requerido',
            'supplier_id.exists' => 'El proveedor no existe',
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto debe ser mayor a 0',
        ];
    }
}