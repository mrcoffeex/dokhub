<?php

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

describe('successful booking', function () {
    it('creates an appointment for an approved doctor', function () {
        $doctor = Doctor::factory()->approved()->create();

        $payload = validBookingPayload();

        $this->post("/doctors/{$doctor->slug}/book", $payload)
            ->assertOk();

        $this->assertDatabaseHas('appointments', [
            'doctor_id'        => $doctor->id,
            'patient_email'    => $payload['patient_email'],
            'appointment_type' => 'in_person',
            'status'           => 'pending',
        ]);
    });

    it('stores the correct appointment_type', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload(['appointment_type' => 'online']))
            ->assertOk();

        $this->assertDatabaseHas('appointments', [
            'doctor_id'        => $doctor->id,
            'appointment_type' => 'online',
        ]);
    });

    it('assigns a reference number in YEAR-XXXX-XXXX format', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload());

        $appointment = Appointment::where('doctor_id', $doctor->id)->first();
        expect($appointment->reference)->toMatch('/^\d{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
    });
});

describe('booking guards', function () {
    it('returns 404 for a pending doctor', function () {
        $doctor = Doctor::factory()->pending()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload())
            ->assertNotFound();
    });

    it('returns 404 for a suspended doctor', function () {
        $doctor = Doctor::factory()->suspended()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload())
            ->assertNotFound();
    });

    it('rejects if same email already has 5 bookings today', function () {
        $doctor = Doctor::factory()->approved()->create();
        $email  = 'heavy@example.com';

        Appointment::factory()->count(5)->create([
            'doctor_id'     => $doctor->id,
            'patient_email' => $email,
            'created_at'    => now(),
        ]);

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload(['patient_email' => $email]))
            ->assertSessionHasErrors('patient_email');
    });

    it('rejects booking if patient already has appointment with same doctor on same date', function () {
        $doctor = Doctor::factory()->approved()->create();
        $date   = now()->addDays(3)->format('Y-m-d');
        $email  = 'double@example.com';

        Appointment::factory()->create([
            'doctor_id'        => $doctor->id,
            'patient_email'    => $email,
            'appointment_date' => $date,
            'status'           => 'pending',
        ]);

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload([
            'patient_email'    => $email,
            'appointment_date' => $date,
        ]))->assertSessionHasErrors('appointment_date');
    });

    it('rejects booking if time slot is already taken', function () {
        $doctor = Doctor::factory()->approved()->create();
        $date   = now()->addDays(3)->format('Y-m-d');

        Appointment::factory()->create([
            'doctor_id'        => $doctor->id,
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'status'           => 'confirmed',
        ]);

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload([
            'appointment_date' => $date,
            'appointment_time' => '10:00',
        ]))->assertSessionHasErrors('appointment_time');
    });

    it('rejects a past appointment date', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload([
            'appointment_date' => now()->subDay()->format('Y-m-d'),
        ]))->assertSessionHasErrors('appointment_date');
    });

    it('rejects an invalid appointment_type', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload([
            'appointment_type' => 'carrier_pigeon',
        ]))->assertSessionHasErrors('appointment_type');
    });

    it('requires patient_name', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload(['patient_name' => '']))
            ->assertSessionHasErrors('patient_name');
    });

    it('requires a valid email', function () {
        $doctor = Doctor::factory()->approved()->create();

        $this->post("/doctors/{$doctor->slug}/book", validBookingPayload(['patient_email' => 'not-an-email']))
            ->assertSessionHasErrors('patient_email');
    });
});
