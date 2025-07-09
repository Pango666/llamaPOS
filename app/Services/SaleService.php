<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function all(array $filters = [], int $perPage = 15)
    {
        $q = Sale::with('items.variant.product')->orderByDesc('created_at');
        if (! empty($filters['branch_id'])) {
            $q->where('branch_id', $filters['branch_id']);
        }
        if (! empty($filters['date'])) {
            $q->whereDate('created_at', $filters['date']);
        }
        return $q->paginate($perPage);
    }

    public function find($id)
    {
        return Sale::with('items.variant.product')->findOrFail($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = auth('api')->id();

            $sale = Sale::create([
                'branch_id' => $data['branch_id'],
                'user_id'   => $userId,
                'total'     => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $i) {
                $variant = DB::table('product_variants')->where('id', $i['variant_id'])->first();
                $line = SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_variant_id' => $i['variant_id'],
                    'quantity' => $i['quantity'],
                    'price' => $variant->price,
                    'total' => $variant->price * $i['quantity'],
                ]);
                $total += $line->total;
            }
            $sale->update(['total' => $total]);
            return $sale->load('items.variant.product');
        });
    }
}
