<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorScheduleController extends Controller
{
    public function edit(Request $request): Response
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        $doctor->load('schedules');

        // Build a full 7-day map so the frontend always has all days
        $scheduleMap = collect(range(0, 6))->mapWithKeys(function ($day) use ($doctor) {
            $existing = $doctor->schedules->firstWhere('day_of_week', $day);
            return [$day => [
                'day_of_week'           => $day,
                'is_active'             => $existing?->is_active ?? false,
                'start_time'            => $existing?->start_time ? substr($existing->start_time, 0, 5) : '09:00',
                'end_time'              => $existing?->end_time   ? substr($existing->end_time,   0, 5) : '17:00',
                'slot_duration_minutes' => $existing?->slot_duration_minutes ?? 30,
            ]];
        })->values();

        return Inertia::render('Doctor/Schedule', [
            'doctor'    => $doctor,
            'schedules' => $scheduleMap,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        $validated = $request->validate([
            'schedules'                             => ['required', 'array', 'size:7'],
            'schedules.*.day_of_week'               => ['required', 'integer', 'between:0,6'],
            'schedules.*.is_active'                 => ['required', 'boolean'],
            'schedules.*.start_time'                => ['required', 'date_format:H:i'],
            'schedules.*.end_time'                  => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.slot_duration_minutes'     => ['required', 'integer', 'in:15,20,30,45,60'],
        ]);

        // Replace all schedules for this doctor
        $doctor->schedules()->delete();

        foreach ($validated['schedules'] as $s) {
            $doctor->schedules()->create([
                'day_of_week'           => $s['day_of_week'],
                'is_active'             => $s['is_active'],
                'start_time'            => $s['start_time'],
                'end_time'              => $s['end_time'],
                'slot_duration_minutes' => $s['slot_duration_minutes'],
            ]);
        }

        return back()->with('success', 'Schedule saved successfully.');
    }
}
