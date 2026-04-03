<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingSetting extends Model
{
    protected $fillable = [
        'monthly_price_cents',
        'annual_price_cents',
        'updated_by',
    ];

    /**
     * Always returns the single settings row, creating it with defaults if absent.
     */
    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'monthly_price_cents' => 99900,
                'annual_price_cents'  => 999900,
            ]
        );
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Annual savings vs paying monthly × 12, in centavos. */
    public function annualSavingsCents(): int
    {
        return ($this->monthly_price_cents * 12) - $this->annual_price_cents;
    }

    /** Price in pesos (float). */
    public function monthlyPesos(): float
    {
        return $this->monthly_price_cents / 100;
    }

    public function annualPesos(): float
    {
        return $this->annual_price_cents / 100;
    }
}
