<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Payment extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $table = 'payments';

    protected $primaryKey = 'payment_id';

    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'monthly_bill_id',
        'payer',
        'amount_paid',
        'currency',
        'payment_method',
        'reference_number',
        'voucher_number',
        'payment_date',
        'created_by',
    ];

    protected $dates = ['deleted_at'];

    public function bills()
    {
        return $this->belongsToMany(
            Bill::class,        // Related model
            'bill_payments',    // Pivot table
            'payment_id',       // Foreign key on pivot table for this model
            'bill_id'           // Foreign key on pivot table for related model
        )->withPivot('allocated_amount', 'allocation_date', 'status');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

}
