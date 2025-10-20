<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bill extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bills';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'bill_id';

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
        'total_amount',
        'bill_period_start',
        'bill_period_end',
        'bill_file_id',
        'bill_status',
        'created_by',
    ];

    /**
     * Relationship with the Referral model.
     */
        /** Relationships */
    public function referral()
    {
        return $this->belongsTo(Referral::class, 'referral_id', 'referral_id');
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function payments()
    {
        return $this->belongsToMany(
            Payment::class,     // Related model
            'bill_payments',    // Pivot table
            'bill_id',          // Foreign key on pivot table for this model
            'payment_id'        // Foreign key on pivot table for related model
        )->withPivot('allocated_amount', 'allocation_date', 'status');
    }


    public function billFile()
    {
        return $this->belongsTo(BillFile::class, 'bill_file_id', 'bill_file_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}