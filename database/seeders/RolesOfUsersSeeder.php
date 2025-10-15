<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesOfUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * =============================
         * 1️ MEDICAL BOARD MEMBER
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

        $medicalBoardRole = Role::firstOrCreate(['name' => 'Medical Board Member']);
        $medicalBoardRole->syncPermissions(Permission::whereIn('name', $medicalBoardPermissions)->get());


        /**
         * =============================
         * 2️ ACCOUNTANT
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

        $accountantRole = Role::firstOrCreate(['name' => 'Accountant']);
        $accountantRole->syncPermissions(Permission::whereIn('name', $accountantPermissions)->get());


        /**
         * =============================
         * 3️ DIRECTOR GENERAL (DG)
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

        $dgRole = Role::firstOrCreate(['name' => 'ROLE DG']);
        $dgRole->syncPermissions(Permission::whereIn('name', $dgPermissions)->get());


        /**
         * =============================
         * 4️ BILL VERIFICATION OFFICER
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

        $billVerificationRole = Role::firstOrCreate(['name' => 'Bill Verification Officer']);
        $billVerificationRole->syncPermissions(Permission::whereIn('name', $billVerificationPermissions)->get());
    }
}
