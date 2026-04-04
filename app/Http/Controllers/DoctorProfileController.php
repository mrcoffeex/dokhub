<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\Doctor;
use App\Models\Insurance;
use App\Models\Specialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DoctorProfileController extends Controller
{
    use ResolvesCurrentDoctor;

    public function edit(Request $request): Response
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        return Inertia::render('Doctor/Profile', [
            'doctor'          => $doctor,
            'specializations' => Specialization::active()->orderBy('sort_order')->orderBy('name')->pluck('name'),
            'insurances'      => Insurance::active()->orderBy('sort_order')->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

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
            'languages'           => ['nullable', 'array'],
            'languages.*'         => ['string', 'max:100'],
            'appointment_modes'   => ['nullable', 'array'],
            'appointment_modes.*' => ['string', 'in:in_person,online'],
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
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $disk = config('filesystems.avatar_disk');

        if ($doctor->avatar) {
            Storage::disk($disk)->delete($doctor->avatar);
        }

        $path = $request->file('avatar')->storePublicly('avatars', ['disk' => $disk]);
        $doctor->update(['avatar' => $path]);

        return back()->with('status', 'Photo updated successfully.');
    }

    public function deleteAvatar(Request $request): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        if ($doctor->avatar) {
            Storage::disk(config('filesystems.avatar_disk'))->delete($doctor->avatar);
            $doctor->update(['avatar' => null]);
        }

        return back()->with('status', 'Photo removed.');
    }

    public function updateCredentials(Request $request): RedirectResponse
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        $year = (int) date('Y');

        $validated = $request->validate([
            'education'                  => ['nullable', 'array'],
            'education.*.degree'         => ['required', 'string', 'max:200'],
            'education.*.institution'    => ['required', 'string', 'max:200'],
            'education.*.year'           => ['nullable', 'integer', 'min:1900', 'max:' . ($year + 1)],
            'affiliations'               => ['nullable', 'array'],
            'affiliations.*.name'        => ['required', 'string', 'max:200'],
            'affiliations.*.role'        => ['nullable', 'string', 'max:200'],
            'certifications'             => ['nullable', 'array'],
            'certifications.*.name'      => ['required', 'string', 'max:200'],
            'certifications.*.issuer'    => ['nullable', 'string', 'max:200'],
            'certifications.*.year'      => ['nullable', 'integer', 'min:1900', 'max:' . ($year + 1)],
        ]);

        $doctor->update($validated);

        return back()->with('credentials_status', 'Credentials updated successfully.');
    }
}
