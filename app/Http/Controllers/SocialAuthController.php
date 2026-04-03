<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google's OAuth consent screen.
     *
     * @param  'login'|'doctor'  $intent  What we do after Google returns
     */
    public function redirect(string $intent): RedirectResponse
    {
        if (! in_array($intent, ['login', 'doctor'], true)) {
            abort(404);
        }

        session(['google_oauth_intent' => $intent]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the Google callback.
     *
     * - intent = "login"   → find existing admin/approved-doctor, sign them in
     * - intent = "doctor"  → stash Google profile in session, redirect to doctor
     *                        registration form with pre-filled name/email
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')
                ->with('status', 'Google sign-in failed. Please try again.');
        }

        $intent = session()->pull('google_oauth_intent', 'login');

        // ── Doctor sign-up via Google ─────────────────────────────────────
        if ($intent === 'doctor') {
            return $this->handleDoctorIntent($googleUser);
        }

        // ── Login via Google ──────────────────────────────────────────────
        return $this->handleLoginIntent($googleUser);
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function handleLoginIntent(\Laravel\Socialite\Contracts\User $googleUser): RedirectResponse
    {
        // Try to find by google_id first, then by email
        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('login_error', 'No account found for that Google account. If you are a doctor, please use the doctor sign-up form.');
        }

        // Bind google_id if not yet set
        if (! $user->google_id) {
            $user->update([
                'google_id' => $googleUser->getId(),
                'avatar'    => $user->avatar ?? $googleUser->getAvatar(),
            ]);
        }

        // Enforce the same allow-list as Fortify's authenticateUsing
        if ($user->isAdmin()) {
            Auth::login($user, true);
            return redirect()->intended('/dashboard');
        }

        if ($user->isDoctor()) {
            $doctor = $user->doctor;

            if ($doctor?->isSuspended()) {
                return redirect()->route('login')
                    ->with('login_error', 'Your account is suspended. Please contact support.');
            }

            if ($doctor?->isApproved()) {
                Auth::login($user, true);
                return redirect()->intended('/dashboard');
            }

            return redirect()->route('login')
                ->with('login_error', 'Your doctor account is not approved yet. You will be notified once reviewed.');
        }

        return redirect()->route('login')
            ->with('login_error', 'Only approved doctors and admins may sign in.');
    }

    private function handleDoctorIntent(\Laravel\Socialite\Contracts\User $googleUser): RedirectResponse
    {
        // If an approved doctor already exists, just log them in
        $existing = User::where('google_id', $googleUser->getId())->first()
            ?? User::where('email', $googleUser->getEmail())->first();

        if ($existing?->isDoctor() && $existing->doctor?->isApproved()) {
            if (! $existing->google_id) {
                $existing->update(['google_id' => $googleUser->getId()]);
            }
            Auth::login($existing, true);
            return redirect('/dashboard');
        }

        if ($existing?->isDoctor() && ! $existing->doctor?->isApproved()) {
            return redirect()->route('auth.signup.doctor')
                ->with('google_info', 'Your doctor application is still pending review.');
        }

        // Stash in session so the doctor registration form can pre-fill name/email
        // and skip the password field.
        session([
            'google_pending_doctor' => [
                'google_id' => $googleUser->getId(),
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'avatar'    => $googleUser->getAvatar(),
            ],
        ]);

        return redirect()->route('auth.signup.doctor');
    }
}
