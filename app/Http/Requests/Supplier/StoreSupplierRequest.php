<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
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
            'representant' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'description' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'representant.required' => 'El representante es requerido',
            'phone.required' => 'El teléfono es requerido',
            'category_supplier_id.required' => 'La categoría es requerida',
            'category_supplier_id.exists' => 'La categoría no existe',
            'subcategory_supplier_id.required' => 'La subcategoría es requerida',
            'subcategory_supplier_id.exists' => 'La subcategoría no existe',
        ];
    }
}