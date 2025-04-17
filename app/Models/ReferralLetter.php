<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ReferralLetter extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'referral_letters';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'referral_letter_id';

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
        'referral_id',
        'letter_text',
        'start_date',
        'end_date',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function Referral_letter()
    {
        return $this->belongsTo(Referral_letter::class);
    }


    // Automatically generate referral_letter_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referalLetterCodeNumber) {
            if (empty($referalLetterCodeNumber->referral_letter_code)) {
                $referalLetterCodeNumber->referral_letter_code = self::generateReferalLetterCodeNumber();
            }
        });
    }

    // Generate random unique referral_letter_code
    private static function generateReferalLetterCodeNumber()
    {
        do {
            $referalLetterCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('referral_letter_code', $referalLetterCodeNumber)->exists());

        return $referalLetterCodeNumber;
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}