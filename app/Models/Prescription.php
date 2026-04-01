<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference',
        'doctor_id',
        'patient_id',
        'diagnosis_id',
        'medications',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'medications' => 'array',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public static function generateReference(): string
    {
        $year = now()->year;
        $count = static::whereYear('created_at', $year)->count() + 1;
        return 'RX-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
