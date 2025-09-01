<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class permission_info extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->delete();

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

            'Monthly Bill Module',
            'Create Monthly Bill',
            'Update Monthly Bill',
            'Delete Monthly Bill',
            'View Monthly Bill',

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

            // 'Accountant Module',
            // 'Create Source',
            // 'Update Source',
            // 'Delete Source',
            // 'View Source',

            // 'Create Source Type',
            // 'Update Source Type',
            // 'Delete Source Type',
            // 'View Source Type',

            // 'Create Category',
            // 'Update Category',
            // 'Delete Category',
            // 'View Category',

            // 'Create Document Type',
            // 'Update Document Type',
            // 'Delete Document Type',
            // 'View Document Type',

            // 'Create Document Form',
            // 'Update Document Form',
            // 'Delete Document Form',
            // 'View Document Form',
            // 'View Report',
        ];

        foreach ($permissions as $permission) {

            Permission::create(['name' => $permission, 'guard_name' => 'web']);

        }
    }
}