<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class dg_role extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create(['name' => 'ROLE DG']);
        $permissionNames = [
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
        ];

        $permissions = Permission::whereIn('name', $permissionNames)->get();

        $role->syncPermissions($permissions);

    }
}
