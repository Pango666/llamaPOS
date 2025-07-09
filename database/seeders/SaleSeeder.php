<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\User;
use App\Models\ProductVariant;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $branch = Branch::where('name', 'Ocheto Centro')->first();
        $seller = User::role('seller')->where('branch_id', $branch->id)->first();
        $variant = ProductVariant::first();

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'user_id'   => $seller->id,
            'total'     => $variant->price * 2,
            'status'    => 'completed',
            'notes'     => 'Venta de prueba'
        ]);

        SaleItem::create([
            'sale_id'             => $sale->id,
            'product_variant_id'  => $variant->id,
            'quantity'            => 2,
            'price'               => $variant->price,
            'total'               => $variant->price * 2,
        ]);
    }
}
