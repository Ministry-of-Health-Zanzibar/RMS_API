<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Diagnosis extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'diagnosis_id';

    protected $fillable = [
        'uuid',
        'diagnosis_name',
        'diagnosis_code',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically generate a UUID if not provided
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function patientHistories()
    {
        return $this->belongsToMany(
            PatientHistory::class,
            'history_diagnosis',          // pivot table
            'diagnosis_id',               // foreign key on pivot (this model)
            'patient_histories_id'        // related key on pivot (other model)
        );
    }

}
