<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Specialisation extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $primaryKey = 'referral_id';  // Set the primary key to referral_id
    public $incrementing = true;  // Disable auto-increment

    protected $fillable = [
        'specialisation_name',
        'specialisation_descriptions',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
