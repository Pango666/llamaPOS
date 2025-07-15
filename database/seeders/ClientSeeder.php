<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Branch;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleItem;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Clientes de ejemplo
        Client::firstOrCreate(
            ['documento' => null, 'name' => 'Público General'],
            ['email' => null, 'phone' => null, 'address' => null]
        );

        Client::firstOrCreate(
            ['documento' => '123456789'],
            ['name'    => 'Empresa XYZ',
             'email'   => 'contacto@empresa.xyz',
             'phone'   => '555-1234',
             'address' => 'Av. Siempre Viva 742']
        );

        Client::firstOrCreate(
            ['documento' => '987654321'],
            ['name'    => 'Cliente ABC',
             'email'   => 'ventas@cliente.abc',
             'phone'   => '555-9876',
             'address' => 'Calle Falsa 123']
        );
    }
}