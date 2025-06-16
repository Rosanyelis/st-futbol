<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
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
            'category_supplier_id' => 'required|exists:category_suppliers,id',
            'subcategory_supplier_id' => 'required|exists:subcategory_suppliers,id',
            'currency_id' => 'required|exists:currencies,id',
            'representant' => 'required',
            'phone' => 'required',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'category_supplier_id.required' => 'La categoría de proveedor es requerida',
            'category_supplier_id.exists' => 'La categoría de proveedor no existe',
            'subcategory_supplier_id.required' => 'La subcategoría de proveedor es requerida',
            'subcategory_supplier_id.exists' => 'La subcategoría de proveedor no existe',
            'currency_id.required' => 'La moneda es requerida',
            'currency_id.exists' => 'La moneda no existe',
            'representant.required' => 'El representante es requerido',
            'phone.required' => 'El teléfono es requerido',
            'amount.numeric' => 'El monto debe ser un número',
            'amount.min' => 'El monto debe ser mayor a 0',
            'description.nullable' => 'La descripción es opcional',
        ];
    }
}
