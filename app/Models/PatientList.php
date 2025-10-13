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
    public $incrementing = true;      // since it's bigIncrements
    protected $keyType = 'int';       // integer PK

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

            // Convert board_type to uppercase and shorten it
            $boardType = strtoupper($patientList->board_type ?? 'ROUTINE');

            // Abbreviate
            $boardTypeAbbr = match ($boardType) {
                'EMERGENCY' => 'EMG',
                'ROUTINE' => 'RTN',
                default => substr($boardType, 0, 3),
            };

            // Determine next patient number for that day/type
            $latest = self::whereDate('board_date', $patientList->board_date)
                ->where('board_type', $patientList->board_type)
                ->max('no_of_patients');

            $patientList->no_of_patients = $latest ? $latest + 1 : 1;

            // Format to 3 digits (e.g. 005)
            $formattedNum = str_pad($patientList->no_of_patients, 3, '0', STR_PAD_LEFT);

            // Generate reference number
            $patientList->reference_number = sprintf(
                'REFF-%s-%s-%s',
                $patientList->board_date,
                $boardTypeAbbr,
                $formattedNum
            );

            // Save back the abbreviation version (optional)
            $patientList->board_type = $boardTypeAbbr;
        });
    }

    /**
     * Relationship: PatientList belongs to a User (creator).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'patient_list_id', 'patient_list_id');
    }

    /**
     * Configure Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
