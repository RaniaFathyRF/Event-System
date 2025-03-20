<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run this seeder first
        $this->call(RolePermissionSeeder::class);

        // Then run this seeder
        $this->call(UserSeeder::class);
    }
}
