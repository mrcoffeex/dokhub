<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class SmsService
{
    private bool $enabled;
    private ?string $sid;
    private ?string $token;
    private ?string $from;

    public function __construct()
    {
        $this->enabled = (bool) config('services.twilio.enabled');
        $this->sid     = config('services.twilio.sid');
        $this->token   = config('services.twilio.token');
        $this->from    = config('services.twilio.from');
    }

    /**
     * Send an SMS to a single recipient.
     *
     * @param  string  $to   E.164 format (e.g. +639171234567)
     * @param  string  $body Message text
     */
    public function send(string $to, string $body): void
    {
        if (! $this->enabled || blank($this->sid) || blank($this->token) || blank($this->from)) {
            return;
        }

        // Normalise: strip anything that isn't digits or a leading +
        $to = preg_replace('/[^\d+]/', '', $to);

        // Auto-prefix +63 for 11-digit PH numbers starting with 09
        if (preg_match('/^09\d{9}$/', $to)) {
            $to = '+63' . substr($to, 1);
        }

        if (blank($to)) {
            return;
        }

        try {
            $client = new Client($this->sid, $this->token);
            $client->messages->create($to, [
                'from' => $this->from,
                'body' => $body,
            ]);
        } catch (\Throwable $e) {
            Log::error('SMS send failed', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
