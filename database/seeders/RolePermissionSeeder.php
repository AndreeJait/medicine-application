<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'medicine.view',
            'medicine.create',
            'medicine.update',
            'medicine.delete',
            'medicine.export',
            'stock.in',
            'stock.out',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $admin = Role::firstOrCreate(['name' => 'Admin']);

        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'medicine.view',
            'medicine.create',
            'medicine.update',
            'medicine.delete',
            'medicine.export',
            'stock.in',
            'stock.out',
        ]);
    }
}
