<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentType extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_types';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'document_type_id';

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
        'document_type_name',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Automatically generate document_type_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documentTypeCodeNumber) {
            if (empty($documentTypeCodeNumber->document_type_code)) {
                $documentTypeCodeNumber->document_type_code = self::generateDocumentTypeCodeNumber();
            }
        });
    }

    // Generate random unique document_type_code
    private static function generateDocumentTypeCodeNumber()
    {
        do {
            $documentTypeCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('document_type_code', $documentTypeCodeNumber)->exists());

        return $documentTypeCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}