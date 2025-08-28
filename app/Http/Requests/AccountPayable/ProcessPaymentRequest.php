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
            'account_payable_id' => 'required|exists:account_payables,id',
            'amount' => 'required|numeric|min:0.01',
            'method_payment_id' => 'required|exists:method_payments,id',
            'date' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'account_payable_id.required' => 'La cuenta por pagar es requerida',
            'account_payable_id.exists' => 'La cuenta por pagar no existe',
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto debe ser mayor a 0',
            'method_payment_id.required' => 'El método de pago es requerido',
            'method_payment_id.exists' => 'El método de pago no existe',
            'date.date' => 'La fecha debe tener un formato válido',
        ];
    }
}