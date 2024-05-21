<?php

namespace App\Imports;

use App\Models\Identifications;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class IdentificationsImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Identifications([
            //
            'identification_name' => $row['identification_name'],
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.identification_name' =>['required'],
            '*.created_by' =>['required']
        ];
    }
}
