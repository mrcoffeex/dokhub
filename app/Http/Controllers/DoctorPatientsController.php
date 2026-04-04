<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'vitals' => fn($q) => $q->orderByDesc('recorded_at'),
            'records' => fn($q) => $q->latest(),
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

        $oldEmail = $patient->email;
        $oldName  = $patient->name;

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients')
                    ->where(fn($q) => $q->where('doctor_id', $doctor->id))
                    ->ignore($patient->id),
            ],
            'gender'          => ['nullable', 'in:male,female,other'],
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
            'allergies'       => ['nullable', 'string', 'max:1000'],
            'medical_history' => ['nullable', 'string', 'max:2000'],
            'notes'           => ['nullable', 'string', 'max:2000'],
            'phone'           => ['nullable', 'string', 'max:30'],
        ]);

        $patient->update($validated);

        // If the patient's email or name changed, keep appointments in sync
        $newEmail = $patient->email;
        $newName  = $patient->name;

        if ($oldEmail && $newEmail && $newEmail !== $oldEmail) {
            Appointment::where('doctor_id', $doctor->id)
                ->where('patient_email', $oldEmail)
                ->update(['patient_email' => $newEmail, 'patient_name' => $newName]);
        } elseif ($oldEmail && $newName !== $oldName) {
            Appointment::where('doctor_id', $doctor->id)
                ->where('patient_email', $oldEmail)
                ->update(['patient_name' => $newName]);
        } elseif (!$oldEmail && $oldName && $newName !== $oldName) {
            // Fallback: update appointments by matching the name if there was no email
            Appointment::where('doctor_id', $doctor->id)
                ->where('patient_name', $oldName)
                ->update(['patient_name' => $newName]);
        }

        return back()->with('success', 'Patient information updated.');
    }

    public function create(): Response
    {
        return Inertia::render('Doctor/Patients/Create');
    }

    public function store(Request $request)
    {
        $doctor = $this->getDoctor($request);

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:30'],
            'email'           => ['nullable', 'email', 'max:255'],
            'gender'          => ['nullable', 'in:male,female,other'],
            'date_of_birth'   => ['nullable', 'date', 'before:today'],
            'allergies'       => ['nullable', 'string', 'max:1000'],
            'medical_history' => ['nullable', 'string', 'max:2000'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        // Email is unique per doctor — generate a placeholder if not provided
        $email = $validated['email']
            ?? 'walkin.' . now()->timestamp . '@practice.local';

        // Prevent duplicate registration for the same doctor
        if (Patient::where('doctor_id', $doctor->id)->where('email', $validated['email'] ?? '')->exists()) {
            return back()->withErrors(['email' => 'A patient with this email is already registered.']);
        }

        $patient = Patient::create([
            'doctor_id'       => $doctor->id,
            'name'            => $validated['name'],
            'email'           => $email,
            'phone'           => $validated['phone'] ?? null,
            'gender'          => $validated['gender'] ?? null,
            'date_of_birth'   => $validated['date_of_birth'] ?? null,
            'allergies'       => $validated['allergies'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
            'notes'           => $validated['notes'] ?? null,
            'first_seen_at'   => now(),
        ]);

        return redirect()->route('doctor.patients.show', $patient->id)
            ->with('success', 'Walk-in patient registered successfully.');
    }
}
