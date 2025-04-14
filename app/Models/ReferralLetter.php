<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

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
        'is_printed',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    public function Referral_letter()
    {
        return $this->belongsTo(Referral_letter::class);
    }


    // Automatically generate insurance_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referralLettersCodeNumber) {
            if (empty($referralLettersCodeNumber->referral_letter_code)) {
                $referralLettersCodeNumber->referral_letter_code = self::generatereferralLetterCodeNumber();
            }
        });
    }

    // Generate random unique insurance_code
    private static function generatereferralLetterCodeNumber()
    {
        do {
            $referralLettersCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('referral_letter_code', $referralLettesrCodeNumber)->exists());

        return $referralLettersCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
