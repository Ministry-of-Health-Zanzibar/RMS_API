<?php

namespace App\Models;

use App\Models\Patient;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MonthlyBill extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monthly_bills';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'monthly_bill_id';

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
        'current_monthly_bill_amount',
        'after_audit_monthly_bill_amount',
        'hospital_id',
        'bill_date',
        'bill_file',
        'created_by'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}