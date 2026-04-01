<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'doctor_id',
        'patient_name',
        'patient_email',
        'patient_phone',
        'appointment_date',
        'appointment_time',
        'reason',
        'notes',
        'status',
        'cancellation_reason',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date:Y-m-d',
            'confirmed_at'     => 'datetime',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public static function generateReference(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $year = now()->year;

        do {
            $part1 = substr(str_shuffle(str_repeat($chars, 4)), 0, 4);
            $part2 = substr(str_shuffle(str_repeat($chars, 4)), 0, 4);
            $reference = $year . '-' . $part1 . '-' . $part2;
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}
