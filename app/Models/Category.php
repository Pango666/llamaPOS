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
        $base   = rtrim(env('R2_PUBLIC_BASE'), '/');
        $bucket = trim(env('AWS_BUCKET', 'komercia'));
        $key    = ltrim($this->image_path, '/');
        return "{$base}/{$bucket}/{$key}";
    }
}
