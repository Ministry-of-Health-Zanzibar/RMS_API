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
        'bill_id',
        'amount_paid',
        'payment_method',
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'bill_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }

}
