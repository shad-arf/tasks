<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultBusiness = Business::query()->firstOrCreate([
            'name' => 'Default Business',
        ]);

        User::query()->updateOrCreate([
            'email' => 'shad@example.com',
        ], [
            'name' => 'Shad',
            'username' => 'shad',
            'business_id' => $defaultBusiness->id,
            'role' => 'user',
            'password' => bcrypt('password123'),
        ]);

        User::query()->updateOrCreate([
            'email' => 'manager@example.com',
        ], [
            'name' => 'Manager',
            'username' => 'manager',
            'business_id' => $defaultBusiness->id,
            'role' => 'manager',
            'password' => bcrypt('password123'),
        ]);
    }
}
