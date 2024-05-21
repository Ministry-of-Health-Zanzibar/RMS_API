<?php

namespace App\Imports;

use App\Models\GeographicalLocations;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GeographicalLocationsImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new GeographicalLocations([
            //
            'location_id'=> $row['location_id'],
            'location_name' => $row['location_name'],
            'parent_id' => $row['parent_id'],
            'label' => $row['label'],
            'created_by' => $row['created_by']
        ]);
    }

    public function rules():array{
        return [
            '*.location_id' =>['unique:geographical_locations'],
            '*.location_name' =>['required'],
            '*.parent_id' =>['required'],
            '*.label' =>['required'],
            '*.created_by' =>['required']
        ];
    }
}
