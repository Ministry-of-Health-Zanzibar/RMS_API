<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class permission_info extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
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
            'View Location',

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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }
    }
}
