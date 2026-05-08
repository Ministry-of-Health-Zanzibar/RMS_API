<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardedOutLetter extends Model
{
    protected $table = 'boarded_out_letters';

    protected $fillable = [
        'patient_histories_id',
        'receiver',
        'reference_number',
        'reference_date',
        'recommendations',
    ];

    protected $casts = [
        'reference_date' => 'date',
        'recommendations' => 'array', // 🔥 auto JSON handling
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function patientHistory()
    {
        return $this->belongsTo(PatientHistory::class, 'patient_histories_id', 'patient_histories_id');
    }
}