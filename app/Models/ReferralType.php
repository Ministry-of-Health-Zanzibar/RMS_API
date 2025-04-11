<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralType extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'referral_types';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'referral_type_id';

    /**
     * Indicates if the model's ID is not auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';


    protected $fillable = [
        'referral_type_name',
        'created_by',
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


    // Automatically generate hospital_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referral_typeCodeNumber) {
            if (empty($referral_typeCodeNumber->referral_type_code)) {
                $referral_typeCodeNumber->referral_type_code = self::generateHospitalCodeNumber();
            }
        });
    }

    // Generate random unique hospital_code
    private static function generateHospitalCodeNumber()
    {
        do {
            $referral_typeCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('referral_type_code', $referral_typeCodeNumber)->exists());

        return $referral_typeCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
