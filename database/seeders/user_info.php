<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class user_info extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    // public function run()
    // {
    //     //
    //     DB::table('users')->delete();
    //     $user = User::create([

    //         'first_name' => 'System',
    //         'middle_name' => 'Supper',
    //         'last_name' => 'Admin',
    //         'address' => 'Kilimani',
    //         'gender' => 'Male',
    //         'phone_no' => '0777000001',
    //         'date_of_birth' => '1990-10-30',
    //         'email' => 'info@mohz.go.tz',
    //         'password' => bcrypt('admin@123')
    //     ]);

    //     $role = Role::create(['name' => 'ROLE ADMIN']);

    //     $permissions = Permission::pluck('id', 'id')->all();

    //     $role->syncPermissions($permissions);
    //     $user->givePermissionTo($permissions);
    //     $user->assignRole([$role->id]);
    // }


    public function run()
    {
        // Clean users and roles if needed
        DB::table('users')->delete();
        DB::table('roles')->delete();

        // 1. Create Admin user
        $admin = User::create([
            'first_name' => 'System',
            'middle_name' => 'Supper',
            'last_name' => 'Admin',
            'address' => 'Kilimani',
            'gender' => 'Male',
            'phone_no' => '0777000001',
            'date_of_birth' => '1990-10-30',
            'email' => 'info@mohz.go.tz',
            'password' => bcrypt('admin@123')
        ]);

        $adminRole = Role::create(['name' => 'ROLE ADMIN']);
        $allPermissions = Permission::pluck('id')->all();

        $adminRole->syncPermissions($allPermissions);
        $admin->givePermissionTo($allPermissions);
        $admin->assignRole($adminRole);

        // 2. Create Accountant user
        $accountant = User::create([
            'first_name' => 'Accountant',
            'middle_name' => 'Accountant',
            'last_name' => 'Accountant',
            'address' => 'Kilimani',
            'gender' => 'Male',
            'phone_no' => '0777000003',
            'date_of_birth' => '1990-10-30',
            'email' => 'accountant@mohz.go.tz',
            'password' => bcrypt('accountant@123')
        ]);

        $accountantRole = Role::create(['name' => 'ROLE ACCOUNTANT']);

        $accountantPermissions = Permission::whereIn('name', [
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

            'View Report',
        ])->get();

        $accountantRole->syncPermissions($accountantPermissions);
        $accountant->givePermissionTo($accountantPermissions);
        $accountant->assignRole($accountantRole);
    }

}