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
        try {
            return Storage::disk('s3')->url($this->image_path);
        } catch (\Throwable $e) {
            return Storage::disk('public')->url($this->image_path);
        }
    }
}
