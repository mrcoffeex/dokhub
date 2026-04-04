<?php

use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;

describe('create prescription', function () {
    it('creates a prescription for own patient', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->post("/doctor/patients/{$patient->id}/prescriptions", [
            'medications' => [
                [
                    'name'         => 'Amoxicillin',
                    'dosage'       => '500mg',
                    'frequency'    => 'Three times daily',
                    'duration'     => '7 days',
                    'instructions' => 'Take with food',
                ],
            ],
            'notes' => 'Follow up in a week.',
        ])->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
        ]);
    });

    it('requires at least one medication', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->post("/doctor/patients/{$patient->id}/prescriptions", [
            'medications' => [],
        ])->assertSessionHasErrors('medications');
    });

    it('requires medication name, dosage, frequency and duration', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->post("/doctor/patients/{$patient->id}/prescriptions", [
            'medications' => [['name' => '', 'dosage' => '', 'frequency' => '', 'duration' => '']],
        ])->assertSessionHasErrors([
            'medications.0.name',
            'medications.0.dosage',
            'medications.0.frequency',
            'medications.0.duration',
        ]);
    });

    it('returns 403 for another doctors patient', function () {
        actingAsProDoctor();
        $other   = Doctor::factory()->approved()->create();
        $patient = Patient::factory()->create(['doctor_id' => $other->id]);

        $this->post("/doctor/patients/{$patient->id}/prescriptions", [
            'medications' => [
                ['name' => 'Test', 'dosage' => '10mg', 'frequency' => 'Once', 'duration' => '1 day'],
            ],
        ])->assertForbidden();
    });

    it('assigns a reference in RX-YEAR-XXXX format', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->post("/doctor/patients/{$patient->id}/prescriptions", [
            'medications' => [
                ['name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'As needed', 'duration' => '3 days'],
            ],
        ]);

        $prescription = Prescription::where('doctor_id', $doctor->id)->first();
        expect($prescription->reference)->toMatch('/^RX-\d{4}-\d{4}$/');
    });
});

describe('show prescription', function () {
    it('shows a prescription belonging to own doctor', function () {
        $doctor       = actingAsProDoctor();
        $patient      = Patient::factory()->create(['doctor_id' => $doctor->id]);
        $prescription = Prescription::factory()->create([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $this->get("/doctor/prescriptions/{$prescription->id}")
            ->assertOk()
            ->assertInertia(fn($page) => $page->component('Doctor/Prescriptions/Show'));
    });

    it('returns 403 for another doctors prescription', function () {
        actingAsProDoctor();
        $other        = Doctor::factory()->approved()->create();
        $patient      = Patient::factory()->create(['doctor_id' => $other->id]);
        $prescription = Prescription::factory()->create([
            'doctor_id'  => $other->id,
            'patient_id' => $patient->id,
        ]);

        $this->get("/doctor/prescriptions/{$prescription->id}")->assertForbidden();
    });
});
