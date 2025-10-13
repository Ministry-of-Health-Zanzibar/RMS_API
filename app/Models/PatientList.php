<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PatientList extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $table = 'patient_lists';
    protected $primaryKey = 'patient_list_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'reference_number',
        'board_type',
        'no_of_patients',
        'board_date',
        'patient_list_title',
        'patient_list_file',
        'created_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patientList) {

            // Ensure board_date exists
            if (empty($patientList->board_date)) {
                $patientList->board_date = now()->toDateString();
            }

            // Full board type
            $boardTypeFull = ucfirst(strtolower($patientList->board_type ?? 'Routine'));

            // Abbreviation for reference number
            $boardTypeAbbr = match ($boardTypeFull) {
                'Emergency' => 'EMG',
                'Routine' => 'RTN',
                default => substr($boardTypeFull, 0, 3),
            };

            // Use user-provided no_of_patients, no auto-generation
            $numPatients = $patientList->no_of_patients ?? 1;

            // Format to 3 digits for reference number
            $formattedNum = str_pad($numPatients, 3, '0', STR_PAD_LEFT);

            // Generate reference number
            $patientList->reference_number = sprintf(
                'MBM-%s-%s-%s',
                $patientList->board_date,
                $boardTypeAbbr,
                $formattedNum
            );

            // Save full board_type
            $patientList->board_type = $boardTypeFull;
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'patient_list_id', 'patient_list_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
