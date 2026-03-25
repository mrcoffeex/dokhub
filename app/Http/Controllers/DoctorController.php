<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Doctor::approved()
            ->withCount(['appointments' => fn($q) => $q->whereIn('status', ['confirmed', 'completed'])])
            ->withCount(['reviews as reviews_count' => fn($q) => $q->where('is_approved', true)])
            ->withAvg(['reviews as review_avg_rating' => fn($q) => $q->where('is_approved', true)], 'rating');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$term}%")
                ->orWhereRaw("specialization::text ilike ?", ["%{$term}%"])
                ->orWhere('location', 'ilike', "%{$term}%")
            );
        }

        if ($request->filled('specialization')) {
            $query->whereJsonContains('specialization', $request->specialization);
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

        $specializations = DB::table('doctors')
            ->where('status', 'approved')
            ->whereNotNull('specialization')
            ->selectRaw('DISTINCT json_array_elements_text(specialization) as spec')
            ->orderBy('spec')
            ->pluck('spec');

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

        $reviews = $doctor->reviews()
            ->approved()
            ->latest()
            ->get(['id', 'patient_name', 'rating', 'comment', 'created_at']);

        $reviewStats = [
            'average' => round((float) ($reviews->avg('rating') ?? 0), 1),
            'total'   => $reviews->count(),
            'counts'  => collect([5, 4, 3, 2, 1])
                ->mapWithKeys(fn($s) => [$s => $reviews->where('rating', $s)->count()])
                ->all(),
        ];

        return Inertia::render('Doctors/Show', [
            'doctor'      => $doctor->append('avatar_url'),
            'bookedSlots' => $bookedSlots,
            'reviews'     => $reviews,
            'reviewStats' => $reviewStats,
        ]);
    }
}
