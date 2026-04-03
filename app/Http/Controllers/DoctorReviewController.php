<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class DoctorReviewController extends Controller
{
    public function store(Request $request, Doctor $doctor)
    {
        abort_unless($doctor->isApproved(), 404);

        // --- Per-IP daily rate limit: 1 review per day ---
        $ipKey = 'review-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 1)) {
            $seconds = RateLimiter::availableIn($ipKey);
            $hours   = ceil($seconds / 3600);
            return back()->withErrors([
                'rate_limit' => "You can only submit one review per day. Please try again in {$hours} hour(s).",
            ]);
        }

        $captchaConfigured = app()->isProduction() && config('services.hcaptcha.secret') && config('services.hcaptcha.sitekey');

        $validated = $request->validate([
            'patient_name'    => ['required', 'string', 'max:100'],
            'patient_email'   => ['required', 'email', 'max:150'],
            'rating'          => ['required', 'integer', 'min:1', 'max:5'],
            'comment'         => ['nullable', 'string', 'max:1000'],
            'hcaptcha_token'  => $captchaConfigured ? ['required', 'string'] : ['nullable', 'string'],
        ]);

        // --- hCaptcha server-side verification (production only, when keys are set) ---
        if ($captchaConfigured) {
            try {
                $captchaResponse = Http::timeout(10)->asForm()->post('https://hcaptcha.com/siteverify', [
                    'secret'   => config('services.hcaptcha.secret'),
                    'sitekey'  => config('services.hcaptcha.sitekey'),
                    'response' => $validated['hcaptcha_token'],
                    'remoteip' => $request->ip(),
                ]);

                $captchaData   = $captchaResponse->json();
                $captchaOk     = ($captchaData['success'] ?? false) === true;
                $captchaErrors = implode(', ', $captchaData['error-codes'] ?? []);

                if (! $captchaOk) {
                    \Illuminate\Support\Facades\Log::warning('hCaptcha verification failed', [
                        'error-codes' => $captchaErrors,
                        'doctor_id'   => $doctor->id,
                        'ip'          => $request->ip(),
                    ]);
                    return back()->withErrors(['hcaptcha_token' => 'Captcha verification failed. Please solve the captcha again.']);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('hCaptcha HTTP error: ' . $e->getMessage());
                return back()->withErrors(['hcaptcha_token' => 'Could not verify captcha. Please try again.']);
            }
        }

        // --- 1 email per doctor ---
        $alreadyReviewed = DoctorReview::where('doctor_id', $doctor->id)
            ->where('patient_email', $validated['patient_email'])
            ->exists();

        if ($alreadyReviewed) {
            return back()->withErrors(['patient_email' => 'You have already submitted a review for this doctor.']);
        }

        DoctorReview::create([
            'doctor_id'    => $doctor->id,
            'patient_name' => $validated['patient_name'],
            'patient_email'=> $validated['patient_email'],
            'rating'       => $validated['rating'],
            'comment'      => $validated['comment'] ?? null,
            'is_approved'  => true,
        ]);

        // Register the hit only after successful storage (decays after 24 hours)
        RateLimiter::hit($ipKey, 86400);

        return back()->with('review_success', 'Thank you for your review!');
    }
}
