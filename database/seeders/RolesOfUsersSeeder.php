<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Schema;

class RolesOfUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Detect if 'created_by' column exists in roles table
        $hasCreatedBy = Schema::hasColumn('roles', 'created_by');

        /**
         * =============================
         * 1️ ROLE MEDICAL BOARD MEMBER
         * =============================
         */
        $medicalBoardPermissions = [
            'View Referral Dashboard',

            // Patient Module
            'View Patient',
            'Create Patient',
            'Update Patient',

            // Referral Module
            'View Referral',
            'Create Referral',
            'Update Referral',

            // Diagnosis / History
            'View Diagnoses',
            'Create Diagnoses',
            'Update Diagnoses',
            'Restore Diagnoses',

            'View Patient History',
            'Create Patient History',
            'Update Patient History',

            // Treatment
            'View Treatment',
            'Create Treatment',
            'Update Treatment',

            // Reports
            'View Report',
        ];

        foreach ($medicalBoardPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $medicalBoardRoleData = ['name' => 'ROLE MEDICAL BOARD MEMBER', 'guard_name' => 'web'];
        if ($hasCreatedBy) {
            $medicalBoardRoleData['created_by'] = 1;
        }

        $medicalBoardRole = Role::firstOrCreate(['name' => 'ROLE MEDICAL BOARD MEMBER'], $medicalBoardRoleData);
        $medicalBoardRole->syncPermissions($medicalBoardPermissions);


        /**
         * =============================
         * 2️ ROLE ACCOUNTANT
         * =============================
         */
        $accountantPermissions = [
            'Accountant Module',

            // Bill Management
            'View Bill',
            'Create Bill',
            'Update Bill',
            'Delete Bill',

            'View Bill Item',
            'Create Bill Item',
            'Update Bill Item',
            'Delete Bill Item',

            'View BillFile',
            'Create BillFile',
            'Update BillFile',
            'Delete BillFile',

            // Payment
            'View Payment',
            'Create Payment',
            'Update Payment',
            'Delete Payment',

            // Reports
            'View Report',
        ];

        foreach ($accountantPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $accountantRoleData = ['name' => 'ROLE ACCOUNTANT', 'guard_name' => 'web'];
        if ($hasCreatedBy) {
            $accountantRoleData['created_by'] = 1;
        }

        $accountantRole = Role::firstOrCreate(['name' => 'ROLE ACCOUNTANT'], $accountantRoleData);
        $accountantRole->syncPermissions($accountantPermissions);


        /**
         * =============================
         * 3️ ROLE DIRECTOR GENERAL (DG)
         * =============================
         */
        $dgPermissions = [
            'View Referral Dashboard',

            // System management
            'System Audit',
            'Report Management',

            // User and Role Management
            'User Management',
            'Create User',
            'Update User',
            'Delete User',
            'View User',

            'Create Role',
            'Update Role',
            'Delete Role',
            'View Role',

            // Hospitals and Referrals
            'View Hospital',
            'Create Hospital',
            'Update Hospital',
            'Delete Hospital',

            'View Referral',
            'Update Referral',

            // Bills and Reports
            'View Bill',
            'View Report',
        ];

        foreach ($dgPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $dgRoleData = ['name' => 'ROLE DIRECTOR GENERAL', 'guard_name' => 'web'];
        if ($hasCreatedBy) {
            $dgRoleData['created_by'] = 1;
        }

        $dgRole = Role::firstOrCreate(['name' => 'ROLE DIRECTOR GENERAL'], $dgRoleData);
        $dgRole->syncPermissions($dgPermissions);


        /**
         * =============================
         * 4️ ROLE BILL VERIFICATION OFFICER
         * =============================
         */
        $billVerificationPermissions = [
            'Bill Module',

            // Bills
            'View Bill',
            'Create Bill',
            'Update Bill',

            // Bill Items
            'View Bill Item',
            'Create Bill Item',
            'Update Bill Item',

            // Bill Files
            'View BillFile',
            'Update BillFile',

            // Related Data
            'View Patient',
            'View Referral',
            'View Treatment',

            // Reports
            'View Report',
        ];

        foreach ($billVerificationPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $billVerificationRoleData = ['name' => 'ROLE BILL VERIFICATION OFFICER', 'guard_name' => 'web'];
        if ($hasCreatedBy) {
            $billVerificationRoleData['created_by'] = 1;
        }

        $billVerificationRole = Role::firstOrCreate(['name' => 'ROLE BILL VERIFICATION OFFICER'], $billVerificationRoleData);
        $billVerificationRole->syncPermissions($billVerificationPermissions);
    }
}
