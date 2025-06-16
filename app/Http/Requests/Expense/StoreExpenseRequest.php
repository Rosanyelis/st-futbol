<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
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
            'category_expense_id' => 'required|exists:category_expenses,id',
            'subcategory_expense_id' => 'required|exists:subcategory_expenses,id',
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'category_expense_id.required' => 'La categoría de gasto es requerida',
            'category_expense_id.exists' => 'La categoría de gasto no existe',
            'subcategory_expense_id.required' => 'La subcategoría de gasto es requerida',
            'subcategory_expense_id.exists' => 'La subcategoría de gasto no existe',
            'description.string' => 'La descripción debe ser una cadena de texto',
            'description.max' => 'La descripción debe tener menos de 255 caracteres',
        ];
    }
}
