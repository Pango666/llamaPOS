<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Services\VariantService;
use App\Http\Requests\StoreVariantRequest;
use App\Http\Requests\UpdateVariantRequest;
use Illuminate\Support\Facades\Log;

class ProductVariantController extends BaseApiController
{
    public function __construct(private VariantService $service)
    {
        $this->middleware(['auth:api','role:owner']);
    }

    public function index()
    {
        try {
            $variants = $this->service->all();
            return $this->success($variants);
        } catch (\Exception $e) {
            Log::error('ProductVariantController@index error', ['msg'=>$e->getMessage()]);
            return $this->error('No se pudieron obtener variantes', 500);
        }
    }

    public function store(StoreVariantRequest $request)
    {
        try {
            $variant = $this->service->create($request->validated());
            return $this->success($variant, 'Variante creada', 201);
        } catch (\Exception $e) {
            Log::error('ProductVariantController@store error', ['msg'=>$e->getMessage()]);
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
            Log::error('ProductVariantController@update error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al actualizar variante', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->success(null, 'Variante eliminada', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Variante no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('ProductVariantController@destroy error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al eliminar variante', 500);
        }
    }
    
}