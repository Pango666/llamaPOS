<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && method_exists($this->user(), 'hasRole')
            && $this->user()->hasRole('owner');
    }

    public function rules(): array
    {
        $categoryId = $this->input('category_id');
        return [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255|unique:products,name,NULL,id,category_id,' . $categoryId,
            'price'       => 'nullable|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'          => 'Ya existe un producto con este nombre en la categoría.',
            'category_id.required' => 'La categoría es obligatoria.',
            'category_id.exists'   => 'La categoría no existe.',
            'name.required'        => 'El nombre es obligatorio.',
            'name.string'          => 'El nombre debe ser texto.',
            'name.max'             => 'El nombre no puede exceder 255 caracteres.',
            'price.numeric'        => 'El precio debe ser un número.',
            'price.min'            => 'El precio no puede ser negativo.',
            'image.image'          => 'El archivo debe ser una imagen.',
            'image.mimes'          => 'La imagen debe ser jpg, jpeg, png o gif.',
            'image.max'            => 'La imagen no puede exceder los 2 MB.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'Datos inválidos al crear producto',
            'errors'  => $validator->errors(),
        ], 422));
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'status'  => 'error',
            'message' => 'No autorizado para crear productos',
        ], 403));
    }
}