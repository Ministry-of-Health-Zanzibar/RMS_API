<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterPrintEvent extends Model
{
    protected $primaryKey = 'letter_print_event_id';

    protected $fillable = [
        'letter_type',
        'letter_id',
        'language',
        'printed_by',
        'printed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'printed_at' => 'datetime',
    ];

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }
}
