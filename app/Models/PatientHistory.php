<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PatientHistory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'patient_histories';
    protected $primaryKey = 'patient_histories_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'patient_id',
        'diagnosis_id',
        'referring_doctor',
        'file_number',
        'referring_date',
        'history_of_presenting_illness',
        'physical_findings',
        'investigations',
        'management_done',
        'board_comments',
        'history_file',
    ];

    // Belongs to patient
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    // Belongs to diagnosis
    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class, 'diagnosis_id', 'diagnosis_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}

