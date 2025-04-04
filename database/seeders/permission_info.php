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

                'Create Identification',
                'Update Identification',
                'Delete Identification',

                'Create Upload Types',
                'Update Upload Types',
                'Delete Upload Types',

                'Create Parent Upload Type',
                'Update Parent Upload Type',
                'Delete Parent Upload Type',


         ];

         foreach ($permissions as $permission) {
 
            Permission::create(['name' => $permission,'guard_name'=>'web']);
 
         }
    }
}
