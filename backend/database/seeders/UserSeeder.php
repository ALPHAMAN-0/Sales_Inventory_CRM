<?php

namespace Database\Seeders;

use App\Domain\Crm\Models\Employee;
use App\Domain\Support\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $mainBranch = Branch::where('code', 'BR-MAIN')->first() ?? $branches->first();

        // Admin (also has an employee profile so they can record sales).
        $admin = User::firstOrCreate(
            ['email' => 'admin@salescrm.test'],
            ['name' => 'Admin User', 'password' => Hash::make('password')],
        );
        $admin->syncRoles(['admin']);
        Employee::firstOrCreate(
            ['user_id' => $admin->id],
            ['name' => 'Admin User', 'email' => $admin->email, 'branch_id' => $mainBranch->id],
        );

        // Regular employees.
        $staff = [
            ['Alice Nguyen', 'alice@salescrm.test'],
            ['Bob Martins', 'bob@salescrm.test'],
            ['Carol Reyes', 'carol@salescrm.test'],
        ];
        foreach ($staff as [$name, $email]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => Hash::make('password')],
            );
            $user->syncRoles(['employee']);
            Employee::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $name, 'email' => $email, 'branch_id' => $branches->random()->id],
            );
        }

        // Machine client for the e-commerce feed: a token with the store:read ability.
        $storeUser = User::firstOrCreate(
            ['email' => 'store@salescrm.test'],
            ['name' => 'E-commerce Integration', 'password' => Hash::make('password')],
        );
        $storeUser->tokens()->delete();
        $token = $storeUser->createToken('ecommerce-feed', ['store:read'])->plainTextToken;

        $this->command?->newLine();
        $this->command?->warn("E-commerce feed token (store:read):");
        $this->command?->line("  {$token}");
        $this->command?->newLine();
    }
}
