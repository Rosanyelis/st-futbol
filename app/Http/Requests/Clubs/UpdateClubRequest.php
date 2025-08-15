<?php

namespace App\Http\Requests\Clubs;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'country_id' => 'nullable|exists:countries,id', 
            'province_id' => 'nullable|exists:provinces,id',
            'city_id' => 'nullable|exists:cities,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del club es requerido',
            'name.string' => 'El nombre del club debe ser una cadena de texto',
            'name.max' => 'El nombre del club debe tener menos de 255 caracteres',
            'logo.image' => 'El logo del club debe ser una imagen',
            'logo.mimes' => 'El logo del club debe ser una imagen de tipo: JPG, JPEG, PNG',
            'logo.max' => 'El logo del club debe ser menor a 2MB',
            'country_id.exists' => 'El país no existe',
            'province_id.exists' => 'La provincia no existe',
            'city_id.exists' => 'La ciudad no existe',
        ];
    }
}
