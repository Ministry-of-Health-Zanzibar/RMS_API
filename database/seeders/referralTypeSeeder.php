<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class referralTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('referral_types')->delete();

        $referral_types = array(
            array(
                'referral_type_name' => 'MAINLAND',
                'referral_type_code' => 'REFTYPE1',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'referral_type_name' => 'INLAND',
                'referral_type_code' => 'REFTYPE2',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            
        );

        DB::table('referral_types')->insert($referral_types);
    }
}
