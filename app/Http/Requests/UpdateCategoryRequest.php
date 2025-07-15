<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCategoryRequest extends FormRequest
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
            'name'  => 'sometimes|required|string|max:255',
            'image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio si se proporciona.',
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
            'message' => 'Datos inválidos al actualizar categoría',
            'errors'  => $validator->errors(),
        ], 422));
    }
}