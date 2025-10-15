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
    protected $keyType = 'integer';

    protected $fillable = [
        'patient_id',
        'reason_id',
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

    /**
     * Belongs to Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function reason()
    {
        return $this->belongsTo(Reason::class, 'reason_id', 'reason_id');
    }

    /**
     * Many-to-many relation with Diagnosis via pivot table
     */
    public function diagnoses()
    {
        return $this->belongsToMany(
            Diagnosis::class,
            'history_diagnosis',
            'patient_histories_id',
            'diagnosis_id'
        );
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
