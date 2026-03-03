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
    protected $keyType = 'integer';

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
            // 1. Ensure board_date is not empty
            if (empty($patientList->board_date)) {
                $patientList->board_date = now()->toDateString();
            }
        
            // 2. Safely parse the date
            try {
                // Handle JS Date strings (like "Thu Feb 05 2026...")
                $boardDate = \Carbon\Carbon::parse($patientList->board_date);
                
                // If Carbon parsed it but it ended up as 1970 (false positive),
                // or if it's way in the past, fall back to now.
                if ($boardDate->year <= 1970) {
                    $boardDate = now();
                }
            } catch (\Exception $e) {
                $boardDate = now(); // Fallback to current date instead of 1970
            }
        
            // 3. Update the field to a clean Y-m-d format for the database
            $patientList->board_date = $boardDate->toDateString();
        
            // 4. Generate Reference and Title using the valid $boardDate
            $formattedDate = $boardDate->format('d/m/Y');
        
            $boardTypeMap = [
                'Emergency' => 'EMG',
                'Routine'   => 'RTN',
            ];
            $boardTypeAbbr = $boardTypeMap[ucfirst(strtolower($patientList->board_type))] ?? 'RTN';
            $formattedNum = str_pad($patientList->no_of_patients ?? 1, 3, '0', STR_PAD_LEFT);
        
            $patientList->reference_number = sprintf(
                'MBM-%s-%s-%s-%s',
                $formattedDate,
                $boardTypeAbbr,
                $formattedNum,
                now()->format('H-i')
            );
        
            $patientList->patient_list_title = sprintf(
                'MBM of %s at %s',
                $formattedDate,
                now()->format('h:i a')
            );
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function boardMembers()
    {
        return $this->belongsToMany(User::class, 'medical_board_user', 'patient_list_id', 'user_id');
    }

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_list_patient', 'patient_list_id', 'patient_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
