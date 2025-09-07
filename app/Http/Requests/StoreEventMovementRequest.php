<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventMovementRequest extends FormRequest
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
        $rules = [
            'description' => 'required|string|max:500',
            'date' => 'required|date',
            'type' => 'required|in:Ingreso,Egreso',
            'currency_id' => 'required|exists:currencies,id',
            'method_payment_id' => 'required|exists:method_payments,id',
            'amount' => 'required|numeric|min:0.01',
        ];

        // Validaciones específicas para Ingresos
        if ($this->input('type') === 'Ingreso') {
            $rules['type_income'] = 'required|exists:category_incomes,id';
            
            // Si es categoría de club (ID 1), requiere cuenta por cobrar
            if ($this->input('type_income') === '1') {
                $rules['account_receivable_id'] = 'required|exists:account_receivables,id';
            }
        }

        // Validaciones específicas para Egresos
        if ($this->input('type') === 'Egreso') {
            $rules['type_expense'] = 'required|exists:category_egresses,id';
            
            // Si es categoría de gastos (ID 1), requiere gasto registrado
            if ($this->input('type_expense') === '1') {
                $rules['expense_id'] = 'required|exists:expenses,id';
            }
            
            // Si es categoría de proveedor (ID 2), requiere cuenta por pagar
            if ($this->input('type_expense') === '2') {
                $rules['account_payable_id'] = 'required|exists:account_payables,id';
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'La descripción del movimiento es obligatoria.',
            'description.max' => 'La descripción no puede exceder los 500 caracteres.',
            
            'date.required' => 'La fecha del movimiento es obligatoria.',
            'date.date' => 'La fecha debe tener un formato válido.',
            
            'type.required' => 'El tipo de movimiento es obligatorio.',
            'type.in' => 'El tipo de movimiento debe ser Ingreso o Egreso.',
            
            'currency_id.required' => 'Debe seleccionar una moneda.',
            'currency_id.exists' => 'La moneda seleccionada no es válida.',
            
            'method_payment_id.required' => 'Debe seleccionar un método de pago.',
            'method_payment_id.exists' => 'El método de pago seleccionado no es válido.',
            
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'El monto debe ser un número válido.',
            'amount.min' => 'El monto debe ser mayor a 0.',
            
            // Mensajes para ingresos
            'type_income.required' => 'Debe seleccionar una categoría de ingreso.',
            'type_income.exists' => 'La categoría de ingreso seleccionada no es válida.',
            'account_receivable_id.required' => 'Debe seleccionar una cuenta por cobrar del club.',
            'account_receivable_id.exists' => 'La cuenta por cobrar seleccionada no es válida.',
            
            // Mensajes para egresos
            'type_expense.required' => 'Debe seleccionar una categoría de egreso.',
            'type_expense.exists' => 'La categoría de egreso seleccionada no es válida.',
            'expense_id.required' => 'Debe seleccionar un gasto registrado.',
            'expense_id.exists' => 'El gasto seleccionado no es válido.',
            'account_payable_id.required' => 'Debe seleccionar una cuenta por pagar del proveedor.',
            'account_payable_id.exists' => 'La cuenta por pagar seleccionada no es válida.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'description' => 'descripción',
            'date' => 'fecha',
            'type' => 'tipo de movimiento',
            'currency_id' => 'moneda',
            'method_payment_id' => 'método de pago',
            'amount' => 'monto',
            'type_income' => 'categoría de ingreso',
            'account_receivable_id' => 'cuenta por cobrar',
            'type_expense' => 'categoría de egreso',
            'expense_id' => 'gasto',
            'account_payable_id' => 'cuenta por pagar',
        ];
    }
}