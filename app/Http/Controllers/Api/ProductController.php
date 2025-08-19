<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Intervention Image v3
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class ProductController extends BaseApiController
{
    public function __construct(private ProductService $service)
    {
        $this->middleware(['auth:api', 'role:owner']);
    }

    public function index()
    {
        try {
            $products = collect($this->service->all())->map(function ($p) {
                if (!empty($p['image_path'])) {
                    $p['image_url'] = $this->publicUrl($p['image_path']);
                }
                return $p;
            });

            return $this->success($products);
        } catch (\Exception $e) {
            Log::error('ProductController@index error', ['msg' => $e->getMessage()]);
            return $this->error('No se pudieron obtener productos', 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image_path'] = $this->storeAsWebpToR2($request->file('image'), 'products');
            }

            $product = $this->service->create($data);

            if (!empty($product['image_path'])) {
                $product['image_url'] = $this->publicUrl($product['image_path']);
            }

            return $this->success($product, 'Producto creado', 201);
        } catch (\Exception $e) {
            Log::error('ProductController@store error', ['msg' => $e->getMessage()]);
            return $this->error('Error al crear producto', 500);
        }
    }

    public function show($id)
    {
        try {
            $product = $this->service->find($id);
            if (!empty($product['image_path'])) {
                $product['image_url'] = $this->publicUrl($product['image_path']);
            }
            return $this->success($product);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ProductController@show error', ['msg' => $e->getMessage()]);
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
                    if (Storage::disk('s3')->exists($old)) {
                        Storage::disk('s3')->delete($old);
                    } elseif (Storage::disk('public')->exists($old)) {
                        Storage::disk('public')->delete($old); // por si hay legacy en local
                    }
                }

                $data['image_path'] = $this->storeAsWebpToR2($request->file('image'), 'products');
            }

            $product = $this->service->update($id, $data);

            if (!empty($product['image_path'])) {
                $product['image_url'] = $this->publicUrl($product['image_path']);
            }

            return $this->success($product, 'Producto actualizado');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ProductController@update error', ['msg' => $e->getMessage()]);
            return $this->error('Error al actualizar producto', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = $this->service->find($id);

            if (!empty($product['image_path'])) {
                $old = $product['image_path'];
                if (Storage::disk('s3')->exists($old)) {
                    Storage::disk('s3')->delete($old);
                } elseif (Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }

            $this->service->delete($id);
            return $this->success(null, 'Producto eliminado', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Producto no encontrado', 404);
        } catch (\Exception $e) {
            Log::error('ProductController@destroy error', ['msg' => $e->getMessage()]);
            return $this->error('Error al eliminar producto', 500);
        }
    }

    /* ========== Helpers ========== */

    /**
     * Convierte a WebP (calidad 82) y sube a R2 (disk s3).
     * Retorna la key guardada, p.ej. "products/abc.webp".
     */
    private function storeAsWebpToR2(UploadedFile $file, string $dir): string
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($file->getPathname())
                         ->encode(new WebpEncoder(quality: 82));

        $filename = Str::uuid()->toString() . '.webp';
        $key = trim($dir, '/').'/'.$filename;

        Storage::disk('s3')->put($key, (string) $image, [
            'visibility'   => 'public',
            'ContentType'  => 'image/webp',
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return $key;
    }

    /**
     * Construye la URL pública usando AWS_URL (R2 public bucket URL).
     */
    private function publicUrl(string $path): string
    {
        $base = rtrim(config('filesystems.disks.s3.url') ?: env('AWS_URL', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}
