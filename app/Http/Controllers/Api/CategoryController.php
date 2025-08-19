<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\CategoryService;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Intervention Image v3
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;

class CategoryController extends BaseApiController
{
    public function __construct(private CategoryService $service)
    {
        $this->middleware(['auth:api', 'role:owner']);
    }

    public function index()
    {
        try {
            $categories = collect($this->service->all())->map(function ($c) {
                if (!empty($c['image_path'])) {
                    $c['image_url'] = $this->publicUrl($c['image_path']);
                }
                return $c;
            });

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
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $data['image_path'] = $this->storeAsWebpToR2($request->file('image'), 'categories');
            }

            $category = $this->service->create($data);

            if (!empty($category['image_path'])) {
                $category['image_url'] = $this->publicUrl($category['image_path']);
            }

            return $this->success($category, 'Categoría creada', 201);
        } catch (\Exception $e) {
            Log::error('CategoryController@store error', ['msg' => $e->getMessage()]);
            return $this->error('Error al crear categoría', 500);
        }
    }

    public function show($id)
    {
        try {
            $category = $this->service->find($id);
            if (!empty($category['image_path'])) {
                $category['image_url'] = $this->publicUrl($category['image_path']);
            }
            return $this->success($category);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('CategoryController@show error', ['msg' => $e->getMessage()]);
            return $this->error('Error al obtener categoría', 500);
        }
    }

    public function update(UpdateCategoryRequest $request, $id)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $old = $this->service->find($id)['image_path'] ?? null;

                if ($old) {
                    if (Storage::disk('s3')->exists($old)) {
                        Storage::disk('s3')->delete($old);
                    } elseif (Storage::disk('public')->exists($old)) {
                        Storage::disk('public')->delete($old);
                    }
                }

                $data['image_path'] = $this->storeAsWebpToR2($request->file('image'), 'categories');
            }

            $category = $this->service->update($id, $data);

            if (!empty($category['image_path'])) {
                $category['image_url'] = $this->publicUrl($category['image_path']);
            }

            return $this->success($category, 'Categoría actualizada');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('CategoryController@update error', ['msg' => $e->getMessage()]);
            return $this->error('Error al actualizar categoría', 500);
        }
    }

    public function destroy($id)
    {
        try {
            $category = $this->service->find($id);

            if (!empty($category['image_path'])) {
                $old = $category['image_path'];
                if (Storage::disk('s3')->exists($old)) {
                    Storage::disk('s3')->delete($old);
                } elseif (Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
            }

            $this->service->delete($id);
            return $this->success(null, 'Categoría eliminada', 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error('Categoría no encontrada', 404);
        } catch (\Exception $e) {
            Log::error('CategoryController@destroy error', ['msg' => $e->getMessage()]);
            return $this->error('Error al eliminar categoría', 500);
        }
    }

    public function catalog(Request $request)
    {
        $q          = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category_id');
        $withEmpty  = (bool) $request->boolean('with_empty', false);

        $categories = Category::query()
            ->when($categoryId, fn($qq) => $qq->where('id', $categoryId))
            ->with(['products' => function ($qp) use ($q) {
                $qp->where('is_active', true)
                    ->when($q, function ($qpp) use ($q) {
                        $qpp->where('name', 'like', "%{$q}%")
                            ->orWhereHas('variants', fn($v) => $v->where('name', 'like', "%{$q}%"));
                    })
                    ->with(['variants' => fn($v) => $v->where('is_active', true)])
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        if (!$withEmpty) {
            $categories = $categories->filter(fn($c) => $c->products->count() > 0)->values();
        }

        // Forzar URL pública en categoría y en productos anidados
        $categories->each(function ($c) {
            if (!empty($c->image_path)) {
                $c->image_url = Storage::disk('s3')->url($c->image_path);
            }
            $c->products->transform(function ($p) {
                if (!empty($p->image_path)) {
                    // si el accessor ya está bien, esto no es estrictamente necesario,
                    // pero lo forzamos para evitar serializadores que ignoran appends.
                    $p->image_url = Storage::disk('s3')->url($p->image_path);
                }
                return $p;
            });
        });

        return $this->success($categories);
    }

    /* ========== Helpers ========== */

    private function storeAsWebpToR2(UploadedFile $file, string $dir): string
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($file->getPathname())
            ->encode(new WebpEncoder(quality: 82));

        $filename = Str::uuid()->toString() . '.webp';
        $key = trim($dir, '/') . '/' . $filename;

        Storage::disk('s3')->put($key, (string) $image, [
            'visibility'   => 'public',
            'ContentType'  => 'image/webp',
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);

        return $key;
    }

    private function publicUrl(string $path): string
    {
        try {
            return Storage::disk('s3')->url($path);
        } catch (\Throwable $e) {
            try {
                return Storage::disk('public')->url($path);
            } catch (\Throwable $e2) {
                $base = config('filesystems.disks.s3.url') ?: env('AWS_URL');
                return $base ? rtrim($base, '/') . '/' . ltrim($path, '/') : $path;
            }
        }
    }
}
