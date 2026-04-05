<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use MessageBird\Client;
use MessageBird\Objects\Message;

class SmsService
{
    private bool $enabled;
    private ?string $apiKey;
    private ?string $from;

    public function __construct()
    {
        $this->enabled = (bool) config('services.messagebird.enabled');
        $this->apiKey  = config('services.messagebird.api_key');
        $this->from    = config('services.messagebird.from');
    }

    /**
     * Send an SMS to a single recipient.
     *
     * @param  string  $to   E.164 format (e.g. +639171234567)
     * @param  string  $body Message text
     */
    public function send(string $to, string $body): void
    {
        if (! $this->enabled || blank($this->apiKey) || blank($this->from)) {
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
            $client = new Client($this->apiKey);

            $message            = new Message();
            $message->originator = $this->from;
            $message->recipients = [ltrim($to, '+')];
            $message->body       = $body;

            $client->messages->create($message);
        } catch (\Throwable $e) {
            Log::error('SMS send failed', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
