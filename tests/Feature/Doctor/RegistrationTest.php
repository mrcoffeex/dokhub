<?php

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function registrationPayload(array $overrides = []): array
{
    Storage::fake('public');
    $doc = UploadedFile::fake()->image('id.jpg');

    return array_merge([
        'name'             => 'Dr. Test Doctor',
        'email'            => 'drtest@example.com',
        'phone'            => '+639171234567',
        'password'         => 'Password123!',
        'password_confirmation' => 'Password123!',
        'qualification'    => 'MD',
        'bio'              => 'Experienced physician with 10 years of practice.',
        'experience_years' => 10,
        'consultation_fee' => 500,
        'location'         => 'Makati City',
        'specialization'   => ['General Practice'],
        'languages'        => 'English',
        'insurance'        => [],
        'id_documents'     => [$doc],
        'hcaptcha_token'   => null,
    ], $overrides);
}

describe('doctor registration', function () {
    it('registers a new doctor with pending status', function () {
        $payload = registrationPayload();

        $this->post('/auth/signup/doctor', $payload)
            ->assertOk()
            ->assertJson(['message' => 'Doctor application submitted successfully']);

        $this->assertDatabaseHas('doctors', [
            'email'  => 'drtest@example.com',
            'status' => 'pending',
        ]);
    });

    it('creates a linked user account', function () {
        $payload = registrationPayload();

        $this->post('/auth/signup/doctor', $payload);

        $this->assertDatabaseHas('users', [
            'email' => 'drtest@example.com',
            'role'  => 'doctor',
        ]);
    });

    it('rejects duplicate email', function () {
        User::factory()->create(['email' => 'drtest@example.com']);

        $this->post('/auth/signup/doctor', registrationPayload())
            ->assertStatus(422);
    });

    it('requires name, email, and qualification', function () {
        $payload = registrationPayload([
            'name'          => '',
            'email'         => '',
            'qualification' => '',
        ]);

        $response = $this->post('/auth/signup/doctor', $payload);
        $response->assertStatus(422);
    });

    it('requires at least one specialization', function () {
        $payload = registrationPayload(['specialization' => []]);

        $this->post('/auth/signup/doctor', $payload)->assertStatus(422);
    });

    it('requires at least one id document', function () {
        $payload = registrationPayload(['id_documents' => []]);

        $this->post('/auth/signup/doctor', $payload)->assertStatus(422);
    });

    it('requires password confirmation to match', function () {
        $payload = registrationPayload(['password_confirmation' => 'WrongPassword!']);

        $this->post('/auth/signup/doctor', $payload)->assertStatus(422);
    });
});
