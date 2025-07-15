<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Client;
use App\Models\User;
use App\Models\ProductVariant;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::whereIn('name', ['Ocheto Centro', 'Ocheto Sur'])->get();
        $clients  = Client::all();

        foreach ($branches as $branch) {
            $seller = User::role('seller')
                          ->where('branch_id', $branch->id)
                          ->first();

            for ($i = 1; $i <= 3; $i++) {
                // Asignar cliente solo si hay clientes registrados
                $client = null;
                if ($i % 2 === 0 && $clients->isNotEmpty()) {
                    $client = $clients->random();
                }

                $sale = Sale::create([
                    'branch_id' => $branch->id,
                    'user_id'   => $seller->id,
                    'client_id' => $client?->id,
                    'total'     => 0,
                    'status'    => 'completed',
                    'notes'     => 'Venta semilla ' . $i . ' en ' . $branch->name,
                ]);

                $variants = ProductVariant::inRandomOrder()
                             ->take(rand(1, 2))
                             ->get();

                $total = 0;
                foreach ($variants as $variant) {
                    $quantity = rand(1, 3);
                    $line = SaleItem::create([
                        'sale_id'            => $sale->id,
                        'product_variant_id' => $variant->id,
                        'quantity'           => $quantity,
                        'price'              => $variant->price,
                        'total'              => $variant->price * $quantity,
                    ]);
                    $total += $line->total;
                }

                $sale->update(['total' => $total]);
            }
        }
    }
}
