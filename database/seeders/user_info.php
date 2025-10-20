<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class user_info extends Seeder
{
    public function run()
    {
        // 1. Create or get Admin user
        $admin = User::firstOrCreate(
            ['email' => 'info@mohz.go.tz'],
            [
                'first_name' => 'System',
                'middle_name' => 'Supper',
                'last_name' => 'Admin',
                'address' => 'Vuga',
                'gender' => 'Male',
                'phone_no' => '0777000001',
                'date_of_birth' => '1990-10-30',
                'password' => bcrypt('admin@123'),
                'created_by' => 1,
            ]
        );

        // Create admin role if not exists
        $adminRole = Role::firstOrCreate(
            ['name' => 'ROLE ADMIN', 'guard_name' => 'web'],
            ['created_by' => 1]
        );

        // Use permission names instead of IDs
        $allPermissions = Permission::pluck('name')->all();

        $adminRole->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);
        $admin->syncRoles([$adminRole]);

    }
}

