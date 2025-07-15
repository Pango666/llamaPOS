<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function all(array $filters = [], int $perPage = 15): array
    {
        $paginator = Sale::with('items.variant.product')
            ->when(!empty($filters['branch_id']), fn($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(!empty($filters['date']), fn($q) => $q->whereDate('created_at', $filters['date']))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $sales = $paginator->getCollection()->map(fn($sale) => [
            'id'         => $sale->id,
            'branch_id'  => $sale->branch_id,
            'user_id'    => $sale->user_id,
            'total'      => $sale->total,
            'created_at' => $sale->created_at->toDateTimeString(),
            'items'      => $sale->items->map(fn($item) => [
                'product_id'   => $item->variant->product->id,
                'product_name' => $item->variant->product->name,
                'variant_id'   => $item->variant->id,
                'variant_name' => $item->variant->name,
                'price'        => $item->price,
                'quantity'     => $item->quantity,
                'subtotal'     => $item->total,
            ])->toArray(),
        ])->toArray();

        $meta = [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
        ];

        return ['sales' => $sales, 'meta' => $meta];
    }

    public function find(int $id): array
    {
        $sale = Sale::with('items.variant.product')->findOrFail($id);

        return [
            'id'         => $sale->id,
            'branch_id'  => $sale->branch_id,
            'user_id'    => $sale->user_id,
            'total'      => $sale->total,
            'created_at' => $sale->created_at->toDateTimeString(),
            'items'      => $sale->items->map(fn($item) => [
                'product_id'   => $item->variant->product->id,
                'product_name' => $item->variant->product->name,
                'variant_id'   => $item->variant->id,
                'variant_name' => $item->variant->name,
                'price'        => $item->price,
                'quantity'     => $item->quantity,
                'subtotal'     => $item->total,
            ])->toArray(),
        ];
    }

    public function create(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $userId = auth('api')->id();

            $sale = Sale::create([
                'branch_id' => $data['branch_id'],
                'user_id'   => Auth::user()->id,
                'total'     => 0,
                'status'    => $data['status'] ?? 'completed',
                'notes'     => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $i) {
                $variant = ProductVariant::findOrFail($i['variant_id']);
                $line = SaleItem::create([
                    'sale_id'            => $sale->id,
                    'product_variant_id' => $variant->id,
                    'quantity'           => $i['quantity'],
                    'price'              => $variant->price,
                    'total'              => $variant->price * $i['quantity'],
                ]);
                $total += $line->total;
            }

            $sale->update(['total' => $total]);

            return $this->find($sale->id);
        });
    }
}
