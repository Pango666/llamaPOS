<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Controllers\API\Traits\RoleCheck;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends BaseApiController
{
    use RoleCheck;

    public function __construct(private ProductService $service) {}

    public function index(Request $request)
    {
        try {
            $rol = Auth::user()->roles->first();
            if ($rol->name == "owner") {
                $perPage = (int) $request->query('per_page', 15);
                $data = $this->service->all($perPage);
                return $this->success($data);
            }else{
                return $this->error('Acceso Denegado', 422);    
            }
        } catch (\Exception $e) {
            return $this->error('No se pudieron obtener productos', 422);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->service->create($request->validated());
            return $this->success($product, 'Producto creado', 201);
        } catch (\Exception $e) {
            return $this->error('Error al crear producto', 500);
        }
    }

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = $this->service->update($id, $request->validated());
            return $this->success($product, 'Producto actualizado');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            return $this->error('Error al actualizar producto', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->authorizeRole('owner');
            $this->service->delete($id);
            return $this->success(null, 'Producto eliminado', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        }
    }
}
