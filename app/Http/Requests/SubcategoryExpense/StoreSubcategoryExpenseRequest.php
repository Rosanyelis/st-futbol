<?php

namespace App\Http\Requests\SubcategoryExpense;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubcategoryExpenseRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:subcategory_expenses,name',
        ];
    }

    public function messages(): array
    {
        return [
            'category_expense_id.required' => 'La categoría de gasto es requerida',
            'category_expense_id.exists' => 'La categoría de gasto no existe',
            'name.required' => 'El nombre es requerido',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre debe tener menos de 255 caracteres',
            'name.unique' => 'La subcategoría de gasto ya existe',
        ];
    }
}
