<?php

namespace App\Http\Controllers;

use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;

class DoctorDiagnosisController extends Controller
{
    private function getDoctor(Request $request): Doctor
    {
        return Doctor::where('user_id', $request->user()->id)->firstOrFail();
    }

    public function store(Request $request, Patient $patient)
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'symptoms'       => ['nullable', 'string', 'max:2000'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'treatment'      => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date', 'after:today'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
        ]);

        Diagnosis::create([
            ...$validated,
            'doctor_id'  => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        return back()->with('success', 'Diagnosis added successfully.');
    }

    public function update(Request $request, Patient $patient, Diagnosis $diagnosis)
    {
        $doctor = $this->getDoctor($request);
        abort_unless(
            $patient->doctor_id === $doctor->id && $diagnosis->patient_id === $patient->id,
            403
        );

        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:200'],
            'symptoms'       => ['nullable', 'string', 'max:2000'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'treatment'      => ['nullable', 'string', 'max:2000'],
            'follow_up_date' => ['nullable', 'date'],
        ]);

        $diagnosis->update($validated);

        return back()->with('success', 'Diagnosis updated.');
    }

    public function destroy(Request $request, Patient $patient, Diagnosis $diagnosis)
    {
        $doctor = $this->getDoctor($request);
        abort_unless(
            $patient->doctor_id === $doctor->id && $diagnosis->patient_id === $patient->id,
            403
        );

        $diagnosis->delete();

        return back()->with('success', 'Diagnosis deleted.');
    }
}
