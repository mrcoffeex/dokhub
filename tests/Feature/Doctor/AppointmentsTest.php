<?php

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
});

describe('index', function () {
    it('returns the appointments page for an authenticated doctor', function () {
        $doctor = actingAsDoctor();

        Appointment::factory()->count(3)->create(['doctor_id' => $doctor->id]);

        $this->get('/doctor/appointments')
            ->assertOk()
            ->assertInertia(fn($page) => $page->component('Doctor/Appointments'));
    });

    it('redirects unauthenticated users', function () {
        $this->get('/doctor/appointments')->assertRedirect('/login');
    });

    it('only returns the authenticated doctors own appointments', function () {
        $doctor = actingAsDoctor();
        $other  = Doctor::factory()->approved()->create();

        Appointment::factory()->count(2)->create(['doctor_id' => $doctor->id]);
        Appointment::factory()->count(5)->create(['doctor_id' => $other->id]);

        $this->get('/doctor/appointments')
            ->assertOk()
            ->assertInertia(fn($page) => $page
                ->has('appointments.data', 2)
            );
    });
});

describe('updateStatus – confirm', function () {
    it('confirms an appointment and sets confirmed_at', function () {
        $doctor = actingAsDoctor();
        $appt   = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'pending']);

        $this->patch("/doctor/appointments/{$appt->id}/status", ['status' => 'confirmed'])
            ->assertRedirect();

        $appt->refresh();
        expect($appt->status)->toBe('confirmed');
        expect($appt->confirmed_at)->not->toBeNull();
    });

    it('cannot confirm another doctors appointment', function () {
        actingAsDoctor();
        $other = Doctor::factory()->approved()->create();
        $appt  = Appointment::factory()->create(['doctor_id' => $other->id]);

        $this->patch("/doctor/appointments/{$appt->id}/status", ['status' => 'confirmed'])
            ->assertForbidden();
    });
});

describe('updateStatus – cancel', function () {
    it('cancels an appointment with a reason', function () {
        $doctor = actingAsDoctor();
        $appt   = Appointment::factory()->create(['doctor_id' => $doctor->id, 'status' => 'confirmed']);

        $this->patch("/doctor/appointments/{$appt->id}/status", [
            'status'              => 'cancelled',
            'cancellation_reason' => 'Doctor unavailable',
        ])->assertRedirect();

        $appt->refresh();
        expect($appt->status)->toBe('cancelled');
        expect($appt->cancellation_reason)->toBe('Doctor unavailable');
    });

    it('requires cancellation_reason when cancelling', function () {
        $doctor = actingAsDoctor();
        $appt   = Appointment::factory()->create(['doctor_id' => $doctor->id]);

        $this->patch("/doctor/appointments/{$appt->id}/status", [
            'status' => 'cancelled',
        ])->assertSessionHasErrors('cancellation_reason');
    });
});

describe('updateStatus – complete', function () {
    it('marks an appointment as completed', function () {
        $doctor = actingAsDoctor();
        $appt   = Appointment::factory()->confirmed()->create(['doctor_id' => $doctor->id]);

        $this->patch("/doctor/appointments/{$appt->id}/status", ['status' => 'completed'])
            ->assertRedirect();

        expect($appt->fresh()->status)->toBe('completed');
    });
});

describe('updateStatus – validation', function () {
    it('rejects an invalid status value', function () {
        $doctor = actingAsDoctor();
        $appt   = Appointment::factory()->create(['doctor_id' => $doctor->id]);

        $this->patch("/doctor/appointments/{$appt->id}/status", ['status' => 'magic'])
            ->assertSessionHasErrors('status');
    });
});
