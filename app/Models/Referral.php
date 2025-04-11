<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Referral extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $primaryKey = 'referral_id';  // Set the primary key to referral_id
    public $incrementing = true;  // Disable auto-increment

    protected $fillable = [
        'patient_id',
        'hospital_id',
        'referral_type_id',
        'reason_id',
        'start_date',
        'end_date',
        'status',
        'confirmed_by',
        'created_by',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function referralType()
    {
        return $this->belongsTo(ReferralType::class);
    }

    public function reason()
    {
        return $this->belongsTo(Reason::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}