<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        return Inertia::render('Doctor/Profile', [
            'doctor' => $doctor,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'phone'             => ['nullable', 'string', 'max:30'],
            'specialization'    => ['nullable', 'string', 'max:255'],
            'qualification'     => ['nullable', 'string', 'max:255'],
            'bio'               => ['nullable', 'string', 'max:1000'],
            'experience_years'  => ['nullable', 'integer', 'min:0', 'max:80'],
            'consultation_fee'  => ['nullable', 'numeric', 'min:0'],
            'location'          => ['nullable', 'string', 'max:255'],
            'languages'         => ['nullable', 'array'],
            'languages.*'       => ['string', 'max:100'],
        ]);

        $doctor->update($validated);

        return back()->with('status', 'Profile updated successfully.');
    }
}
