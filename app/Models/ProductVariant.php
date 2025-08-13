<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = ['product_id', 'name', 'price', 'image_path', 'is_active'];
    protected $casts    = ['is_active' => 'boolean', 'price' => 'decimal:2'];
    protected $appends  = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }
}
