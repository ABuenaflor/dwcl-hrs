<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([OrganizationSeeder::class, RubricSeeder::class]);

        // First HRDO account. Change the password immediately after the first sign-in.
        User::firstOrCreate(['email' => 'hrdo@dwcl.edu.ph'], [
            'first_name' => 'HRDO', 'last_name' => 'Administrator',
            'password' => 'password', 'role' => Role::Admin, 'status' => AccountStatus::Active,
        ]);

        if (! app()->isProduction()) {
            $this->call(DemoSeeder::class);
        }
    }
}
