<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'owner','guard_name'=>'api']);
        Role::firstOrCreate(['name' => 'seller','guard_name'=>'api']);
    }
}
