<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateVariantRequest extends FormRequest
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
            'name'       => 'sometimes|required|string|max:255',
            'price'      => 'sometimes|required|numeric|min:0',
            'image_path' => 'sometimes|nullable|string|max:255',
            'is_active'  => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string'  => 'El nombre debe ser texto.',
            'name.max'     => 'El nombre no puede exceder 255 caracteres.',
            'price.numeric'=> 'El precio debe ser numérico.',
            'price.min'    => 'El precio debe ser al menos 0.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Datos inválidos al actualizar variante',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
