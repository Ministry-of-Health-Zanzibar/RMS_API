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
    protected $appends = ['status_tracking','progress_percentage'];
    protected $primaryKey = 'patient_histories_id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $fillable = [
        'patient_id',
        'reason_id',
        'case_type',
        'referring_doctor',
        'file_number',
        'referring_date',
        'history_of_presenting_illness',
        'physical_findings',
        'investigations',
        'management_done',
        'board_comments',
        'board_reason_id',
        'history_file',
        'status',
        'mkurugenzi_tiba_comments',
        'dg_comments',
        'mkurugenzi_tiba_id',
        'dg_id',
    ];

    public const STATUS_MAP = [
        'pending' => [
            'stage' => 1,
            'label' => 'Submitted by Hospital',
            'current_holder' => 'Mkurugenzi wa Tiba',
            'description' => 'Medical history submitted and awaiting review',
        ],
        'reviewed' => [
            'stage' => 2,
            'label' => 'Reviewed by Mkurugenzi',
            'current_holder' => 'Medical Board',
            'description' => 'Reviewed and forwarded to medical board',
        ],
        'requested' => [
            'stage' => 3,
            'label' => 'More Info Requested',
            'current_holder' => 'Hospital / Mkurugenzi',
            'description' => 'Medical board requested additional information',
        ],
        'approved' => [
            'stage' => 4,
            'label' => 'Approved by Mkurugenzi',
            'current_holder' => 'Director General (DG)',
            'description' => 'Approved and sent to DG for confirmation',
        ],
        'confirmed' => [
            'stage' => 5,
            'label' => 'Confirmed by DG',
            'current_holder' => 'Completed',
            'description' => 'Final approval completed',
        ],
        'rejected' => [
            'stage' => 0,
            'label' => 'Rejected by DG',
            'current_holder' => 'Closed',
            'description' => 'Medical history rejected',
        ],
    ];

    public function getStatusTrackingAttribute()
    {
        return self::STATUS_MAP[$this->status] ?? null;
    }

    public function getProgressPercentageAttribute()
    {
        if (!isset(self::STATUS_MAP[$this->status])) {
            return '0%';
        }

        $maxStage = 5;
        $stage = self::STATUS_MAP[$this->status]['stage'];

        return (int) round(($stage / $maxStage) * 100) . '%';
    }





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

    public function boardReason()
    {
        return $this->belongsTo(Reason::class, 'board_reason_id', 'reason_id');
    }

    // Diagnoses
    public function diagnoses()
    {
        return $this->belongsToMany(
            Diagnosis::class,
            'history_diagnosis',
            'patient_histories_id',
            'diagnosis_id'
        )
        ->withPivot('added_by')
        ->wherePivot('added_by', 'doctor');
    }

    // Board diagnoses
    public function boardDiagnoses()
    {
        return $this->belongsToMany(
            Diagnosis::class,
            'history_diagnosis',
            'patient_histories_id',
            'diagnosis_id'
        )
        ->withPivot('added_by')
        ->wherePivot('added_by', 'medical_board');
    }

    public function referrals()
    {
        return $this->hasManyThrough(
            Referral::class,
            Patient::class,
            'patient_id', // Foreign key on PatientHistory (belongs to patient)
            'patient_id', // Foreign key on Referral
            'patient_id', // Local key on PatientHistory
            'patient_id'  // Local key on Patient
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
