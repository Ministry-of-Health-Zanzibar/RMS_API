<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Patient extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'patients';

    protected $primaryKey = 'patient_id'; // custom PK
    public $incrementing = true;         // no auto-increment
    protected $keyType = 'string';        // not an integer

    protected $fillable = [
        'patient_id',
        'name',
        'date_of_birth',
        'gender',
        'phone',
        'location',
        'job',
        'position',
        'patient_list_id',
        'created_by',
    ];

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
        return $this->belongsTo(PatientList::class, 'patient_list_id', 'patient_list_id');
    }

    public function files()
    {
        return $this->hasMany(PatientFile::class, 'patient_id', 'patient_id');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}