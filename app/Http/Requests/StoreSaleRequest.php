<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo seller puede registrar ventas
        return auth('api')->user()?->role === 'seller';
    }

    public function rules(): array
    {
        return [
            'branch_id'           => 'required|exists:branches,id',
            'items'               => 'required|array|min:1',
            'items.*.variant_id'  => 'required|exists:product_variants,id',
            'items.*.quantity'    => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required'          => 'La sucursal es obligatoria.',
            'branch_id.exists'            => 'La sucursal no existe.',
            'items.required'              => 'Debe enviar al menos un ítem.',
            'items.array'                 => 'Los ítems deben ser un arreglo.',
            'items.*.variant_id.required' => 'Cada ítem necesita una variante.',
            'items.*.variant_id.exists'   => 'La variante no existe.',
            'items.*.quantity.required'   => 'La cantidad es obligatoria.',
            'items.*.quantity.integer'    => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min'        => 'La cantidad debe ser al menos 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Datos inválidos al registrar venta',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
