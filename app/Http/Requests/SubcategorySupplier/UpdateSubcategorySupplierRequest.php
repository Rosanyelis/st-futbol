<?php

namespace App\Http\Requests\SubcategorySupplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubcategorySupplierRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:subcategory_suppliers,name,' . $this->id,
            'category_supplier_id' => 'required|exists:category_suppliers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es requerido',
            'name.unique' => 'El nombre ya existe',
            'name.string' => 'El nombre debe ser una cadena de texto',
            'name.max' => 'El nombre debe tener máximo 255 caracteres',
            'category_supplier_id.required' => 'La categoría es requerida',
            'category_supplier_id.exists' => 'La categoría no existe',
        ];
    }
}
