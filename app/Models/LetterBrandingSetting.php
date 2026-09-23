<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterBrandingSetting extends Model
{
    protected $table = 'letter_branding_settings';

    protected $fillable = [
        'signature_path',
        'stamp_path',
        'updated_by',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
