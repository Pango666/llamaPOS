<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'image_path'];
    protected $appends  = ['image_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;

        $key     = ltrim($this->image_path, '/'); // ej: "categories/uuid.webp" o "products/uuid.webp"
        // Toma bucket y base desde config/.env
        $bucket  = trim(config('filesystems.disks.s3.bucket') ?: env('AWS_BUCKET', ''));
        // Usa el dominio público que prefieras:
        // - R2 público "pub-<account>.r2.dev" si defines R2_PUBLIC_BASE en .env
        // - O el endpoint cloudflarestorage.com del .env
        $base    = rtrim(env('R2_PUBLIC_BASE')
            ?: (config('filesystems.disks.s3.endpoint') ?: env('AWS_ENDPOINT')), '/');

        return $bucket !== '' ? "{$base}/{$bucket}/{$key}" : "{$base}/{$key}";
    }
}
