<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Branch;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'admin@ocheto.com'],
            [
                'name' => 'Administrador Ocheto',
                'password' => bcrypt('password'),
                'branch_id' => null,
            ]
        );
        $owner->syncRoles('owner');

        $branch1 = Branch::where('name', 'Ocheto Centro')->first();
        $branch2 = Branch::where('name', 'Ocheto Sur')->first();

        $seller1 = User::firstOrCreate(
            ['email' => 'vendedor.centro@ocheto.com'],
            [
                'name' => 'Vendedor Centro',
                'password' => bcrypt('password'),
                'branch_id' => $branch1->id,
            ]
        );
        $seller1->syncRoles('seller');

        $seller2 = User::firstOrCreate(
            ['email' => 'vendedor.sur@ocheto.com'],
            [
                'name' => 'Vendedor Sur',
                'password' => bcrypt('password'),
                'branch_id' => $branch2->id,
            ]
        );
        $seller2->syncRoles('seller');
    }
}
