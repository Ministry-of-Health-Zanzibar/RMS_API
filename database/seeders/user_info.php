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
                'address' => 'Kilimani',
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

        // 2. DG user
        $dg = User::firstOrCreate(
            ['email' => 'dg@mohz.go.tz'],
            [
                'first_name' => 'DG',
                'middle_name' => 'STAFF',
                'last_name' => 'OFFICER',
                'address' => 'Kilimani',
                'gender' => 'Male',
                'phone_no' => '0777783400',
                'date_of_birth' => '1980-10-30',
                'password' => bcrypt('dg@123'),
                'created_by' => $admin->id,
            ]
        );

        $dgRole = Role::firstOrCreate(
            ['name' => 'ROLE DG', 'guard_name' => 'web'],
            ['created_by' => $admin->id]
        );

        $dgPermissionNames = [
            'Create ReferralLetter',
            'Update ReferralLetter',
            'Delete ReferralLetter',
            'View ReferralLetter',
            
            'Referral Module',
            'Create Referral',
            'Update Referral',
            'Delete Referral',
            'View Referral',

            'Bill Module',
            'Create Bill',
            'Update Bill',
            'Delete Bill',
            'View Bill',

            // 'Payment Module',
            // 'Create Payment',
            // 'Update Payment',
            // 'Delete Payment',
            // 'View Payment',
        ];

        $dgPermissions = collect();
        foreach ($dgPermissionNames as $permName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
            $dgPermissions->push($permission->name);
        }

        $dgRole->syncPermissions($dgPermissions);
        $dg->syncPermissions($dgPermissions);
        $dg->syncRoles([$dgRole]);
    }
}

