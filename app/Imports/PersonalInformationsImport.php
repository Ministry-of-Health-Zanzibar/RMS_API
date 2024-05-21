<?php

namespace App\Imports;

use App\Models\PersonalInformations;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
// use Maatwebsite\Excel\Concerns\ToCollection;

class PersonalInformationsImport implements  ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new PersonalInformations([
            'personal_information_id'=> $row['personal_information_id'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'last_name'=> $row['last_name'],
            'sur_name' => $row['sur_name'],
            'phone_no' => $row['phone_no'],
            'date_of_birth' => $row['date_of_birth'],
            'email' => $row['email'],
            'gender'=> $row['gender'],
            'physical_address' => $row['physical_address'],
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.personal_information_id' =>['unique:personal_informations'],
            '*.first_name' =>['required'],
            '*.middle_name' =>['required'],
            '*.last_name' =>['required'],
            '*.sur_name' =>['required'],
            '*.phone_no' =>['required'],
            '*.date_of_birth' =>['required'],
            '*.gender' =>['required'],
            '*.physical_address' =>['required'],
            '*.created_by' =>['required'],
        ];
    }
}
