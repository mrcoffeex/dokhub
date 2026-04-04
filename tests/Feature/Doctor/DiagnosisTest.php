<?php

use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Patient;

describe('create diagnosis', function () {
    it('creates a diagnosis for own patient', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->post("/doctor/patients/{$patient->id}/diagnoses", [
            'title'       => 'Hypertension',
            'symptoms'    => 'Headache, dizziness',
            'description' => 'Stage 1 hypertension',
            'treatment'   => 'Lifestyle changes and medication',
        ])->assertRedirect();

        $this->assertDatabaseHas('diagnoses', [
            'patient_id' => $patient->id,
            'doctor_id'  => $doctor->id,
            'title'      => 'Hypertension',
        ]);
    });

    it('requires a title', function () {
        $doctor  = actingAsProDoctor();
        $patient = Patient::factory()->create(['doctor_id' => $doctor->id]);

        $this->post("/doctor/patients/{$patient->id}/diagnoses", [
            'title' => '',
        ])->assertSessionHasErrors('title');
    });

    it('returns 403 for another doctors patient', function () {
        actingAsProDoctor();
        $other   = Doctor::factory()->approved()->create();
        $patient = Patient::factory()->create(['doctor_id' => $other->id]);

        $this->post("/doctor/patients/{$patient->id}/diagnoses", [
            'title' => 'Unauthorized',
        ])->assertForbidden();
    });
});

describe('update diagnosis', function () {
    it('updates a diagnosis for own patient', function () {
        $doctor    = actingAsProDoctor();
        $patient   = Patient::factory()->create(['doctor_id' => $doctor->id]);
        $diagnosis = Diagnosis::factory()->create([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $this->put("/doctor/patients/{$patient->id}/diagnoses/{$diagnosis->id}", [
            'title'   => 'Updated Title',
            'symptoms'=> 'Updated symptoms',
        ])->assertRedirect();

        $this->assertDatabaseHas('diagnoses', [
            'id'    => $diagnosis->id,
            'title' => 'Updated Title',
        ]);
    });

    it('returns 403 when updating another doctors diagnosis', function () {
        actingAsProDoctor();
        $other     = Doctor::factory()->approved()->create();
        $patient   = Patient::factory()->create(['doctor_id' => $other->id]);
        $diagnosis = Diagnosis::factory()->create([
            'doctor_id'  => $other->id,
            'patient_id' => $patient->id,
        ]);

        $this->put("/doctor/patients/{$patient->id}/diagnoses/{$diagnosis->id}", [
            'title' => 'Hacked Title',
        ])->assertForbidden();
    });
});

describe('delete diagnosis', function () {
    it('deletes a diagnosis for own patient', function () {
        $doctor    = actingAsProDoctor();
        $patient   = Patient::factory()->create(['doctor_id' => $doctor->id]);
        $diagnosis = Diagnosis::factory()->create([
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $this->delete("/doctor/patients/{$patient->id}/diagnoses/{$diagnosis->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('diagnoses', ['id' => $diagnosis->id]);
    });

    it('returns 403 when deleting another doctors diagnosis', function () {
        actingAsProDoctor();
        $other     = Doctor::factory()->approved()->create();
        $patient   = Patient::factory()->create(['doctor_id' => $other->id]);
        $diagnosis = Diagnosis::factory()->create([
            'doctor_id'  => $other->id,
            'patient_id' => $patient->id,
        ]);

        $this->delete("/doctor/patients/{$patient->id}/diagnoses/{$diagnosis->id}")
            ->assertForbidden();
    });
});
