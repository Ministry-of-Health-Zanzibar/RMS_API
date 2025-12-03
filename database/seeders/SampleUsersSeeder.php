<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SampleUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Define sample users with their roles and required fields
        $users = [
            [
                'first_name' => 'Admin',
                'middle_name' => 'System',
                'last_name' => 'User',
                'email' => 'admin@mohz.go.tz',
                'password' => 'admin@123',
                'role' => 'ROLE ADMIN',
                'address' => 'Vuga',
                'gender' => 'Male',
                'phone_no' => '0777000002',
                'date_of_birth' => '1990-01-01',
            ],
            [
                'first_name' => 'Medical',
                'middle_name' => 'Board',
                'last_name' => 'Member',
                'email' => 'medicalboard@mohz.go.tz',
                'password' => 'board@123',
                'role' => 'ROLE MEDICAL BOARD MEMBER',
                'address' => 'Vuga',
                'gender' => 'Male',
                'phone_no' => '0777000003',
                'date_of_birth' => '1990-02-01',
            ],
            [
                'first_name' => 'Accountant',
                'middle_name' => 'Finance',
                'last_name' => 'User',
                'email' => 'accountant@mohz.go.tz',
                'password' => 'accountant@123',
                'role' => 'ROLE ACCOUNTANT',
                'address' => 'Vuga',
                'gender' => 'Female',
                'phone_no' => '0777000004',
                'date_of_birth' => '1990-03-01',
            ],
            [
                'first_name' => 'DG',
                'middle_name' => 'Director',
                'last_name' => 'General',
                'email' => 'dg@mohz.go.tz',
                'password' => 'dg@123',
                'role' => 'ROLE DIRECTOR GENERAL',
                'address' => 'Vuga',
                'gender' => 'Male',
                'phone_no' => '0777000005',
                'date_of_birth' => '1990-04-01',
            ],
            [
                'first_name' => 'Bill',
                'middle_name' => 'Verifier',
                'last_name' => 'User',
                'email' => 'billverifier@mohz.go.tz',
                'password' => 'bill@123',
                'role' => 'ROLE BILL VERIFICATION OFFICER',
                'address' => 'Vuga',
                'gender' => 'Female',
                'phone_no' => '0777000006',
                'date_of_birth' => '1990-05-01',
            ],
            [
                'first_name' => 'Hospital',
                'middle_name' => 'User',
                'last_name' => 'Sample',
                'email' => 'hospitaluser@mohz.go.tz',
                'password' => 'hospital@123',
                'role' => 'ROLE HOSPITAL USER',
                'address' => 'Vuga',
                'gender' => 'Female',
                'phone_no' => '0777000007',
                'date_of_birth' => '1990-06-01',
            ],
            [
                'first_name' => 'Mkurugenzi',
                'middle_name' => 'Tiba',
                'last_name' => 'User',
                'email' => 'mkurugenzi@mohz.go.tz',
                'password' => 'mkurugenzi@123',
                'role' => 'ROLE MKURUGENZI TIBA',
                'address' => 'Vuga',
                'gender' => 'Male',
                'phone_no' => '0777000008',
                'date_of_birth' => '1990-07-01',
            ],
        ];

        foreach ($users as $data) {

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                    'last_name' => $data['last_name'],
                    'address' => $data['address'],
                    'gender' => $data['gender'],
                    'phone_no' => $data['phone_no'],
                    'date_of_birth' => $data['date_of_birth'],
                    'password' => Hash::make($data['password']),
                    'created_by' => 1,
                    'login_status' => 1,
                ]
            );

            $role = Role::where('name', $data['role'])->first();

            if ($role) {
                $user->syncRoles([$role->name]);
                $user->syncPermissions($role->permissions->pluck('id')->toArray());
            }
        }
    }
}
