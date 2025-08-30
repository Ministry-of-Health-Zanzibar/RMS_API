<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PatientList extends Model
{
    use LogsActivity, HasFactory, SoftDeletes;

    protected $table = 'patient_lists';
    protected $primaryKey = 'patient_list_id';
    public $incrementing = true;      // since it's bigIncrements
    protected $keyType = 'int';       // integer PK

    protected $fillable = [
        'patient_list_title',
        'patient_list_file',
        'created_by',
    ];

    /**
     * Relationship: PatientList belongs to a User (creator).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patients()
    {
        return $this->hasMany(Patient::class, 'patient_list_id', 'patient_list_id');
    }

    /**
     * Configure Spatie Activity Log
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['*']);
    }
}
