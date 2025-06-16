<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
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
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'year' => 'required|integer',
            'url_images' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del evento es requerido',
            'start_date.required' => 'La fecha de inicio del evento es requerida',
            'end_date.required' => 'La fecha de fin del evento es requerida',
            'start_date.date' => 'La fecha de inicio del evento debe ser una fecha válida',
            'end_date.date' => 'La fecha de fin del evento debe ser una fecha válida',
            'year.required' => 'El año del evento es requerido',
            'url_images.image' => 'El archivo debe ser una imagen',
            'url_images.mimes' => 'El archivo debe ser una imagen de tipo: JPG, JPEG, PNG',
            'url_images.max' => 'El archivo debe ser menor a 2MB',
        ];
    }
}
