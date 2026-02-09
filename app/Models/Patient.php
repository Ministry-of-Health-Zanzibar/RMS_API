<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\GeographicalLocations;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Patient extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $table = 'patients';
    protected $primaryKey = 'patient_id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $fillable = [
        'patient_id',
        'matibabu_card',
        'zan_id',
        'name',
        'date_of_birth',
        'gender',
        'phone',
        'location_id',
        'job',
        'position',
        'created_by',
    ];

    public function geographicalLocation()
    {
        return $this->belongsTo(GeographicalLocations::class, 'location_id', 'location_id');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'patient_id', 'patient_id');
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class, 'patient_id', 'patient_id');
    }

    public function patientList()
    {
        return $this->belongsToMany(PatientList::class, 'patient_list_patient', 'patient_id', 'patient_list_id')->withTimestamps();
    }

    public function patientHistories()
    {
        return $this->hasMany(PatientHistory::class, 'patient_id', 'patient_id');
    }

    // public function latestHistory()
    // {
    //     return $this->hasOne(PatientHistory::class, 'patient_id', 'patient_id')->latest('patient_histories_id');
    // }
    public function latestHistory()
    {
        // latestOfMany() ensures the relationship only points to the absolute latest record
        return $this->hasOne(PatientHistory::class, 'patient_id', 'patient_id')
            ->latestOfMany('patient_histories_id');
    }

    public function files()
    {
        return $this->hasMany(PatientFile::class, 'patient_id', 'patient_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function followups()
    {
        return $this->hasMany(FollowUp::class, 'patient_id', 'patient_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
