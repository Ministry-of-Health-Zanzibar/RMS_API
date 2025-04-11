<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Reason extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reasons';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'reason_id';

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
        'referral_reason_name',
        'reason_descriptions',
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


    /* // Automatically generate hospital_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hospitalCodeNumber) {
            if (empty($hospitalCodeNumber->hospital_code)) {
                $hospitalCodeNumber->hospital_code = self::generateHospitalCodeNumber();
            }
        });
    } */

    /* // Generate random unique hospital_code
    private static function generateHospitalCodeNumber()
    {
        do {
            $hospitalCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('hospital_code', $hospitalCodeNumber)->exists());

        return $hospitalCodeNumber;
    } */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
