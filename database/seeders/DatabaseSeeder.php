<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'shad@example.com',
        ], [
            'name' => 'Shad',
            'username' => 'shad',
            'role' => 'user',
            'password' => bcrypt('password123'),
        ]);

        User::query()->updateOrCreate([
            'email' => 'manager@example.com',
        ], [
            'name' => 'Manager',
            'username' => 'manager',
            'role' => 'manager',
            'password' => bcrypt('password123'),
        ]);
    }
}
