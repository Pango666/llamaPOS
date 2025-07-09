<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::firstOrCreate(['name' => 'Cafés']);
        Category::firstOrCreate(['name' => 'Masitas']);
        Category::firstOrCreate(['name' => 'Bebidas Frías']);
    }
}
