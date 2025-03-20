<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // add default super admin user
           User::firstOrCreate(
                ['email' => 'super_admin@example.com'], // Search criteria
                ['name' => 'super admin', 'password' => bcrypt('password')] // Create if not found
            )->assignRole('super_admin');

            // add default admin user
            User::firstOrCreate(
                ['email' => 'admin@example.com'], // Search criteria
                ['name' => 'admin', 'password' => bcrypt('password')] // Create if not found
            )->assignRole('admin');

            // add default user
            User::firstOrCreate(
                ['email' => 'user@example.com'], // Search criteria
                ['name' => 'user', 'password' => bcrypt('password')] // Create if not found
            )->assignRole('user');

        }catch (\Exception $exception){
           throw $exception;
        }

    }
}
