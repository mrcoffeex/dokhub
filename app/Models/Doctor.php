<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Doctor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'email',
        'phone',
        'specialization',
        'insurance',
        'qualification',
        'bio',
        'avatar',
        'experience_years',
        'consultation_fee',
        'status',
        'plan',
        'trial_ends_at',
        'pro_expires_at',
        'last_payment_id',
        'pending_checkout_session_id',
        'location',
        'latitude',
        'longitude',
        'languages',
    ];

    protected $appends = ['avatar_url'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Doctor $doctor) {
            if (empty($doctor->slug)) {
                $doctor->slug = static::generateUniqueSlug($doctor->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            static::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'specialization'   => 'array',
            'insurance'        => 'array',
            'languages'        => 'array',
            'consultation_fee' => 'decimal:2',
            'latitude'         => 'float',
            'longitude'        => 'float',
            'trial_ends_at'    => 'datetime',
            'pro_expires_at'   => 'datetime',
        ];
    }

    // ── Plan helpers ──────────────────────────────────────────────────────────

    /** True while the 15-day trial window is still open. */
    public function isInTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /** True when on an active paid Pro subscription. */
    public function isPaidPro(): bool
    {
        return $this->plan === 'pro'
            && ($this->pro_expires_at === null || $this->pro_expires_at->isFuture());
    }

    /** True when the doctor has full feature access (trial OR paid pro). */
    public function hasProAccess(): bool
    {
        return $this->isInTrial() || $this->isPaidPro();
    }

    /** Days remaining in trial (0 if expired / not in trial). */
    public function trialDaysRemaining(): int
    {
        if (!$this->isInTrial()) return 0;
        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(DoctorReview::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnosis::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=635bff&color=fff&size=128';
    }
}
