<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('document_types')->delete();

        $document_types = array(
            array(
                'document_type_name' => 'Voucher',
                'document_type_code' => 'BXKDN67DJSBD',
                'created_by' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'document_type_name' => 'Dokezo',
                'document_type_code' => 'XNMDLSHDO9',
                'created_by' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'document_type_name' => 'Mfuko Mkuu',
                'document_type_code' => 'XNMD2SHDO9',
                'created_by' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),
            array(
                'document_type_name' => 'Ubani',
                'document_type_code' => 'XNMPLSHDO9',
                'created_by' => 2,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now()
            ),

        );

        DB::table('document_types')->insert($document_types);
    }
}