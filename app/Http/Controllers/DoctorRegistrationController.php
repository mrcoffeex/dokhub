<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DoctorRegistrationController extends Controller
{
    /**
     * Register a new doctor with pending status.
     */
    public function __invoke(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'specialization' => ['required', 'array', 'min:1'],
            'specialization.*' => ['string', 'max:100'],
            'qualification' => ['required', 'string', 'max:255'],
            'bio' => ['required', 'string', 'max:1000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:70'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'location' => ['required', 'string', 'max:255'],
            'languages' => ['required', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        // Create user account
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'doctor', // New role
        ]);

        // Create doctor profile with pending status
        Doctor::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'specialization' => $validated['specialization'],
            'qualification' => $validated['qualification'],
            'bio' => $validated['bio'],
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $validated['consultation_fee'],
            'location' => $validated['location'],
            'languages' => array_map('trim', explode(',', $validated['languages'])),
            'status' => 'pending', // Requires admin approval
        ]);

        return response()->json([
            'message' => 'Doctor application submitted successfully',
            'redirect' => '/auth/doctor-signup-success',
        ]);
    }
}
