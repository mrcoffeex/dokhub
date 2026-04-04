<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;

describe('patients index', function () {
    it('lists patients belonging to the authenticated doctor', function () {
        $doctor = actingAsProDoctor();

        Patient::factory()->count(4)->create(['doctor_id' => $doctor->id]);

        $this->get('/doctor/patients')
            ->assertOk()
            ->assertInertia(fn($page) => $page
                ->component('Doctor/Patients/Index')
                ->has('patients.data', 4)
            );
    });

    it('does not show patients from other doctors', function () {
        $doctor = actingAsProDoctor();
        $other  = Doctor::factory()->approved()->create();

        Patient::factory()->count(3)->create(['doctor_id' => $other->id]);

        $this->get('/doctor/patients')
            ->assertOk()
            ->assertInertia(fn($page) => $page->has('patients.data', 0));
    });

    it('filters by search term', function () {
        $doctor = actingAsProDoctor();

        Patient::factory()->create(['doctor_id' => $doctor->id, 'name' => 'Maria Santos']);
        Patient::factory()->create(['doctor_id' => $doctor->id, 'name' => 'John Dela Cruz']);

        $this->get('/doctor/patients?search=maria')
            ->assertOk()
            ->assertInertia(fn($page) => $page->has('patients.data', 1));
    });

    it('redirects unauthenticated users', function () {
        $this->get('/doctor/patients')->assertRedirect('/login');
    });
});

describe('patient show', function () {
    it('shows a patient belonging to the authenticated doctor', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->get("/doctor/patients/{$patient->id}")
            ->assertOk()
            ->assertInertia(fn($page) => $page->component('Doctor/Patients/Show'));
    });

    it('returns 403 for a patient belonging to another doctor', function () {
        actingAsProDoctor();
        $other   = Doctor::factory()->approved()->create();
        $patient = Patient::factory()->create(['doctor_id' => $other->id]);

        $this->get("/doctor/patients/{$patient->id}")->assertForbidden();
    });
});

describe('patient update', function () {
    it('updates patient information', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->patch("/doctor/patients/{$patient->id}", [
            'name'  => 'Updated Name',
            'email' => $patient->email,
            'phone' => '+639001234567',
            'gender'=> $patient->gender,
            'date_of_birth' => $patient->date_of_birth->format('Y-m-d'),
        ])->assertRedirect();

        $this->assertDatabaseHas('patients', [
            'id'   => $patient->id,
            'name' => 'Updated Name',
        ]);
    });

    it('returns 403 when updating another doctors patient', function () {
        actingAsProDoctor();
        $other   = Doctor::factory()->approved()->create();
        $patient = Patient::factory()->create(['doctor_id' => $other->id]);

        $this->patch("/doctor/patients/{$patient->id}", [
            'name'  => 'Hacked Name',
            'email' => $patient->email,
        ])->assertForbidden();
    });
});
