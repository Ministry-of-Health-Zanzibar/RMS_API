<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reasons')->delete();

        $reasons = array(
            array(
                'referral_reason_name' => 'Kufanyiwa uchunguzi',
                'reason_descriptions' => 'Here is description',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'referral_reason_name' => 'Kupatiwa matibabu',
                'reason_descriptions' => 'Here is description',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'referral_reason_name' => 'Uchunguzi na matibabu zaidi',
                'reason_descriptions' => 'Here is description',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),

            array(
                'referral_reason_name' => 'Uchunguzi na matibabu',
                'reason_descriptions' => 'Here is description',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'referral_reason_name' => 'Uchunguzi na matibabu zaidi',
                'reason_descriptions' => 'Here is description',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'referral_reason_name' => 'Pars Plana  Vitrotomy (PPV)',
                'reason_descriptions' => 'Here is description',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
        );

        DB::table('reasons')->insert($reasons);
    }
}