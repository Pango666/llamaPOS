<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo owner puede actualizar categorías
        return auth('api')->user()?->role === 'owner';
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio si se proporciona.',
            'name.string'   => 'El nombre debe ser texto.',
            'name.max'      => 'El nombre no puede exceder 255 caracteres.',
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
