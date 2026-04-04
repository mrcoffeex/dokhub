<?php

use App\Models\Doctor;
use App\Models\DoctorReview;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    RateLimiter::clear('review-ip:127.0.0.1');
});

function reviewPayload(array $overrides = []): array
{
    return array_merge([
        'patient_name'   => 'Jane Patient',
        'patient_email'  => 'reviewer@example.com',
        'rating'         => 5,
        'comment'        => 'Excellent doctor!',
        'hcaptcha_token' => null,
    ], $overrides);
}

describe('submit review', function () {
    it('stores an approved review for an approved doctor', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/reviews", reviewPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('doctor_reviews', [
            'doctor_id'     => $doctor->id,
            'patient_email' => 'reviewer@example.com',
            'rating'        => 5,
            'is_approved'   => true,
        ]);
    });

    it('returns 404 for a non-approved doctor', function () {
        $doctor = Doctor::factory()->pending()->create();

        $this->post("/doctors/{$doctor->slug}/reviews", reviewPayload())
            ->assertNotFound();
    });

    it('requires patient_name and email', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/reviews", reviewPayload([
            'patient_name'  => '',
            'patient_email' => '',
        ]))->assertSessionHasErrors(['patient_name', 'patient_email']);
    });

    it('requires rating between 1 and 5', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/reviews", reviewPayload(['rating' => 6]))
            ->assertSessionHasErrors('rating');

        $this->post("/doctors/{$doctor->slug}/reviews", reviewPayload(['rating' => 0]))
            ->assertSessionHasErrors('rating');
    });
});

describe('duplicate review guard', function () {
    it('blocks same email from reviewing the same doctor twice', function () {
        $doctor = Doctor::factory()->approved()->create();

        DoctorReview::factory()->create([
            'doctor_id'     => $doctor->id,
            'patient_email' => 'reviewer@example.com',
        ]);

        $this->post("/doctors/{$doctor->slug}/reviews", reviewPayload())
            ->assertSessionHasErrors('patient_email');
    });

    it('allows same email to review a different doctor', function () {
        $doctor1 = Doctor::factory()->approved()->create();
        $doctor2 = Doctor::factory()->approved()->create();

        DoctorReview::factory()->create([
            'doctor_id'     => $doctor1->id,
            'patient_email' => 'reviewer@example.com',
        ]);

        $this->post("/doctors/{$doctor2->slug}/reviews", reviewPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('doctor_reviews', [
            'doctor_id'     => $doctor2->id,
            'patient_email' => 'reviewer@example.com',
        ]);
    });
});

describe('rate limit', function () {
    it('blocks more than 1 review per day from the same IP', function () {
        $doctor1 = Doctor::factory()->approved()->create();
        $doctor2 = Doctor::factory()->approved()->create();

        // First review — should succeed
        $this->post("/doctors/{$doctor1->slug}/reviews", reviewPayload(['patient_email' => 'first@example.com']))
            ->assertRedirect();

        // Second review — different doctor, different email but same IP
        $this->post("/doctors/{$doctor2->slug}/reviews", reviewPayload(['patient_email' => 'second@example.com']))
            ->assertSessionHasErrors('rate_limit');
    });
});
