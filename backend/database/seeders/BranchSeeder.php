<?php

namespace Database\Seeders;

use App\Domain\Support\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Main Store', 'code' => 'BR-MAIN'],
            ['name' => 'North Branch', 'code' => 'BR-NORTH'],
            ['name' => 'Online', 'code' => 'BR-ONLINE'],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(['code' => $branch['code']], ['name' => $branch['name']]);
        }
    }
}
