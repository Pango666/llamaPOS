<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el owner puede actualizar sucursales
        return auth('api')->user()?->role === 'owner';
    }

    public function rules(): array
    {
        return [
            'name'    => 'sometimes|required|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'El nombre de la sucursal es obligatorio cuando se proporciona.',
            'name.string'           => 'El nombre debe ser un texto válido.',
            'name.max'              => 'El nombre no puede exceder los 255 caracteres.',
            'address.string'        => 'La dirección debe ser un texto válido.',
            'address.max'           => 'La dirección no puede exceder los 255 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos al actualizar sucursal',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
