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

        // 100% explícito: base pública + bucket + key
        $base   = rtrim(env('R2_PUBLIC_BASE'), '/');   // 👈 lo defines en .env
        $bucket = trim(env('AWS_BUCKET', 'komercia')); // "komercia"
        $key    = ltrim($this->image_path, '/');       // "products/xxx.webp"

        return "{$base}/{$bucket}/{$key}";
    }
}
