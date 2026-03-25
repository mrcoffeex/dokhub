<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPatientsController extends Controller
{
    private function getDoctor(Request $request): Doctor
    {
        return Doctor::where('user_id', $request->user()->id)->firstOrFail();
    }

    public function index(Request $request): Response
    {
        $doctor = $this->getDoctor($request);

        $query = Patient::where('doctor_id', $doctor->id)
            ->withCount(['diagnoses', 'prescriptions']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$term}%")
                ->orWhere('email', 'ilike', "%{$term}%")
                ->orWhere('phone', 'ilike', "%{$term}%")
            );
        }

        $patients = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Doctor/Patients/Index', [
            'patients' => $patients,
            'filters'  => $request->only(['search']),
        ]);
    }

    public function show(Request $request, Patient $patient): Response
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $patient->load([
            'diagnoses' => fn($q) => $q->with('appointment:id,reference,appointment_date')->latest(),
            'prescriptions' => fn($q) => $q->with('diagnosis:id,title')->latest(),
        ]);

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('patient_email', $patient->email)
            ->orderByDesc('appointment_date')
            ->get();

        return Inertia::render('Doctor/Patients/Show', [
            'patient'      => $patient->append('age'),
            'appointments' => $appointments,
            'doctor'       => $doctor->append('avatar_url'),
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $doctor = $this->getDoctor($request);
        abort_unless($patient->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'gender'          => ['nullable', 'in:male,female,other'],
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
            'allergies'       => ['nullable', 'string', 'max:1000'],
            'medical_history' => ['nullable', 'string', 'max:2000'],
            'notes'           => ['nullable', 'string', 'max:2000'],
            'phone'           => ['nullable', 'string', 'max:30'],
        ]);

        $patient->update($validated);

        return back()->with('success', 'Patient information updated.');
    }
}
