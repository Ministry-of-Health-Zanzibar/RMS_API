<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountantRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'ROLE ACCOUNTANT']);

        $permissionNames = [
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
            'View Report'
        ];
        $permissions = Permission::whereIn('name', $permissionNames)->get();

        $role->syncPermissions($permissions);
    }

}