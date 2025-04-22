<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SourceType extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'source_types';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'source_type_id';

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
        'source_type_name',
        'source_id',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Automatically generate source_type_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sourceTypeCodeNumber) {
            if (empty($sourceTypeCodeNumber->source_type_code)) {
                $sourceTypeCodeNumber->source_type_code = self::generateSourceTypeCodeNumber();
            }
        });
    }

    // Generate random unique source_type_code
    private static function generateSourceTypeCodeNumber()
    {
        do {
            $sourceTypeCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('source_type_code', $sourceTypeCodeNumber)->exists());

        return $sourceTypeCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}