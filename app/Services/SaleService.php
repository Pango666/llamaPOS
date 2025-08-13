<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * @param array{branch_id:int, items:array<array{product_id:int,quantity:int}>, client_id?:int} $data
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId   = auth()->id();
            $branchId = (int)$data['branch_id'];

            // ---- Cliente (opcional): crear o reutilizar
            $clientId = $data['client_id'] ?? null;

            if (!$clientId) {
                $hasClientData = !empty($data['customer_name']) || !empty($data['customer_phone']);
                if ($hasClientData) {
                    // Busca por teléfono si viene; si no, crea por nombre
                    $lookup = [];
                    if (!empty($data['customer_phone'])) {
                        $lookup['phone'] = $data['customer_phone'];
                    } else {
                        $lookup['name']  = $data['customer_name'];
                    }

                    $client = Client::firstOrCreate(
                        $lookup,
                        [
                            'name'    => $data['customer_name']   ?? 'Cliente',
                            'phone'   => $data['customer_phone']  ?? null,
                            'address' => $data['billing_address'] ?? null,
                            'email'   => null,
                        ]
                    );
                    $clientId = $client->id;
                }
            }

            // ---- Crear venta base
            $sale = Sale::create([
                'branch_id' => $branchId,
                'user_id'   => $userId,
                'client_id' => $clientId,
                'total'     => 0,
                'status'    => 'completed',
                'notes'     => !empty($data['billing_name'])
                    ? ('Factura: ' . $data['billing_name'])
                    : null,
            ]);

            // ---- Ítems
            $total = 0;
            foreach ($data['items'] as $line) {
                $product  = Product::findOrFail((int)$line['product_id']);
                $qty      = max(1, (int)$line['quantity']);
                $price    = (float)($product->price ?? 0);
                $subtotal = $price * $qty;

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => $price,
                    'total'      => $subtotal,
                ]);

                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $this->find($sale->id);
        });
    }

    public function all(array $filters = [], int $perPage = 15)
    {
        $q = Sale::with(['user', 'branch', 'client', 'items.product'])
            ->latest();

        if (!empty($filters['branch_id'])) {
            $q->where('branch_id', $filters['branch_id']);
        }
        if (!empty($filters['date'])) { // YYYY-mm-dd
            $q->whereDate('created_at', $filters['date']);
        }
        if (!empty($filters['user_id'])) {
            $q->where('user_id', $filters['user_id']);
        }

        return $q->paginate($perPage);
    }

    public function find(int $id)
    {
        return Sale::with(['user', 'branch', 'client', 'items.product'])->findOrFail($id);
    }
}
