<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Treatment extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $fillable = [
        'referral_id',
        'received_date',
        'started_date',
        'ended_date',
        'treatment_status',
        'measurements',
        'disease_id',
        'treatment_file'
    ];

    public function referral()
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }

    public function disease()
    {
        return $this->belongsTo(Disease::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
