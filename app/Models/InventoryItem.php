<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'name',
        'category',
        'sku',
        'unit',
        'description',
        'current_stock',
        'min_stock',
        'cost_price',
        'selling_price',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'integer',
            'min_stock'     => 'integer',
            'cost_price'    => 'decimal:2',
            'selling_price' => 'decimal:2',
            'expiry_date'   => 'date',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->min_stock > 0 && $this->current_stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function isExpiringSoon(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }
        return $this->expiry_date->lte(now()->addDays(30));
    }

    public function isExpired(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isPast();
    }
}
