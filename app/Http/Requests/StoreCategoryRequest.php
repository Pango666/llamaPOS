<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && method_exists($this->user(), 'hasRole')
            && $this->user()->hasRole('owner');
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la categoría es obligatorio.',
            'name.string'   => 'El nombre debe ser texto.',
            'name.max'      => 'El nombre no puede exceder 255 caracteres.',
            'image.image'   => 'El archivo debe ser una imagen.',
            'image.mimes'   => 'La imagen debe ser jpg, jpeg, png o gif.',
            'image.max'     => 'La imagen no puede exceder los 2 MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Datos inválidos al crear categoría',
            'errors'  => $validator->errors(),
        ], 422));
    }
}