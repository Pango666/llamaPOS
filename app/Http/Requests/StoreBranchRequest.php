<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->user()->role === 'owner';
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'name.string'   => 'El nombre debe ser texto.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
