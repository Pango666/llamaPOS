<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && method_exists($this->user(), 'hasAnyRole')
            && $this->user()->hasAnyRole('seller', 'owner');
    }

    public function rules(): array
    {
        return [
            'branch_id'          => 'required|exists:branches,id',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',

            // NUEVO: datos cliente/facturación (opcionales)
            'billing_name'       => 'nullable|string|max:190',
            'billing_address'    => 'nullable|string|max:255',
            'customer_name'      => 'nullable|string|max:190',
            'customer_phone'     => 'nullable|string|max:40',

            // opcional: si mandas id de cliente directo
            'client_id'          => 'nullable|exists:clients,id',
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required'          => 'La sucursal es obligatoria.',
            'branch_id.exists'            => 'La sucursal no existe.',
            'items.required'              => 'Debe enviar al menos un ítem.',
            'items.array'                 => 'Los ítems deben ser un arreglo.',
            'items.*.product_id.required' => 'Cada ítem necesita un producto.',
            'items.*.product_id.exists'   => 'El producto no existe.',
            'items.*.quantity.required'   => 'La cantidad es obligatoria.',
            'items.*.quantity.integer'    => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min'        => 'La cantidad debe ser al menos 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos al registrar venta',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
