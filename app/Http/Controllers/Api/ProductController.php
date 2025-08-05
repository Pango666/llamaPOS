<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseApiController
{
    public function __construct(private ProductService $service)
    {
        $this->middleware(['auth:api','role:owner']);
    }

    public function index()
    {
        try {
            $products = $this->service->all();
            return $this->success($products);
        } catch (\Exception $e) {
            Log::error('ProductController@index error', ['msg'=>$e->getMessage()]);
            return $this->error('No se pudieron obtener productos', 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }

            $product = $this->service->create($data);
            return $this->success($product, 'Producto creado', 201);
        } catch (\Exception $e) {
            Log::error('ProductController@store error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al crear producto', 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->service->find($id);
            return $this->success($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ProductController@show error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al obtener el producto', 500);
        }
    }

    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $old = $this->service->find($id)['image_path'] ?? null;
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data['image_path'] = $request->file('image')->store('products', 'public');
            }

            $product = $this->service->update($id, $data);
            return $this->success($product, 'Producto actualizado');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ProductController@update error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al actualizar producto', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = $this->service->find($id);
            if (!empty($product['image_path'])) {
                Storage::disk('public')->delete($product['image_path']);
            }
            $this->service->delete($id);
            return $this->success(null, 'Producto eliminado', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ProductController@destroy error', ['msg'=>$e->getMessage()]);
            return $this->error('Error al eliminar producto', 500);
        }
    }
}