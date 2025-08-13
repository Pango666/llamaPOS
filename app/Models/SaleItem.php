<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',     // ← sin variantes
        'quantity',
        'price',          // precio unitario
        'total',          // total de la línea (price * quantity)
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // ← relación correcta
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
