<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insurance extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'insurances';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'insurance_id';

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
        'insurance_provider_name',
        'policy_number',
        'patient_id',
        'valid_until',
        'created_by'
    ];

    /**
     * Relationship with the Patient model.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    // Automatically generate insurance_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($insuaranceCodeNumber) {
            if (empty($insuaranceCodeNumber->insurance_code)) {
                $insuaranceCodeNumber->insurance_code = self::generateInsuaranceCodeNumber();
            }
        });
    }

    // Generate random unique insurance_code
    private static function generateInsuaranceCodeNumber()
    {
        do {
            $insuaranceCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('insurance_code', $insuaranceCodeNumber)->exists());

        return $insuaranceCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}