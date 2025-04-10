<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('hospitals')->delete();

        $hospitals = array(
            array(
                'hospital_name' => 'LUMUMBA',
                'hospital_address' => 'Zanzibar',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Muhimbili Orthopaedic Institute (MOI)',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Jakaya Kikwete Cardiac Institute (JKCI)',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'SIMS',
                'hospital_address' => 'Address',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),

            array(
                'hospital_name' => 'Muhimbili National Hospital (MNH)',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Kilimanjaro Christian Medical Centre (KCMC)',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Ocean Road Cancer Institute (ORCI)',
                'hospital_address' => 'Address',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => ' Madras Institute of Orthopaedics and Traumatology (MIOT)',
                'hospital_address' => 'India',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
        );

        DB::table('hospitals')->insert($hospitals);
    }
}