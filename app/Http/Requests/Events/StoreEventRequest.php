<?php

namespace App\Http\Requests\Events;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'year' => 'required',
            'url_images' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            'url_images.image' => 'La imagen debe ser una imagen',
            'url_images.mimes' => 'La imagen debe ser una imagen válida',
            'url_images.max' => 'La imagen debe ser una imagen válida',
        ];
    }
}
