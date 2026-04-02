<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\PaymentLog;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DoctorBillingController extends Controller
{
    public function index(Request $request): Response
    {
        $doctor = Doctor::where('user_id', $request->user()->id)->firstOrFail();

        return Inertia::render('Doctor/Billing', [
            'plan'               => $doctor->plan,
            'isInTrial'          => $doctor->isInTrial(),
            'isPaidPro'          => $doctor->isPaidPro(),
            'hasProAccess'       => $doctor->hasProAccess(),
            'trialDaysRemaining' => $doctor->trialDaysRemaining(),
            'trialEndsAt'        => $doctor->trial_ends_at?->toDateString(),
            'proExpiresAt'       => $doctor->pro_expires_at?->toDateString(),
        ]);
    }

    /**
     * Create a PayMongo checkout session and redirect the doctor to it.
     *
     * We store the full checkout session ID (cs_…) in the doctor row so the
     * return handler can look it up server-side without trusting the URL param.
     */
    public function checkout(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $validated = $request->validate([
            'billing_period' => ['required', 'in:monthly,yearly'],
        ]);

        $doctor = Doctor::where('user_id', $request->user()->id)->firstOrFail();

        $isYearly = $validated['billing_period'] === 'yearly';
        $amount   = $isYearly ? 449900 : 49900; // centavos: ₱4,499 / ₱499
        $label    = $isYearly ? 'Yearly' : 'Monthly';

        try {
            $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
                ->timeout(15)
                ->post('https://api.paymongo.com/v1/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'billing' => [
                                'name'  => $doctor->name,
                                'email' => $doctor->email,
                            ],
                            'line_items' => [[
                                'amount'      => $amount,
                                'currency'    => 'PHP',
                                'name'        => "DokHub Pro – {$label}",
                                'description' => "{$label} Pro subscription for DokHub",
                                'quantity'    => 1,
                            ]],
                            'payment_method_types' => [
                                'card', 'gcash', 'paymaya', 'grab_pay',
                                'dob', 'dob_ubp', 'brankas_bdo', 'brankas_landbank',
                            ],
                            // {{CHECKOUT_SESSION_ID}} is PayMongo's placeholder.
                            // We also store the full cs_ ID server-side (below) so we
                            // never rely solely on this URL param in the return handler.
                            'success_url' => route('doctor.billing.return') . '?session_id={{CHECKOUT_SESSION_ID}}',
                            'cancel_url'  => route('doctor.billing'),
                            'metadata'    => [
                                'doctor_id'      => $doctor->id,
                                'billing_period' => $validated['billing_period'],
                            ],
                        ],
                    ],
                ]);
        } catch (ConnectionException $e) {
            Log::error('PayMongo checkout: connection error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Could not reach the payment gateway. Please try again.');
        }

        if ($response->failed()) {
            Log::error('PayMongo checkout: API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return back()->with('error', 'Could not initiate payment. Please try again later.');
        }

        // Store the authoritative full session ID server-side.
        $checkoutSessionId = $response->json('data.id'); // cs_…
        $doctor->update(['pending_checkout_session_id' => $checkoutSessionId]);

        return Inertia::location($response->json('data.attributes.checkout_url'));
    }

    /**
     * Handle the return redirect from PayMongo after checkout.
     *
     * Security approach:
     * - Primary: look up the session ID we stored server-side in the doctor row.
     * - Fallback: use the URL param, normalising the cs_ prefix (PayMongo's checkout
     *   URL strips it, so the substituted {{CHECKOUT_SESSION_ID}} may arrive without it).
     * - The URL param is sanitised with a strict regex before use to prevent SSRF.
     */
    public function checkoutReturn(Request $request): RedirectResponse
    {
        $doctor = Doctor::where('user_id', $request->user()->id)->firstOrFail();

        // Prefer the server-stored ID; fall back to the normalised URL param.
        $sessionId = $doctor->pending_checkout_session_id;

        if (! $sessionId) {
            $raw = (string) $request->query('session_id', '');

            // PayMongo session IDs use base62 (a-z, A-Z, 0-9) after an optional cs_ prefix —
            // NOT just hex. The i-flag covers A-Z. Max 128 chars caps malicious input.
            if (! preg_match('/^(cs_)?[a-z0-9]{20,128}$/i', $raw)) {
                // Graceful fallback: webhook may have already activated Pro and cleared
                // the pending session before the browser return URL fired.
                if ($doctor->isPaidPro() && $doctor->last_payment_id) {
                    return redirect()->route('doctor.billing')->with('success', 'Your Pro plan is now active!');
                }

                Log::warning('PayMongo return: no valid session ID found', [
                    'raw'    => $raw,
                    'doctor' => $doctor->id,
                ]);
                return redirect()->route('doctor.billing')
                    ->with('error', 'Invalid payment session. If you were charged, please contact support.');
            }

            // PayMongo substitutes {{CHECKOUT_SESSION_ID}} with the hash portion (no cs_ prefix).
            // Normalise to the full cs_ form for the API lookup.
            $sessionId = str_starts_with($raw, 'cs_') ? $raw : 'cs_' . $raw;
        }

        try {
            $response = Http::withBasicAuth(config('services.paymongo.secret_key'), '')
                ->timeout(15)
                ->get("https://api.paymongo.com/v1/checkout_sessions/{$sessionId}");
        } catch (ConnectionException $e) {
            Log::error('PayMongo return: connection error', ['error' => $e->getMessage(), 'session' => $sessionId]);
            return redirect()->route('doctor.billing')->with('error', 'Could not reach the payment gateway. Please try again.');
        }

        if ($response->failed()) {
            Log::error('PayMongo return: session lookup failed', [
                'session' => $sessionId,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            return redirect()->route('doctor.billing')->with('error', 'Could not verify payment. Please contact support.');
        }

        // The checkout session's own `status` is only ever 'active' or 'expired'.
        // A completed payment is indicated by the embedded payment_intent status.
        $paymentIntentStatus = $response->json('data.attributes.payment_intent.attributes.status');
        $metadata            = $response->json('data.attributes.metadata');
        $checkoutId          = $response->json('data.id'); // cs_… (idempotency key)

        if ($paymentIntentStatus !== 'succeeded') {
            Log::info('PayMongo return: payment not yet succeeded', [
                'session'               => $sessionId,
                'payment_intent_status' => $paymentIntentStatus,
            ]);
            return redirect()->route('doctor.billing')->with('error', 'Payment was not completed. Please try again.');
        }

        // Verify the authenticated doctor owns this payment session.
        if ((int) ($metadata['doctor_id'] ?? 0) !== $doctor->id) {
            Log::warning('PayMongo return: doctor ID mismatch', [
                'session'     => $sessionId,
                'metadata_id' => $metadata['doctor_id'] ?? null,
                'auth_doctor' => $doctor->id,
            ]);
            return redirect()->route('doctor.billing')->with('error', 'Payment verification failed.');
        }

        // Idempotency: already activated (webhook may have fired first).
        if ($doctor->last_payment_id === $checkoutId) {
            $doctor->update(['pending_checkout_session_id' => null]);
            return redirect()->route('doctor.billing')->with('success', 'Your Pro plan is already active!');
        }

        $billingPeriod = $metadata['billing_period'] ?? 'monthly';

        self::activatePro($doctor, $billingPeriod, $checkoutId);

        $label = $billingPeriod === 'yearly' ? '1 year' : '1 month';

        return redirect()->route('doctor.billing')
            ->with('success', "You're now on Pro! Added {$label} Subscription in your account.");
    }

    /**
     * Activate or extend Pro for a doctor atomically.
     *
     * @param  Doctor  $doctor
     * @param  string  $billingPeriod  'monthly' | 'yearly'
     * @param  string  $checkoutId     PayMongo checkout session ID used as idempotency key
     */
    public static function activatePro(Doctor $doctor, string $billingPeriod, string $checkoutId, string $source = 'return_url'): void
    {
        $months = $billingPeriod === 'yearly' ? 12 : 1;
        $amount = $billingPeriod === 'yearly' ? 449900 : 49900;

        DB::transaction(function () use ($doctor, $months, $amount, $billingPeriod, $checkoutId, $source) {
            // Re-fetch with a row lock to guard against concurrent webhook + return URL races.
            $locked = Doctor::lockForUpdate()->find($doctor->id);

            // Idempotency inside the transaction.
            if ($locked->last_payment_id === $checkoutId) {
                return;
            }

            // Priority: extend from existing pro expiry → or carry over remaining trial → or start from now.
            if ($locked->isPaidPro() && $locked->pro_expires_at) {
                $base = $locked->pro_expires_at->copy();
            } elseif ($locked->isInTrial() && $locked->trial_ends_at?->isFuture()) {
                $base = $locked->trial_ends_at->copy();
            } else {
                $base = now();
            }

            $proExpiresAt = $base->addMonths($months);

            $locked->update([
                'plan'                        => 'pro',
                'pro_expires_at'              => $proExpiresAt,
                'last_payment_id'             => $checkoutId,
                'pending_checkout_session_id' => null,
            ]);

            PaymentLog::create([
                'doctor_id'           => $locked->id,
                'checkout_session_id' => $checkoutId,
                'billing_period'      => $billingPeriod,
                'amount'              => $amount,
                'status'              => 'completed',
                'source'              => $source,
                'pro_expires_at'      => $proExpiresAt,
            ]);
        });
    }
}
