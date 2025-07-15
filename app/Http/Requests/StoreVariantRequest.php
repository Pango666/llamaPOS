<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados con rol 'owner'
        return $this->user()
            && method_exists($this->user(), 'hasRole')
            && $this->user()->hasRole('owner');
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric|min:0',
            'image_path' => 'sometimes|nullable|string|max:255',
            'is_active'  => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'El producto es obligatorio.',
            'product_id.exists'   => 'El producto no existe.',
            'name.required'       => 'El nombre es obligatorio.',
            'price.required'      => 'El precio es obligatorio.',
            'price.numeric'       => 'El precio debe ser numérico.',
            'price.min'           => 'El precio debe ser al menos 0.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Datos inválidos al crear variante',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
