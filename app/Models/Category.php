<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'category_id';

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
        'category_name',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Automatically generate category_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoryCodeNumber) {
            if (empty($categoryCodeNumber->category_code)) {
                $categoryCodeNumber->category_code = self::generateCategoryCodeNumber();
            }
        });
    }

    // Generate random unique category_code
    private static function generateCategoryCodeNumber()
    {
        do {
            $categoryCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('category_code', $categoryCodeNumber)->exists());

        return $categoryCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}