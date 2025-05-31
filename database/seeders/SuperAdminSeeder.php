<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Buat role jika belum ada
        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin']);

        // Buat user SuperAdmin
        $user = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Ade Putra Panjaitan',
                'password' => Hash::make('Ade@150600'),
                'position' => 'System Administrator',
                'is_active' => true,
                'nik' =>  '12345678',
            ]
        );

        // Assign role
        if (!$user->hasRole('SuperAdmin')) {
            $user->assignRole($superAdminRole);
        }
    }
}
