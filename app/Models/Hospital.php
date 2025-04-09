<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Hospital extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $fillable = [
        'hospital_name',
        'hospital_address',
        'contact_number',
        'hospital_email',
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Optional: Define relationships here if needed
    // e.g., A hospital has many referrals
    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
