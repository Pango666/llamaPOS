<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::firstOrCreate(['name' => 'Ocheto Centro'], [
            'address' => 'Av. Principal 123'
        ]);

        Branch::firstOrCreate(['name' => 'Ocheto Sur'], [
            'address' => 'Av. Flores 456'
        ]);
    }
}
