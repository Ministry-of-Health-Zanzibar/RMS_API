<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Reason extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $fillable = [
        'referral_reason_name',
        'reason_descriptions',
    ];

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
