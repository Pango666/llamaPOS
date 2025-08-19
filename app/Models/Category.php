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
        $path = $this->image_path ?: null;
        if (!$path) return null;

        try {
            return Storage::disk('s3')->url($path);
        } catch (\Throwable $e) {
            try {
                return Storage::disk('public')->url($path);
            } catch (\Throwable $e2) {
                $base = config('filesystems.disks.s3.url') ?: env('AWS_URL');
                return $base ? rtrim($base, '/') . '/' . $path : null;
            }
        }
    }
}
