<?php

namespace App\Http\Requests\City;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:cities,name,' . $this->route('city'),
            'province_id' => 'required|exists:provinces,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la ciudad es requerido',
            'name.unique' => 'La ciudad ya existe',
            'province_id.required' => 'La provincia es requerida',
            'province_id.exists' => 'La provincia no existe',
        ];
    }
}

