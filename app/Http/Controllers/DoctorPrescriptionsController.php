<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPrescriptionsController extends Controller
{
    private function getDoctor(Request $request): Doctor
    {
        return Doctor::where('user_id', $request->user()->id)->firstOrFail();
    }

    public function create(Request $request, Patient $patient): Response
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $diagnoses = Diagnosis::where('patient_id', $patient->id)
            ->where('doctor_id', $doctor->id)
            ->latest()
            ->get(['id', 'title', 'created_at']);

        return Inertia::render('Doctor/Prescriptions/Create', [
            'patient'      => $patient,
            'doctor'       => $doctor->append('avatar_url'),
            'diagnoses'    => $diagnoses,
            'diagnosis_id' => $request->integer('diagnosis_id') ?: null,
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'diagnosis_id'               => ['nullable', 'exists:diagnoses,id'],
            'medications'                => ['required', 'array', 'min:1'],
            'medications.*.name'         => ['required', 'string', 'max:200'],
            'medications.*.dosage'       => ['required', 'string', 'max:100'],
            'medications.*.frequency'    => ['required', 'string', 'max:100'],
            'medications.*.duration'     => ['required', 'string', 'max:100'],
            'medications.*.instructions' => ['nullable', 'string', 'max:500'],
            'notes'                      => ['nullable', 'string', 'max:2000'],
        ]);

        $prescription = Prescription::create([
            'reference'    => Prescription::generateReference(),
            'doctor_id'    => $doctor->id,
            'patient_id'   => $patient->id,
            'diagnosis_id' => $validated['diagnosis_id'] ?? null,
            'medications'  => $validated['medications'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        return redirect()->route('doctor.prescriptions.show', $prescription->id);
    }

    public function show(Request $request, Prescription $prescription): Response
    {
        $doctor = $this->getDoctor($request);
        abort_unless($prescription->doctor_id === $doctor->id, 403);

        $prescription->load(['patient', 'diagnosis']);

        return Inertia::render('Doctor/Prescriptions/Show', [
            'prescription' => $prescription,
            'doctor'       => $doctor->append('avatar_url'),
        ]);
    }
}
