<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AccountantInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        //
        // DB::table('model_has_roles')->delete();

        // $accountantRole = [
        //     [
        //         'role_id' => 4,
        //         'model_type' => 'App\Models\User',
        //         'model_id' => 1
        //     ]
        // ];

        // DB::table('model_has_roles')->insert($accountantRole);


        // Insert account info
        DB::table('users')->delete();
        $user = User::create([

            'first_name' => 'Accountant',
            'middle_name' => 'Accountant',
            'last_name' => 'Accountant',
            'address' => 'Kilimani',
            'gender' => 'Male',
            'phone_no' => '0777000003',
            'date_of_birth' => '1990-10-30',
            'email' => 'a@mohz.go.tz',
            'password' => bcrypt('admin@123')
        ]);

        $role = Role::create(['name' => 'ROLE ACCOUNTANT']);

        // $permissions = Permission::pluck('id', 'id')->all();

        $permissionNames = [
            'View Dashboard',
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

            // 'Create Document Type',
            // 'Update Document Type',
            // 'Delete Document Type',
            // 'View Document Type',

            'Create Document Form',
            'Update Document Form',
            'Delete Document Form',
            'View Document Form',
        ];
        $permissions = Permission::whereIn('name', $permissionNames)->get();


        $role->syncPermissions($permissions);
        $user->givePermissionTo($permissions);
        $user->assignRole([$role->id]);

    }
}