<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductAndVariantSeeder extends Seeder
{
    public function run(): void
    {
        $coffee   = Category::where('name', 'Cafés')->first();
        $pastries = Category::where('name', 'Masitas')->first();
        $drinks   = Category::where('name', 'Bebidas Frías')->first();

        // Café Americano
        $americano = Product::firstOrCreate(
            ['category_id' => $coffee->id, 'name' => 'Café Americano'],
            ['price' => null]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $americano->id, 'name' => 'Grande'],
            ['price' => 12.00]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $americano->id, 'name' => 'Mediano'],
            ['price' => 10.00]
        );

        // Croissant
        $croissant = Product::firstOrCreate(
            ['category_id' => $pastries->id, 'name' => 'Croissant'],
            ['price' => null]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $croissant->id, 'name' => 'Unidad'],
            ['price' => 5.00]
        );

        // Limonada
        $limonada = Product::firstOrCreate(
            ['category_id' => $drinks->id, 'name' => 'Limonada'],
            ['price' => null]
        );
        ProductVariant::firstOrCreate(
            ['product_id' => $limonada->id, 'name' => '350 ml'],
            ['price' => 7.00]
        );
    }
}
