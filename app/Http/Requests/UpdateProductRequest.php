<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo owner puede actualizar productos
        return auth('api')->user()?->role === 'owner';
    }

    public function rules(): array
    {
        return [
            'category_id' => 'sometimes|required|exists:categories,id',
            'name'        => 'sometimes|required|string|max:255',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'La categoría no existe.',
            'name.string'        => 'El nombre debe ser texto.',
            'name.max'           => 'El nombre no puede exceder 255 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Datos inválidos al actualizar producto',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
