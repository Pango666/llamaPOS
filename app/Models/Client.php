<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'documento',
        'email',
        'phone',
        'address',
    ];

    // Si necesitas relaciones, agrégalas aquí
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}