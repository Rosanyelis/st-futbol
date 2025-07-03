<?php

namespace App\Http\Requests\AccountReceivable;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'club_id' => 'required|exists:clubs,id',
            'amount' => 'required|numeric|min:0.01',
            'method_payment_id' => 'required|exists:method_payments,id',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'club_id.required' => 'El club es requerido',
            'club_id.exists' => 'El club seleccionado no existe',
            'amount.required' => 'El monto es requerido',
            'amount.numeric' => 'El monto debe ser un número válido',
            'amount.min' => 'El monto debe ser mayor a 0',
            'method_payment_id.required' => 'Debe seleccionar un método de pago',
            'method_payment_id.exists' => 'El método de pago seleccionado no existe',
        ];
    }
} 