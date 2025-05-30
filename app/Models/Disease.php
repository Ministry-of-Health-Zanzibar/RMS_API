<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Disease extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'diseases';


    protected $primaryKey = 'disease_id';   // custom PK
    public $incrementing = true;            // still auto-incrementing

    protected $fillable = [
        'disease_name',
        'disease_code',
        'created_by',
    ];

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}