<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = [
        'doctor_id',
        'checkout_session_id',
        'billing_period',
        'amount',
        'status',
        'source',
        'pro_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'pro_expires_at' => 'datetime',
            'amount'         => 'integer',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /** Amount in pesos (display helper). */
    public function getAmountInPesosAttribute(): float
    {
        return $this->amount / 100;
    }
}
