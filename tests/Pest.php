<?php

use App\Models\Doctor;
use App\Models\User;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Create an approved doctor along with its linked user and return both.
 * The user will have role=doctor.
 */
function createApprovedDoctor(array $doctorOverrides = [], array $userOverrides = []): Doctor
{
    $user = User::factory()->create(array_merge(['role' => 'doctor'], $userOverrides));

    return Doctor::factory()->approved()->create(array_merge(
        ['user_id' => $user->id],
        $doctorOverrides
    ));
}

/**
 * Create an approved doctor and authenticate as their user in the test.
 */
function actingAsDoctor(array $doctorOverrides = []): Doctor
{
    $doctor = createApprovedDoctor($doctorOverrides);
    test()->actingAs($doctor->user);
    return $doctor;
}

/**
 * Create an approved doctor with Pro trial access and authenticate as them.
 * Use for routes protected by the 'pro' middleware.
 */
function actingAsProDoctor(array $doctorOverrides = []): Doctor
{
    return actingAsDoctor(array_merge(['trial_ends_at' => now()->addDays(10)], $doctorOverrides));
}

/**
 * Build a valid appointment payload for a given doctor.
 */
function validBookingPayload(array $overrides = []): array
{
    return array_merge([
        'appointment_type' => 'in_person',
        'patient_name'     => 'Jane Doe',
        'patient_email'    => 'jane@example.com',
        'patient_phone'    => '+639171234567',
        'appointment_date' => now()->addDays(3)->format('Y-m-d'),
        'appointment_time' => '10:00',
        'reason'           => 'Routine check-up',
        'hcaptcha_token'   => null,
    ], $overrides);
}
