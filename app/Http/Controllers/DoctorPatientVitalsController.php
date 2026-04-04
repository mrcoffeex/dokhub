<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientVital;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DoctorPatientVitalsController extends Controller
{
    private function getDoctor(Request $request): Doctor
    {
        return Doctor::where('user_id', $request->user()->id)->firstOrFail();
    }

    public function store(Request $request, Patient $patient): RedirectResponse
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'recorded_at'              => ['required', 'date'],
            'blood_pressure_systolic'  => ['nullable', 'integer', 'between:40,300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:20,200'],
            'heart_rate'               => ['nullable', 'integer', 'between:20,300'],
            'temperature'              => ['nullable', 'numeric', 'between:30,45'],
            'weight'                   => ['nullable', 'numeric', 'between:1,500'],
            'height'                   => ['nullable', 'numeric', 'between:30,300'],
            'oxygen_saturation'        => ['nullable', 'numeric', 'between:50,100'],
            'notes'                    => ['nullable', 'string', 'max:1000'],
        ]);

        $patient->vitals()->create($validated);

        return back()->with('status', 'Vitals recorded successfully.');
    }

    public function update(Request $request, Patient $patient, PatientVital $vital): RedirectResponse
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);
        abort_unless($vital->patient_id === $patient->id, 403);

        $validated = $request->validate([
            'recorded_at'              => ['required', 'date'],
            'blood_pressure_systolic'  => ['nullable', 'integer', 'between:40,300'],
            'blood_pressure_diastolic' => ['nullable', 'integer', 'between:20,200'],
            'heart_rate'               => ['nullable', 'integer', 'between:20,300'],
            'temperature'              => ['nullable', 'numeric', 'between:30,45'],
            'weight'                   => ['nullable', 'numeric', 'between:1,500'],
            'height'                   => ['nullable', 'numeric', 'between:30,300'],
            'oxygen_saturation'        => ['nullable', 'numeric', 'between:50,100'],
            'notes'                    => ['nullable', 'string', 'max:1000'],
        ]);

        $vital->update($validated);

        return back()->with('status', 'Vitals updated successfully.');
    }

    public function destroy(Request $request, Patient $patient, PatientVital $vital): RedirectResponse
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);
        abort_unless($vital->patient_id === $patient->id, 403);

        $vital->delete();

        return back()->with('status', 'Vitals record deleted.');
    }
}
