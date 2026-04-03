<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class DoctorRegistrationController extends Controller
{
    /**
     * Register a new doctor with pending status.
     * Supports both regular (password) and Google OAuth sign-up flows.
     */
    public function __invoke(Request $request)
    {
        $googlePending = session('google_pending_doctor');
        $isGoogleSignup = ! empty($googlePending);

        $captchaConfigured = app()->isProduction()
            && config('services.hcaptcha.secret')
            && config('services.hcaptcha.sitekey');

        if ($captchaConfigured) {
            $captchaResponse = Http::asForm()->post('https://hcaptcha.com/siteverify', [
                'secret'   => config('services.hcaptcha.secret'),
                'response' => $request->input('hcaptcha_token', ''),
            ]);
            $captchaBody = $captchaResponse->json();
            if (! ($captchaBody['success'] ?? false)) {
                Log::warning('hCaptcha verification failed (doctor registration)', [
                    'error-codes' => $captchaBody['error-codes'] ?? [],
                    'http_status' => $captchaResponse->status(),
                ]);
                return response()->json([
                    'message' => 'Human verification failed. Please try again.',
                    'errors'  => ['hcaptcha_token' => ['Human verification failed. Please try again.']],
                ], 422);
            }
        }

        $rules = [
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', 'unique:users', 'unique:doctors'],
            'phone'            => ['required', 'string', 'max:20'],
            'specialization'   => ['required', 'array', 'min:1'],
            'specialization.*' => ['string', 'max:100'],
            'qualification'    => ['required', 'string', 'max:255'],
            'bio'              => ['required', 'string', 'max:1000'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:70'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'location'         => ['required', 'string', 'max:255'],
            'languages'        => ['required', 'string', 'max:500'],
            'insurance'        => ['nullable', 'array'],
            'insurance.*'      => ['string', 'max:100'],
        ];

        if (! $isGoogleSignup) {
            $rules['password'] = ['required', 'string', Password::min(8), 'confirmed'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('DoctorRegister 422', [
                'errors' => $validator->errors()->toArray(),
                'input'  => $request->except(['password', 'password_confirmation']),
            ]);
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        // Create user account
        $userData = [
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'role'  => 'doctor',
        ];

        if ($isGoogleSignup) {
            $userData['google_id']     = $googlePending['google_id'];
            $userData['avatar']        = $googlePending['avatar'];
            $userData['password']      = Hash::make(Str::random(32)); // unusable random password
            $userData['password_set']  = false;
        } else {
            $userData['password']     = Hash::make($validated['password']);
            $userData['password_set'] = true;
        }

        $user = User::create($userData);

        // Mark email as verified for Google-sourced accounts (Google already verified it)
        if ($isGoogleSignup) {
            $user->markEmailAsVerified();
        }

        // Create doctor profile with pending status
        Doctor::create([
            'user_id'          => $user->id,
            'name'             => $validated['name'],
            'email'            => $validated['email'],
            'phone'            => $validated['phone'],
            'specialization'   => $validated['specialization'],
            'qualification'    => $validated['qualification'],
            'bio'              => $validated['bio'],
            'experience_years' => $validated['experience_years'],
            'consultation_fee' => $validated['consultation_fee'],
            'location'         => $validated['location'],
            'languages'        => array_map('trim', explode(',', $validated['languages'])),
            'insurance'        => $validated['insurance'] ?? null,
            'plan'             => 'basic',
            'trial_ends_at'    => null,
            'status'           => 'pending',
        ]);

        // Clear the Google session data now that the account is created
        session()->forget('google_pending_doctor');

        return response()->json([
            'message'  => 'Doctor application submitted successfully',
            'redirect' => '/auth/doctor-signup-success',
        ]);
    }
}

