<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSubUser extends Model
{
    protected $fillable = [
        'doctor_id',
        'user_id',
        'role',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Permissions granted to each role. */
    public const PERMISSIONS = [
        'secretary' => [
            'appointments.view',
            'appointments.manage',   // confirm / complete / cancel
            'patients.view',
            'patients.create',
            'patients.edit',
            'inventory.view',
        ],
        'nurse' => [
            'appointments.view',
            'patients.view',
            'vitals.manage',
            'diagnoses.view',
            'prescriptions.view',
            'records.manage',
            'inventory.view',
            'inventory.movements',
        ],
    ];

    public function can(string $permission): bool
    {
        return in_array($permission, static::PERMISSIONS[$this->role] ?? []);
    }
}
