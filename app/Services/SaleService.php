<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SaleService
{
    /**
     * @param array{branch_id:int, items:array<array{product_id:int,quantity:int}>, client_id?:int} $data
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            // resolver cliente
            $clientId = $data['client_id'] ?? null;

            if (!$clientId && (
                !empty($data['client_documento']) ||
                !empty($data['client_name'])
            )) {
                $client = Client::query()
                    ->when(
                        !empty($data['client_documento']),
                        fn($q) =>
                        $q->where('documento', $data['client_documento'])
                    )
                    ->first();

                if (!$client) {
                    $client = Client::create([
                        'name'      => $data['client_name']      ?? 'Cliente',
                        'documento' => $data['client_documento'] ?? null,
                        'phone'     => $data['client_phone']     ?? null,
                        'address'   => $data['client_address']   ?? null,
                        'email'     => null,
                    ]);
                } else {
                    // actualizar datos si llegaron
                    $client->fill([
                        'name'    => $data['client_name']   ?? $client->name,
                        'phone'   => $data['client_phone']  ?? $client->phone,
                        'address' => $data['client_address'] ?? $client->address,
                    ])->save();
                }

                $clientId = $client->id;
            }

            $sale = Sale::create([
                'branch_id' => $data['branch_id'],
                'user_id'   => auth()->id(),
                'client_id' => $clientId,
                'total'     => 0,
                'status'    => 'completed',
                'notes'     => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $line) {
                $price = (float) optional(\App\Models\Product::find($line['product_id']))->price ?: 0;
                $qty   = (int) $line['quantity'];

                $item = SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $line['product_id'],
                    'quantity'   => $qty,
                    'price'      => $price,
                    'total'      => $price * $qty,
                ]);

                $total += $item->total;
            }

            $sale->update(['total' => $total]);

            return $this->find($sale->id); // regresa con relaciones
        });
    }

    public function all(array $filters, int $perPage = 15)
    {
        $q = Sale::with([
            'client:id,name,documento,phone,address',
            'user:id,name',
            'branch:id,name',
            'items.product:id,name'
        ])->latest();

        if (!empty($filters['branch_id'])) $q->where('branch_id', $filters['branch_id']);
        if (!empty($filters['date']))      $q->whereDate('created_at', $filters['date']);
        if (!empty($filters['client_id'])) $q->where('client_id', $filters['client_id']);

        return $q->paginate($perPage);
    }

    public function find(int $id)
    {
        $sale = \App\Models\Sale::with([
            'client:id,name,documento,phone,address',
            'user:id,name,email',          // ← email por si quieres mostrarlo
            'branch:id,name',
            'items:id,sale_id,product_id,quantity,price,total,created_at',
            'items.product:id,name,image_path' // ← traemos image_path para armar url
        ])->findOrFail($id);

        // (opcional) Adjuntar url pública desde R2/S3
        foreach ($sale->items as $it) {
            if (!empty($it->product?->image_path)) {
                $it->product->image_url = Storage::disk('s3')->url($it->product->image_path);
            } else {
                $it->product->image_url = null;
            }
        }

        return $sale;
    }
}
