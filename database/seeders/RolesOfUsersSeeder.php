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

            'View Reason',
            'View Location',

            'Create Insurance',
            'Update Insurance',
            'Delete Insurance',
            'View Insurance',

            'Patient Module',
            'Create Patient',
            'Update Patient',
            'Delete Patient',
            'View Patient',

            'Create Patient List',
            'Update Patient List',
            'Delete Patient List',
            'View Patient List',

            'View Patient History',
            'Create Patient History',
            'Update Patient History',
            'Delete Patient History',

            'View Diagnoses',
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

            'View Referral Dashboard',
            'View Permission',
            'System Audit',
            'Report Management',

            'User Management',
            'Create User',
            'Update User',
            'Delete User',
            'View User',

            'Create Role',
            'Update Role',
            'Delete Role',
            'View Role',

            'Setup Management',
            'Create Location',
            'Update Location',
            'Delete Location',

            'Create Facility Level',
            'Update Facility Level',
            'Delete Facility Level',

            'Create Identification',
            'Update Identification',
            'Delete Identification',

            'Create Upload Types',
            'Update Upload Types',
            'Delete Upload Types',

            'Create Parent Upload Type',
            'Update Parent Upload Type',
            'Delete Parent Upload Type',

            'Create Hospital',
            'Update Hospital',
            'Delete Hospital',
            'View Hospital',

            'Create ReferralType',
            'Update ReferralType',
            'Delete ReferralType',
            'View ReferralType',

            'Create ReferralLetter',
            'Update ReferralLetter',
            'Delete ReferralLetter',
            'View ReferralLetter',

            'Create Reason',
            'Update Reason',
            'Delete Reason',
            'View Reason',

            'Create Insurance',
            'Update Insurance',
            'Delete Insurance',
            'View Insurance',

            'Patient Module',
            'Create Patient',
            'Update Patient',
            'Delete Patient',
            'View Patient',

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

            'Treatment Module',
            'Create Treatment',
            'Update Treatment',
            'Delete Treatment',
            'View Treatment',

            'Create Patient List',
            'Update Patient List',
            'Delete Patient List',
            'View Patient List',

            'Create FollowUp',
            'Update FollowUp',
            'Delete FollowUp',
            'View FollowUp',

            'Create Hospital Letter',
            'Update Hospital Letter',
            'Delete Hospital Letter',
            'View Hospital Letter',

            'View BillFile',
            'Create BillFile',
            'Update BillFile',
            'Delete BillFile',

            'View Bill Item',
            'Create Bill Item',
            'Update Bill Item',
            'Delete Bill Item',

            'View Disease',
            'Create Disease',
            'Update Disease',
            'Delete Disease',

            'View Patient History',
            'Create Patient History',
            'Update Patient History',
            'Delete Patient History',

            'View Diagnoses',
            'Create Diagnoses',
            'Update Diagnoses',
            'Delete Diagnoses',
            'Restore Diagnoses',
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
            'Report Management',

            'View Hospital',

            'View ReferralType',

            'Create ReferralLetter',
            'Update ReferralLetter',
            'Delete ReferralLetter',
            'View ReferralLetter',

            'Referral Module',
            'Create Referral',
            'Update Referral',
            'Delete Referral',
            'View Referral',

            'View FollowUp',

            'View Patient List',

            'View Hospital Letter',

            'View Patient History',

            'View Diagnoses',
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
            'View Referral Dashboard',

            'Referral Module',
            'Create Referral',
            'Update Referral',
            'Delete Referral',
            'View Referral',

            'View BillFile',
            'Create BillFile',
            'Update BillFile',
            'Delete BillFile',

            'Bill Module',
            'Create Bill',
            'Update Bill',
            'Delete Bill',
            'View Bill',

            'View Bill Item',
            'Create Bill Item',
            'Update Bill Item',
            'Delete Bill Item',

            'Create FollowUp',
            'Update FollowUp',
            'Delete FollowUp',
            'View FollowUp',

            'Create Hospital Letter',
            'Update Hospital Letter',
            'Delete Hospital Letter',
            'View Hospital Letter',
            

            'View Reason',
            'View Location',

            'Patient Module',
            'Create Patient',
            'Update Patient',
            'Delete Patient',
            'View Patient',

            'Create Patient List',
            'Update Patient List',
            'Delete Patient List',
            'View Patient List',

            'View Patient History',
            'Create Patient History',
            'Update Patient History',
            'Delete Patient History',

            'View Diagnoses',
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
