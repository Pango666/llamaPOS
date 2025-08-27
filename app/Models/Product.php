<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'name', 'price', 'image_path', 'is_active'];
    protected $casts    = ['is_active' => 'boolean', 'price' => 'decimal:2'];
    protected $appends  = ['image_url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }


    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) return null;

        // Base y bucket desde config/.env
        $endpoint = rtrim(config('filesystems.disks.s3.endpoint') ?: env('AWS_ENDPOINT'), '/');
        $bucket   = trim(config('filesystems.disks.s3.bucket')   ?: env('AWS_BUCKET', ''));
        $key      = ltrim($this->image_path, '/'); // e.g. "products/uuid.webp"

        // R2 público correcto: https://<account>.r2.cloudflarestorage.com/<bucket>/<key>
        // (si prefieres dominio pub-*.r2.dev, sustituye $endpoint por env('R2_PUBLIC_BASE'))
        return $bucket !== ''
            ? "{$endpoint}/{$bucket}/{$key}"
            : "{$endpoint}/{$key}";
    }
}
