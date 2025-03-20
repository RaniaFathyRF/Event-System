<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Create roles
            $superAdminRole = Role::findOrCreate('super_admin');
            $adminRole = Role::findOrCreate('admin');
            $userRole = Role::findOrCreate('user');

            // Create permissions
            $allPermission = Permission::findOrCreate('manage all');
            $viewTicketsPermission = Permission::findOrCreate('view tickets');
            $deleteTicketsPermission = Permission::findOrCreate('delete tickets');

            // Assign permissions to roles
            $superAdminRole->givePermissionTo($allPermission);
            $adminRole->givePermissionTo([$viewTicketsPermission, $deleteTicketsPermission]);
            $userRole->givePermissionTo($viewTicketsPermission);

        }catch (\Exception $exception){
           throw $exception;
        }

    }
}
