<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema; // ← AÑADIR
use Illuminate\Support\Carbon;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Client;
use App\Models\User;
use App\Models\Product;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::whereIn('name', ['Ocheto Centro', 'Ocheto Sur'])->get();
        if ($branches->isEmpty()) {
            $branches = Branch::all();
        }
        if ($branches->isEmpty()) {
            $this->command->warn('SaleSeeder: no hay sucursales; me salto la siembra de ventas.');
            return;
        }

        $clients = Client::all();

        // Usa Schema::hasColumn en vez de schema()
        $productsBaseQuery = Product::query();
        if (Schema::hasColumn('products', 'is_active')) {
            $productsBaseQuery->where('is_active', true);
        }
        if (!$productsBaseQuery->exists()) {
            $this->command->warn('SaleSeeder: no hay productos; me salto la siembra de ventas.');
            return;
        }

        foreach ($branches as $branch) {
            $seller = User::role('seller')->where('branch_id', $branch->id)->first()
                   ?? User::role('owner')->first()
                   ?? User::first();

            if (!$seller) {
                $this->command->warn("SaleSeeder: no hay usuarios para la sucursal {$branch->name}.");
                continue;
            }

            for ($i = 1; $i <= 3; $i++) {
                $client = ($i % 2 === 0 && $clients->isNotEmpty()) ? $clients->random() : null;
                $createdAt = Carbon::now()->subDays(rand(0, 3))->subMinutes(rand(0, 1440));

                $sale = Sale::create([
                    'branch_id' => $branch->id,
                    'user_id'   => $seller->id,
                    'client_id' => $client?->id,
                    'total'     => 0,
                    'status'    => 'completed',
                    'notes'     => 'Venta semilla ' . $i . ' en ' . $branch->name,
                    'created_at'=> $createdAt,
                    'updated_at'=> $createdAt,
                ]);

                $products = (clone $productsBaseQuery)->inRandomOrder()->take(rand(1, 3))->get();

                $total = 0.0;
                foreach ($products as $product) {
                    $qty   = rand(1, 3);
                    $price = (float) ($product->price ?? 0);
                    $lineTotal = $price * $qty;

                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $product->id, // ← sin variantes
                        'quantity'   => $qty,
                        'price'      => $price,
                        'total'      => $lineTotal,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $total += $lineTotal;
                }

                $sale->update(['total' => $total, 'updated_at' => $createdAt]);
            }
        }
    }
}
