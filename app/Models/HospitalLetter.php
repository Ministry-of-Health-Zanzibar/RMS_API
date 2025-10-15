<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class HospitalLetter extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $primaryKey = 'letter_id';
    public $incrementing = true;   // bigIncrements (integer PK)
    protected $keyType = 'integer';

    protected $fillable = [
        'referral_id',
        'content_summary',
        'next_appointment_date',
        'letter_file',
        'outcome',
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class, 'referral_id', 'referral_id');
    }

    public function followups()
    {
        return $this->hasMany(FollowUp::class, 'letter_id', 'letter_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
