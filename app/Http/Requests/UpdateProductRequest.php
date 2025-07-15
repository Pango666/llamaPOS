<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            && method_exists($this->user(), 'hasRole')
            && $this->user()->hasRole('owner');
    }

    public function rules(): array
    {
        $productId  = $this->route('id');
        $categoryId = $this->input('category_id', Product::findOrFail($productId)->category_id);

        return [
            'category_id' => 'sometimes|required|exists:categories,id',
            'name'        => 'sometimes|required|string|max:255|unique:products,name,' . $productId . ',id,category_id,' . $categoryId,
            'price'       => 'sometimes|nullable|numeric|min:0',
            'image'       => 'sometimes|nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'is_active'   => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'          => 'Ya existe un producto con este nombre en la categoría.',
            'category_id.exists'   => 'La categoría no existe.',
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
            'message' => 'Datos inválidos al actualizar producto',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
