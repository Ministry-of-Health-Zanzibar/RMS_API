<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientHistoryWorkflowEvent extends Model
{
    protected $fillable = [
        'patient_histories_id',
        'action',
        'from_status',
        'to_status',
        'actor_id',
        'metadata',
        'undone_at',
        'undone_by',
        'undo_reason',
    ];

    protected $casts = [
        'metadata' => 'array',
        'undone_at' => 'datetime',
    ];

    public function patientHistory(): BelongsTo
    {
        return $this->belongsTo(PatientHistory::class, 'patient_histories_id', 'patient_histories_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function undoneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'undone_by');
    }
}
