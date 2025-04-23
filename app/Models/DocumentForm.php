<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentForm extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_forms';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'document_form_id';

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
        'payee_name',
        'amount',
        'tin_number',
        'year',
        'document_file',
        'source_id',
        'source_type_id',
        'category_id',
        'document_type_id',
        'created_by'
    ];

    protected $dates = [
        'deleted_at',
    ];

    // Automatically generate document_form_code before creating
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($documentFormCodeNumber) {
            if (empty($documentFormCodeNumber->document_form_code)) {
                $documentFormCodeNumber->document_form_code = self::generateDocumentFormCodeNumber();
            }
        });
    }

    // Generate random unique document_form_code
    private static function generateDocumentFormCodeNumber()
    {
        do {
            $documentFormCodeNumber = strtoupper(Str::random(10)); // Generates a 10-character random string
        } while (self::where('document_form_code', $documentFormCodeNumber)->exists());

        return $documentFormCodeNumber;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}