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
                'hospital_code' => 'HOSP001',
                'hospital_address' => 'Zanzibar',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Muhimbili Orthopaedic Institute (MOI)',
                'hospital_code' => 'HOSP002',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Jakaya Kikwete Cardiac Institute (JKCI)',
                'hospital_code' => 'HOSP003',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'SIMS',
                'hospital_code' => 'HOSP004',
                'hospital_address' => 'Address',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Muhimbili National Hospital (MNH)',
                'hospital_code' => 'HOSP005',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Kilimanjaro Christian Medical Centre (KCMC)',
                'hospital_code' => 'HOSP006',
                'hospital_address' => 'Dar es Salaam, Tanzania',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Ocean Road Cancer Institute (ORCI)',
                'hospital_code' => 'HOSP007',
                'hospital_address' => 'Address',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 1, // MAINLAND
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'hospital_name' => 'Madras Institute of Orthopaedics and Traumatology (MIOT)',
                'hospital_code' => 'HOSP008',
                'hospital_address' => 'India',
                'contact_number' => '000 000 000',
                'hospital_email' => 'hospital@gmail.com',
                'referral_type_id' => 2, // ABROAD
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
        );

        DB::table('hospitals')->insert($hospitals);
    }
}