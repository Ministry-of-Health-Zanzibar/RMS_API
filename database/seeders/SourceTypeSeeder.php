<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SourceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('source_types')->delete();

        $source_types = array(
            array(
                'source_type_name' => 'Elimu ya Afya',
                'source_type_code' => 'XJSOJD7W5Z',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'EPI',
                'source_type_code' => 'MDJS527JHQ',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'MAZINGIRA',
                'source_type_code' => 'MDJSS27JHS',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'UKIMWI',
                'source_type_code' => 'MDJMS27JHS',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'KINGA',
                'source_type_code' => 'MDJSKN27JHS',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'MIPANGO',
                'source_type_code' => 'MDXBD27JHS',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'UTUMISHI',
                'source_type_code' => 'MDJVC27JHS',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'source_type_name' => 'MNAZI MMOJA',
                'source_type_code' => 'MDJV237JHS',
                'source_id' => 3,
                'created_by' => '2',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),

        );

        DB::table('source_types')->insert($source_types);
    }
}