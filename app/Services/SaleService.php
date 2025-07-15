<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function all(array $filters = [], int $perPage = 15): array
    {
        $paginator = Sale::with(['items.variant.product', 'client'])
            ->when(!empty($filters['branch_id']), fn($q) => $q->where('branch_id', $filters['branch_id']))
            ->when(!empty($filters['client_id']), fn($q) => $q->where('client_id', $filters['client_id']))
            ->when(!empty($filters['date']), fn($q) => $q->whereDate('created_at', $filters['date']))
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $sales = $paginator->getCollection()->map(fn($sale) => [
            'id'          => $sale->id,
            'branch_id'   => $sale->branch_id,
            'client_id'   => $sale->client_id,
            'client_name' => $sale->client?->name,
            'user_id'     => $sale->user_id,
            'total'       => $sale->total,
            'status'      => $sale->status,
            'notes'       => $sale->notes,
            'created_at'  => $sale->created_at->toDateTimeString(),
            'items'       => $sale->items->map(fn($item) => [
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
        $sale = Sale::with(['items.variant.product', 'client'])->findOrFail($id);

        return [
            'id'          => $sale->id,
            'branch_id'   => $sale->branch_id,
            'client_id'   => $sale->client_id,
            'client_name' => $sale->client?->name,
            'user_id'     => $sale->user_id,
            'total'       => $sale->total,
            'status'      => $sale->status,
            'notes'       => $sale->notes,
            'created_at'  => $sale->created_at->toDateTimeString(),
            'items'       => $sale->items->map(fn($item) => [
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
            $saleData = [
                'branch_id' => $data['branch_id'],
                'user_id'   => auth('api')->id(),
                'total'     => 0,
                'status'    => $data['status'] ?? 'completed',
                'notes'     => $data['notes'] ?? null,
            ];

            // Asociar cliente si se envió client_id
            if (!empty($data['client_id'])) {
                $saleData['client_id'] = $data['client_id'];
            }

            $sale = Sale::create($saleData);

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
