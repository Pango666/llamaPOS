<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Services\CategoryService;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Log;

class CategoryController extends BaseApiController
{
    public function __construct(private CategoryService $service)
    {
        $this->middleware(['auth:api','role:owner']);
    }

    public function index()
    {
        try {
            $categories = $this->service->all();
            return $this->success($categories);
        } catch (\Exception $e) {
            Log::error('CategoryController@index error', [
                'msg'   => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->error('No se pudieron obtener categorías', 500);
        }
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            $category = $this->service->create($request->validated());
            return $this->success($category, 'Categoría creada', 201);
        } catch (\Exception $e) {
            Log::error('CategoryController@store error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al crear categoría', 500);
        }
    }

    public function show($id)
    {
        try {
            $category = $this->service->find($id);
            return $this->success($category);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoria no encontrada', 404);
        } catch (\Exception $e) {
            \Log::error('CategoryController@show error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al obtener categoria', 500);
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $category = $this->service->update($id, $request->validated());
            return $this->success($category, 'Categoría actualizada');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('CategoryController@update error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al actualizar categoría', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->success(null, 'Categoría eliminada', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('CategoryController@destroy error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al eliminar categoría', 500);
        }
    }
}