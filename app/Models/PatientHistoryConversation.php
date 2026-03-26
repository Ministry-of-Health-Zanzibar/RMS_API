<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PatientHistoryConversation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'patient_history_conversations';
    protected $primaryKey = 'conversation_id';
    public $incrementing = true;
    protected $keyType = 'integer';

    protected $fillable = [
        'patient_history_id',
        'sender_id',
        'receiver_id',
        'parent_id',
        'message',
        'case_status_at_time',
        'attachment_file',
    ];

    /**
     * Belongs to PatientHistory
     */
    public function patientHistory()
    {
        return $this->belongsTo(PatientHistory::class, 'patient_history_id', 'patient_histories_id');
    }

    /**
     * Belongs to sender User
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    /**
     * Belongs to receiver User (nullable)
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    /**
     * Self-referential: parent message
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'conversation_id');
    }

    /**
     * Self-referential: child replies
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id', 'conversation_id');
    }

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

