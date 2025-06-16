<?php

namespace App\Http\Requests\MethodPayment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMethodPaymentRequest extends FormRequest
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
            'category_method_payment_id' => 'required|exists:category_method_payments,id',
            'entity_id' => 'required|exists:entities,id',
            'currency_id' => 'required|exists:currencies,id',
            'alias' => 'required|string|max:255',
            'account_holder' => 'required|string|max:255',
            'type_account' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'cbu_cvu' => 'required|string|max:255',
            'initial_balance' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'category_method_payment_id.required' => 'La categoría de método de pago es requerida',
            'category_method_payment_id.exists' => 'La categoría de método de pago no existe',
            'entity_id.required' => 'La entidad es requerida',
            'entity_id.exists' => 'La entidad no existe',
            'currency_id.required' => 'La moneda es requerida',
            'currency_id.exists' => 'La moneda no existe',
            'alias.required' => 'El alias es requerido',
            'account_holder.required' => 'El titular es requerido',
            'type_account.required' => 'El tipo de cuenta es requerido',
            'account_number.required' => 'El número de cuenta es requerido',
            'cbu_cvu.required' => 'El CBU/CVU es requerido',
            'initial_balance.required' => 'El saldo inicial es requerido',
            'initial_balance.numeric' => 'El saldo inicial debe ser un número',
            'initial_balance.min' => 'El saldo inicial debe ser mayor a 0',
            ];
    }
}
