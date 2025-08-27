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
        try {
            return Storage::disk('s3')->url($this->image_path);
        } catch (\Throwable $e) {
            // fallback por si tienes imágenes antiguas en local
            return Storage::disk('public')->url($this->image_path);
        }
    }
}
