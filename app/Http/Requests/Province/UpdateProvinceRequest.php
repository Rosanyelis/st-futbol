<?php

namespace App\Http\Requests\Province;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProvinceRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:provinces,name,' . $this->route('province'),
            'country_id' => 'required|exists:countries,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la provincia es requerido',
            'name.unique' => 'La provincia ya existe',
            'country_id.required' => 'El país es requerido',
            'country_id.exists' => 'El país no existe',
        ];
    }
}
