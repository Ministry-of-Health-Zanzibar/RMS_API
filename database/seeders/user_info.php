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
    public function run()
    {
        //
        DB::table('users')->delete();
        $user = User::create([

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

        $role = Role::create(['name' => 'ROLE ADMIN']);

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);
        $user->givePermissionTo($permissions);
        $user->assignRole([$role->id]);
    }
}