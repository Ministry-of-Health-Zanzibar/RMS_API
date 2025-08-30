<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FollowUp extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $table = 'followups';
    protected $primaryKey = 'followup_id';
    public $incrementing = true;   // id() is auto-increment
    protected $keyType = 'int';

    protected $fillable = [
        'patient_id',
        'letter_id',
        'followup_date',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }

    public function hospitalLetter()
    {
        return $this->belongsTo(HospitalLetter::class, 'letter_id', 'letter_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
