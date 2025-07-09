<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Controllers\API\Traits\RoleCheck;
use App\Services\VariantService;
use App\Http\Requests\StoreVariantRequest;
use App\Http\Requests\UpdateVariantRequest;

class ProductVariantController extends BaseApiController
{
    use RoleCheck;

    public function __construct(private VariantService $service) {}

    public function index()
    {
        try {
            $this->authorizeRole('owner');
            $variants = $this->service->all();
            return $this->success($variants);
        } catch (\Exception $e) {
            return $this->error('No se pudieron obtener variantes', 500);
        }
    }

    public function store(StoreVariantRequest $request)
    {
        try {
            $variant = $this->service->create($request->validated());
            return $this->success($variant, 'Variante creada', 201);
        } catch (\Exception $e) {
            return $this->error('Error al crear variante', 500);
        }
    }

    public function update(UpdateVariantRequest $request, $id)
    {
        try {
            $variant = $this->service->update($id, $request->validated());
            return $this->success($variant, 'Variante actualizada');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Variante no encontrada', 404);
        } catch (\Exception $e) {
            return $this->error('Error al actualizar variante', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorizeRole('owner');
            $this->service->delete($id);
            return $this->success(null, 'Variante eliminada', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Variante no encontrada', 404);
        }
    }
}
