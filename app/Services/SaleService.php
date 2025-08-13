<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function all(array $filters, int $perPage = 15)
    {
        $q = Sale::query()->with(['items.productVariant.product', 'user', 'branch']);

        if (!empty($filters['date'])) {
            $q->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['branch_id'])) {
            $q->where('branch_id', $filters['branch_id']);
        }
        if (!empty($filters['client_id'])) {
            $q->where('client_id', $filters['client_id']);
        }

        return $q->orderByDesc('created_at')->paginate($perPage);
    }

    public function find(int $id)
    {
        return Sale::with(['items.productVariant.product', 'user', 'branch'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $sale = new Sale();
            $sale->branch_id = $data['branch_id'];
            $sale->user_id   = auth()->id();
            $sale->client_id = $data['client_id'] ?? null;
            $sale->status    = 'completed';
            $sale->total     = 0;
            $sale->save();

            $total = 0;

            foreach ($data['items'] as $line) {
                $product  = Product::where('is_active', true)->findOrFail($line['product_id']);
                $quantity = (int) $line['quantity'];

                // Tomamos precio del producto (la demo no usa variantes)
                $price = $product->price;

                if ($price === null) {
                    // Si no tiene precio, detén el registro
                    throw ValidationException::withMessages([
                        'items' => ["El producto {$product->name} no tiene precio configurado."],
                    ]);
                }

                // Para satisfacer la FK a product_variants, usa/crea una "variante única"
                $variant = ProductVariant::where('product_id', $product->id)
                    ->where('name', 'Única')
                    ->first();

                if (!$variant) {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'name'       => 'Única',
                        'price'      => $price,
                        'is_active'  => true,
                    ]);
                }

                $lineTotal = (float) $price * $quantity;

                SaleItem::create([
                    'sale_id'            => $sale->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $quantity,
                    'price'              => $price,
                    'total'              => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            $sale->total = $total;
            $sale->save();

            return $this->find($sale->id);
        });
    }
}
