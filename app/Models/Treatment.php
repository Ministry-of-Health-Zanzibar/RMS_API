<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Treatment extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;
    protected $primaryKey = 'treatment_id';  // Set the primary key to treatment_id
    public $incrementing = true;  // Disable auto-increment
    
    protected $fillable = [
        'referral_id',
        'received_date',
        'started_date',
        'ended_date',
        'treatment_status',
        'measurements',
        'disease',
        'treatment_file',
        'created_by'
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
