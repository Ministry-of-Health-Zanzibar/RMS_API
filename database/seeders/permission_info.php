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
        //  Permission::truncate();

        $permissions = [
            
            'View Dashboard',
            'View Permission',
            'System Audit',
            'Report Management',

            'User Management',
                'Create User',
                'Update User',
                'Delete User',

                'Create Role',
                'Update Role',
                'Delete Role',

            'Setup Management',
                'Create Location',
                'Update Location',
                'Delete Location',

                'Create Facility Level',
                'Update Facility Level',
                'Delete Facility Level',

                'Create Education Level',
                'Update Education Level',
                'Delete Education Level',

                'Create Admin Hierarchies',
                'Update Admin Hierarchies',
                'Delete Admin Hierarchies',

                'Create Work Station',
                'Update Work Station',
                'Delete Work Station',

                'Create Marital Status',
                'Update Marital Status',
                'Delete Marital Status',

                'Create Identification',
                'Update Identification',
                'Delete Identification',

                'Create Specialization',
                'Update Specialization',
                'Delete Specialization',

                'Create Relation',
                'Update Relation',
                'Delete Relation',

                'Create Department',
                'Update Department',
                'Delete Department',

                'Create Designation',
                'Update Designation',
                'Delete Designation',

                'Create Employment Status',
                'Update Employment Status',
                'Delete Employment Status',

                'Create Institute',
                'Update Institute',
                'Delete Institute',

                'Create Senority',
                'Update Senority',
                'Delete Senority',

                'Create Term Of Employment',
                'Update Term Of Employment',
                'Delete Term Of Employment',

                'Create Upload Types',
                'Update Upload Types',
                'Delete Upload Types',

                'Create Parent Upload Type',
                'Update Parent Upload Type',
                'Delete Parent Upload Type',

                'Create Cader',
                'Update Cader',
                'Delete Cader',

                'Create Disability',
                'Update Disability',
                'Delete Disability',

                'Create Post Category',
                'Update Post Category',
                'Delete Post Category',

                'Create Skills',
                'Update Skills',
                'Delete Skills',

                'Create Language',
                'Update Language',
                'Delete Language',

                'Create Hobby',
                'Update Hobby',
                'Delete Hobby',

                'Create Employer',
                'Update Employer',
                'Delete Employer',

                'Create Working Position',
                'Update Working Position',
                'Delete Working Position',

                'Create Position Post',
                'Update Position Post',
                'Delete Position Post',

                'Create Health Body',
                'Update Health Body',
                'Delete Health Body',

                'Create Standard Level',
                'Update Standard Level',
                'Delete Standard Level',

                'Create Salary Scale',
                'Update Salary Scale',
                'Delete Salary Scale',

                'Delete Salary Source',

            'Staff Management',
                'Create Employee',
                'Update Employee',
                'Delete Employee',


         ];

         foreach ($permissions as $permission) {
 
            Permission::create(['name' => $permission,'guard_name'=>'web']);
 
         }
    }
}
