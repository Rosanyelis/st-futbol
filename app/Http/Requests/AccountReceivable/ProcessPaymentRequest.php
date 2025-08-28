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
            'receivable_id' => 'required|exists:account_receivables,id',
            'method_payment_id' => 'nullable|exists:method_payments,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'payment_reference' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'receivable_id.required' => 'La cuenta por cobrar es requerida.',
            'receivable_id.exists' => 'La cuenta por cobrar seleccionada no existe.',
            'method_payment_id.exists' => 'El método de pago seleccionado no existe.',
            'amount.required' => 'El monto es requerido.',
            'amount.numeric' => 'El monto debe ser un número.',
            'amount.min' => 'El monto debe ser mayor a 0.',
            'date.required' => 'La fecha es requerida.',
            'date.date' => 'La fecha debe tener un formato válido.',
            'description.max' => 'La descripción no puede exceder los 500 caracteres.',
            'payment_reference.max' => 'La referencia del pago no puede exceder los 255 caracteres.',
        ];
    }
} 