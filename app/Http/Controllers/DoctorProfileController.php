<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DoctorProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        return Inertia::render('Doctor/Profile', [
            'doctor'          => $doctor,
            'specializations' => Specialization::active()->orderBy('sort_order')->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        $validated = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', Rule::unique('doctors', 'slug')->ignore($doctor->id)],
            'phone'             => ['nullable', 'string', 'max:30'],
            'specialization'    => ['nullable', 'array'],
            'specialization.*'  => ['string', 'max:100'],
            'insurance'         => ['nullable', 'array'],
            'insurance.*'       => ['string', 'max:100'],
            'qualification'     => ['nullable', 'string', 'max:255'],
            'bio'               => ['nullable', 'string', 'max:1000'],
            'experience_years'  => ['nullable', 'integer', 'min:0', 'max:80'],
            'consultation_fee'  => ['nullable', 'numeric', 'min:0'],
            'location'          => ['nullable', 'string', 'max:255'],
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'languages'         => ['nullable', 'array'],
            'languages.*'       => ['string', 'max:100'],
        ]);

        $doctor->update($validated);

        // Keep linked user account name in sync
        if ($doctor->user && isset($validated['name'])) {
            $doctor->user->update(['name' => $validated['name']]);
        }

        return back()->with('status', 'Profile updated successfully.');
    }

    public function uploadAvatar(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;
        abort_unless($doctor, 403);

        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if ($doctor->avatar) {
            Storage::disk('public')->delete($doctor->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $doctor->update(['avatar' => $path]);

        return back()->with('status', 'Photo updated successfully.');
    }

    public function deleteAvatar(Request $request): RedirectResponse
    {
        $doctor = $request->user()->doctor;
        abort_unless($doctor, 403);

        if ($doctor->avatar) {
            Storage::disk('public')->delete($doctor->avatar);
            $doctor->update(['avatar' => null]);
        }

        return back()->with('status', 'Photo removed.');
    }
}
