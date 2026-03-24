<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Doctor::approved()
            ->withCount(['appointments' => fn($q) => $q->whereIn('status', ['confirmed', 'completed'])]);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$term}%")
                ->orWhere('specialization', 'ilike', "%{$term}%")
                ->orWhere('location', 'ilike', "%{$term}%")
            );
        }

        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        // Sorting
        $sort = $request->input('sort', '');
        match ($sort) {
            'fee_asc'    => $query->orderBy('consultation_fee', 'asc'),
            'fee_desc'   => $query->orderByDesc('consultation_fee'),
            'experience' => $query->orderByDesc('experience_years'),
            default      => $query->latest(),
        };

        $doctors = $query->paginate(12)->withQueryString();

        $specializations = Doctor::approved()
            ->distinct()
            ->orderBy('specialization')
            ->pluck('specialization');

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
            'specializations' => $specializations,
            'filters' => $request->only(['search', 'specialization', 'sort']),
        ]);
    }

    public function show(Doctor $doctor): Response
    {
        abort_unless($doctor->isApproved(), 404);

        $doctor->load('schedules');

        $bookedSlots = $doctor->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>=', now()->toDateString())
            ->select('appointment_date', 'appointment_time')
            ->get()
            ->groupBy(fn($a) => $a->appointment_date->toDateString())
            ->map(fn($slots) => $slots->pluck('appointment_time')->map(fn($t) => substr($t, 0, 5)));

        return Inertia::render('Doctors/Show', [
            'doctor' => $doctor->append('avatar_url'),
            'bookedSlots' => $bookedSlots,
        ]);
    }
}
