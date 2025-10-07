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
        // ❌ Do NOT delete existing users/roles (causes foreign key violations)
        // ✅ Use firstOrCreate() to avoid duplicates

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

        $adminRole = Role::firstOrCreate(['name' => 'ROLE ADMIN'], ['created_by' => 1]);
        $allPermissions = Permission::pluck('id')->all();

        $adminRole->syncPermissions($allPermissions);
        $admin->syncPermissions($allPermissions);
        $admin->syncRoles([$adminRole]);

        // 2. Accountant user
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@mohz.go.tz'],
            [
                'first_name' => 'Accountant',
                'middle_name' => 'Accountant',
                'last_name' => 'Accountant',
                'address' => 'Kilimani',
                'gender' => 'Male',
                'phone_no' => '0777000003',
                'date_of_birth' => '1990-10-30',
                'password' => bcrypt('accountant@123'),
                'created_by' => $admin->id,
            ]
        );

        $accountantRole = Role::firstOrCreate(['name' => 'ROLE ACCOUNTANT'], ['created_by' => $admin->id]);

        $permissions = [
            'Accountant Module',
            'Create Source',
            'Update Source',
            'Delete Source',
            'View Source',
            'Create Source Type',
            'Update Source Type',
            'Delete Source Type',
            'View Source Type',
            'Create Category',
            'Update Category',
            'Delete Category',
            'View Category',
            'Create Document Type',
            'Update Document Type',
            'Delete Document Type',
            'View Document Type',
            'Create Document Form',
            'Update Document Form',
            'Delete Document Form',
            'View Document Form',
            'View Report',
            'View Dashboard',
            'View Permission',
            'Create Role',
            'Update Role',
            'Delete Role',
            'View Role',
            'Create User',
            'Update User',
            'Delete User',
            'View User',
        ];

        $accountantPermissions = collect();
        foreach ($permissions as $permName) {
            $permission = Permission::firstOrCreate(['name' => $permName]);
            $accountantPermissions->push($permission);
        }

        $accountantRole->syncPermissions($accountantPermissions);
        $accountant->syncPermissions($accountantPermissions);
        $accountant->syncRoles([$accountantRole]);

        // 3. DG user
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

        $dgRole = Role::firstOrCreate(['name' => 'ROLE DG'], ['created_by' => $admin->id]);

        $dgPermissions = Permission::whereIn('name', [
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
            'Payment Module',
            'Create Payment',
            'Update Payment',
            'Delete Payment',
            'View Payment',
        ])->get();

        $dgRole->syncPermissions($dgPermissions);
        $dg->syncPermissions($dgPermissions);
        $dg->syncRoles([$dgRole]);
    }
}
