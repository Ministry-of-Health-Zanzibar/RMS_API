<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class batch_year extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batch_years = [
            [
                'batch_year' => '2023/2024',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ]

        ];

          DB::table('batch_years')->insert($batch_years);
    }
}
