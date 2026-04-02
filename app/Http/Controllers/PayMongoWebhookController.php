<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    /**
     * Handle incoming PayMongo webhook events.
     *
     * PayMongo signs every webhook with a HMAC-SHA256 signature sent in the
     * `paymongo-signature` header, formatted as:
     *   t=<unix_timestamp>,te=<test_env_sig>,li=<live_env_sig>
     *
     * We verify the signature before processing any event.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret    = config('services.paymongo.webhook_secret');
        $rawBody   = $request->getContent();
        $sigHeader = $request->header('paymongo-signature');

        if (! $secret || ! $sigHeader) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (! $this->verifySignature($secret, $rawBody, $sigHeader)) {
            Log::warning('PayMongo webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventType = $request->input('data.attributes.type');

        if ($eventType !== 'checkout_session.payment.paid') {
            // Acknowledge unhandled event types — do NOT return an error.
            return response()->json(['received' => true]);
        }

        $metadata  = $request->input('data.attributes.data.attributes.metadata');
        $checkoutId = $request->input('data.attributes.data.id'); // cs_…

        // Metadata must be a valid array.
        if (! is_array($metadata) || empty($metadata['doctor_id'])) {
            Log::error('PayMongo webhook: missing or invalid metadata', [
                'checkout_id' => $checkoutId,
                'metadata'    => $metadata,
            ]);
            // Return 200 to prevent PayMongo from retrying indefinitely.
            return response()->json(['received' => true]);
        }

        $doctor = Doctor::find($metadata['doctor_id']);

        if (! $doctor) {
            Log::error('PayMongo webhook: doctor not found', [
                'doctor_id'   => $metadata['doctor_id'],
                'checkout_id' => $checkoutId,
            ]);
            // Return 200 — retrying won't help if the doctor record doesn't exist.
            return response()->json(['received' => true]);
        }

        // Idempotency: return 200 if already processed (return URL may have fired first).
        if ($doctor->last_payment_id === $checkoutId) {
            return response()->json(['received' => true]);
        }

        DoctorBillingController::activatePro(
            $doctor,
            $metadata['billing_period'] ?? 'monthly',
            $checkoutId,
            'webhook'
        );

        Log::info('PayMongo webhook: Pro activated', [
            'doctor_id'   => $doctor->id,
            'checkout_id' => $checkoutId,
            'period'      => $metadata['billing_period'] ?? 'monthly',
        ]);

        return response()->json(['received' => true]);
    }

    /**
     * Verify the PayMongo HMAC-SHA256 webhook signature.
     *
     * Header format: t={timestamp},te={test_sig},li={live_sig}
     * Signed payload: "{timestamp}.{raw_body}"
     */
    private function verifySignature(string $secret, string $rawBody, string $sigHeader): bool
    {
        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            if (! str_contains($part, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        $timestamp = $parts['t'] ?? '';
        if ($timestamp === '') {
            return false;
        }

        $computed = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret);

        // Use live signature in production, test signature elsewhere.
        $provided = app()->environment('production')
            ? ($parts['li'] ?? '')
            : ($parts['te'] ?? '');

        return $provided !== '' && hash_equals($computed, $provided);
    }
}
