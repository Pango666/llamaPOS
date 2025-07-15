<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreBranchRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches')
                    ->where(fn($query) => $query->where('address', $this->input('address'))),
            ],
            'address' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'name.string'   => 'El nombre debe ser texto.',
            'name.max'      => 'El nombre no puede exceder los 255 caracteres.',
            'name.unique'   => 'Ya existe una sucursal con este nombre y ubicación.',
            'address.string'=> 'La dirección debe ser texto válido.',
            'address.max'   => 'La dirección no puede exceder los 255 caracteres.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status'  => 'error',
                'message' => 'Datos inválidos al crear sucursal',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}

class UpdateBranchRequest extends FormRequest
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
        $branchId = $this->route('id');
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('branches')
                    ->ignore($branchId)
                    ->where(fn($query) => $query->where('address', $this->input('address'))),
            ],
            'address' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'name.string'   => 'El nombre debe ser texto válido.',
            'name.max'      => 'El nombre no puede exceder los 255 caracteres.',
            'name.unique'   => 'Ya existe una sucursal con este nombre y ubicación.',
            'address.string'=> 'La dirección debe ser texto válido.',
            'address.max'   => 'La dirección no puede exceder los 255 caracteres.',
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
