<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Source extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sources';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'source_id';

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
        'source_name',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Automatically generate source_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sourceCodeNumber) {
            if (empty($sourceCodeNumber->source_code)) {
                $sourceCodeNumber->source_code = self::generateSourceCodeNumber();
            }
        });
    }

    // Generate random unique source_code
    private static function generateSourceCodeNumber()
    {
        do {
            $sourceCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('source_code', $sourceCodeNumber)->exists());

        return $sourceCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}