<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Controllers\API\Traits\RoleCheck;
use App\Services\CategoryService;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends BaseApiController
{
    use RoleCheck;

    public function __construct(private CategoryService $service) {}

    public function index()
    {
        try {
            $this->authorizeRole('owner');
            $cats = $this->service->all();
            return $this->success($cats);
        } catch (\Exception $e) {
            return $this->error('No se pudieron obtener categorías', 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            // StoreCategoryRequest::authorize valida role=owner
            $cat = $this->service->create($request->validated());
            return $this->success($cat, 'Categoría creada', 201);
        } catch (\Exception $e) {
            return $this->error('Error al crear categoría', 500);
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $cat = $this->service->update($id, $request->validated());
            return $this->success($cat, 'Categoría actualizada');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        } catch (\Exception $e) {
            return $this->error('Error al actualizar categoría', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorizeRole('owner');
            $this->service->delete($id);
            return $this->success(null, 'Categoría eliminada', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        }
    }
}
