<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReferralFlight extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'referral_flights';

    protected $primaryKey = 'referral_flight_id';

    protected $fillable = [
        'referral_id',
        'arrival_date',
        'arrival_time',
        'arrival_airport',
        'airline',
        'flight_number',
        'departure_city',
        'departure_airport',
        'arrival_city',
        'terminal',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'arrival_time' => 'datetime:H:i',
    ];

    public function referral()
    {
        return $this->belongsTo(
            Referral::class,
            'referral_id',
            'referral_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}