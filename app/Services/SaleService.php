<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * @param array{branch_id:int, items:array<array{product_id:int,quantity:int}>, client_id?:int} $data
     */
    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'branch_id' => $data['branch_id'],
                'user_id'   => auth()->id(),
                'total'     => 0,
            ]);

            $total = 0.0;

            foreach ($data['items'] as $line) {
                $product = Product::findOrFail($line['product_id']);
                $qty     = (int) ($line['quantity'] ?? 1);

                $price     = (float) ($product->price ?? 0); // puede venir como string en DB
                $lineTotal = $price * $qty;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $price,
                    'total'      => $lineTotal, // si prefieres "subtotal", cambia aquí y en DB
                ]);

                $total += $lineTotal;
            }

            $sale->update(['total' => $total]);

            return $sale->load(['items.product']);
        });
    }

    public function all(array $filters, int $perPage = 15)
    {
        $q = Sale::query()->with(['items.product'])->orderByDesc('id');

        if (!empty($filters['branch_id'])) $q->where('branch_id', $filters['branch_id']);
        if (!empty($filters['date']))      $q->whereDate('created_at', $filters['date']);
        if (!empty($filters['client_id'])) $q->where('client_id', $filters['client_id']);

        return $q->paginate($perPage);
    }

    public function find(int $id): Sale
    {
        return Sale::with(['items.product'])->findOrFail($id);
    }
}
