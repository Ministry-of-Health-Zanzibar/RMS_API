<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sources')->delete();

        $sources = array(
            array(
                'source_name' => 'SMZ',
                'source_code' => 'XJSJJD765Z',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_name' => 'PRIVATE',
                'source_code' => 'MDJS527JHS',
                'created_by' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),

        );

        DB::table('sources')->insert($sources);
    }
}