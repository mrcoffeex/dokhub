<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Doctor::withCount('appointments');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$term}%")
                ->orWhereRaw("specialization::text ilike ?", ["%{$term}%"])
                ->orWhere('email', 'ilike', "%{$term}%")
            );
        }

        return Inertia::render('Admin/Doctors/Index', [
            'doctors' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Doctors/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'unique:users,email', 'unique:doctors,email'],
            'phone'              => ['nullable', 'string', 'max:25'],
            'specialization'     => ['required', 'array', 'min:1'],
            'specialization.*'   => ['string', 'max:100'],
            'insurance'          => ['nullable', 'array'],
            'insurance.*'        => ['string', 'max:100'],
            'qualification'      => ['nullable', 'string', 'max:200'],
            'bio'                => ['nullable', 'string', 'max:1000'],
            'experience_years'   => ['nullable', 'integer', 'min:0', 'max:60'],
            'consultation_fee'   => ['nullable', 'numeric', 'min:0'],
            'location'           => ['nullable', 'string', 'max:100'],
            'status'             => ['required', 'in:pending,approved,suspended'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
            'schedules'          => ['nullable', 'array'],
            'schedules.*.day_of_week'           => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time'            => ['required', 'date_format:H:i'],
            'schedules.*.end_time'              => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.slot_duration_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
        ]);

        $schedules = $validated['schedules'] ?? [];
        $password  = $validated['password'];
        unset($validated['schedules'], $validated['password'], $validated['password_confirmation']);

        // Create linked user account so the doctor can log in
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($password),
            'role'     => 'doctor',
        ]);

        $doctor = Doctor::create(array_merge($validated, ['user_id' => $user->id]));

        foreach ($schedules as $schedule) {
            $doctor->schedules()->create($schedule);
        }

        return redirect()->route('admin.doctors.index')->with('success', 'Doctor created successfully.');
    }

    public function edit(Doctor $doctor): Response
    {
        $doctor->load('schedules');
        return Inertia::render('Admin/Doctors/Edit', ['doctor' => $doctor->append('avatar_url')]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $userId = $doctor->user_id;

        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'email'              => [
                'required', 'email',
                'unique:doctors,email,' . $doctor->id,
                $userId ? 'unique:users,email,' . $userId : 'unique:users,email',
            ],
            'phone'              => ['nullable', 'string', 'max:25'],
            'specialization'     => ['required', 'array', 'min:1'],
            'specialization.*'   => ['string', 'max:100'],
            'insurance'          => ['nullable', 'array'],
            'insurance.*'        => ['string', 'max:100'],
            'qualification'      => ['nullable', 'string', 'max:200'],
            'bio'                => ['nullable', 'string', 'max:1000'],
            'experience_years'   => ['nullable', 'integer', 'min:0', 'max:60'],
            'consultation_fee'   => ['nullable', 'numeric', 'min:0'],
            'location'           => ['nullable', 'string', 'max:100'],
            'status'             => ['required', 'in:pending,approved,suspended'],
            'schedules'          => ['nullable', 'array'],
            'schedules.*.day_of_week'           => ['required', 'integer', 'between:0,6'],
            'schedules.*.start_time'            => ['required', 'date_format:H:i'],
            'schedules.*.end_time'              => ['required', 'date_format:H:i'],
            'schedules.*.slot_duration_minutes' => ['required', 'integer', 'in:15,20,30,45,60'],
        ]);

        $schedules = $validated['schedules'] ?? null;
        unset($validated['schedules']);

        $doctor->update($validated);

        // Keep the linked user account in sync
        if ($userId && $doctor->user) {
            $doctor->user->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        if ($schedules !== null) {
            $doctor->schedules()->delete();
            foreach ($schedules as $schedule) {
                $doctor->schedules()->create($schedule);
            }
        }

        return back()->with('success', 'Doctor updated successfully.');
    }

    public function approve(Doctor $doctor)
    {
        $doctor->update(['status' => 'approved']);
        return back()->with('success', "Dr. {$doctor->name} has been approved.");
    }

    public function suspend(Doctor $doctor)
    {
        $doctor->update(['status' => 'suspended']);
        return back()->with('success', "Dr. {$doctor->name} has been suspended.");
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('admin.doctors.index')->with('success', 'Doctor removed.');
    }

    public function createUserAccount(Request $request, Doctor $doctor)
    {
        if ($doctor->user_id) {
            return back()->with('error', 'This doctor already has a user account.');
        }

        if (User::where('email', $doctor->email)->exists()) {
            return back()->with('error', 'A user account with this email already exists.');
        }

        $validated = $request->validate([
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::create([
            'name'     => $doctor->name,
            'email'    => $doctor->email,
            'password' => Hash::make($validated['password']),
            'role'     => 'doctor',
        ]);

        $doctor->update(['user_id' => $user->id]);

        return back()->with('success', "User account created for Dr. {$doctor->name}.");
    }
}
