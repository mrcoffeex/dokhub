<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    private const SESSION_KEY    = 'ai_chat_history';
    private const MAX_TURNS      = 20; // each turn = 1 user + 1 model Content
    private const GUEST_LIMIT    = 5;  // max messages per IP per hour for guests
    private const GUEST_WINDOW   = 3600; // seconds

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'role'    => ['required', 'in:guest,doctor,admin'],
        ]);

        $apiKey = config('services.gemini.api_key');
        $model  = config('services.gemini.model', 'gemini-2.0-flash');

        if (blank($apiKey)) {
            return response()->json(['reply' => 'AI assistant is not configured yet.'], 503);
        }

        $userMessage = trim($request->string('message'));
        $role        = (string) $request->string('role');

        // IP-based rate limit for guest users
        if ($role === 'guest') {
            $ip       = $request->ip();
            $cacheKey = 'ai_guest_limit:' . sha1((string) $ip);
            $count    = (int) Cache::get($cacheKey, 0);

            if ($count >= self::GUEST_LIMIT) {
                $ttl     = Cache::getStore()->many([$cacheKey]);
                return response()->json([
                    'reply'        => "You've reached the free guest limit of " . self::GUEST_LIMIT . " messages per hour. Please try again later, or create a doctor account for unlimited access.",
                    'limit_reached' => true,
                ], 429);
            }

            Cache::put($cacheKey, $count + 1, self::GUEST_WINDOW);
        }

        $systemInstruction = Content::parse(
            $this->buildSystemInstruction($role, $request->user()),
            Role::USER
        );

        // Restore history from session (stored as plain arrays, converted back to Content objects)
        $rawHistory = session(self::SESSION_KEY . "_{$role}", []);
        $history    = array_map(
            fn (array $item) => Content::from($item),
            $rawHistory
        );

        try {
            $client = \Gemini::client($apiKey);

            $chat  = $client->generativeModel($model)
                ->withSystemInstruction($systemInstruction)
                ->startChat(history: $history);

            $response = $chat->sendMessage($userMessage);
            $reply    = $response->text();

            // Append the new turn and persist as plain arrays
            $history[] = Content::parse($userMessage, Role::USER);
            $history[] = Content::parse($reply, Role::MODEL);

            // Cap history
            if (count($history) > self::MAX_TURNS * 2) {
                $history = array_slice($history, -(self::MAX_TURNS * 2));
            }

            $serialised = array_map(fn (Content $c) => $c->toArray(), $history);
            session([self::SESSION_KEY . "_{$role}" => $serialised]);

            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Log::error('Gemini chat error', ['error' => $message]);

            // Detect quota / rate-limit errors — match only actual RESOURCE_EXHAUSTED signals
            if (str_contains($message, 'RESOURCE_EXHAUSTED') || str_contains($message, 'Quota exceeded') || str_contains($message, 'quota')) {
                $retry = '';
                if (preg_match('/retry in ([\d.]+s)/i', $message, $m)) {
                    $retry = ' Please try again in ' . $m[1] . '.';
                }

                return response()->json(
                    ['reply' => 'The AI assistant has reached its rate limit.' . $retry],
                    429
                );
            }

            // Detect invalid / unknown model name
            if (str_contains($message, 'not found') || str_contains($message, 'invalid model') || str_contains($message, 'NOT_FOUND')) {
                return response()->json(
                    ['reply' => 'The configured AI model is not available. Please contact the administrator.'],
                    503
                );
            }

            return response()->json(
                ['reply' => 'Sorry, I encountered an error. Please try again.'],
                500
            );
        }
    }

    public function clear(Request $request): JsonResponse
    {
        $request->validate(['role' => ['required', 'in:guest,doctor,admin']]);

        session()->forget(self::SESSION_KEY . '_' . $request->string('role'));

        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------

    private function buildSystemInstruction(string $role, ?\App\Models\User $user): string
    {
        $base = "You are DokHub AI, a helpful assistant for the DokHub healthcare platform in the Philippines. "
              . "Be concise, friendly, and professional. Use simple English. "
              . "Today is " . now()->format('l, F j, Y') . ". ";

        return match ($role) {
            'guest'  => $this->guestSystem($base),
            'doctor' => $this->doctorSystem($base, $user),
            'admin'  => $this->adminSystem($base, $user),
            default  => $base,
        };
    }

    private function guestSystem(string $base): string
    {
        return $base
            . "You are helping a patient or visitor on the DokHub booking platform. "
            . "Help them: find the right type of doctor, understand how to book an appointment, "
            . "explain the booking process, look up their appointment status (they can visit /my-appointment), "
            . "or answer general health questions. "
            . "Do NOT provide specific medical diagnoses. Always recommend consulting a licensed doctor for medical concerns. "
            . "If asked about pricing, explain that each doctor sets their own consultation fee visible on their profile.";
    }

    private function doctorSystem(string $base, ?\App\Models\User $user): string
    {
        if (! $user || ! $user->isDoctor()) {
            return $base . "You are assisting a doctor on the DokHub portal.";
        }

        /** @var Doctor|null $doctor */
        $doctor = $user->doctor;

        if (! $doctor) {
            return $base . "You are assisting a doctor on the DokHub portal.";
        }

        $upcomingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get(['patient_name', 'patient_email', 'appointment_date', 'appointment_time', 'appointment_type', 'status', 'reason']);

        $recentPatients = Patient::where('doctor_id', $doctor->id)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get(['name', 'gender', 'date_of_birth', 'allergies', 'medical_history', 'notes']);

        $patientCount = Patient::where('doctor_id', $doctor->id)->count();
        $pendingCount = Appointment::where('doctor_id', $doctor->id)->where('status', 'pending')->count();
        $todayCount   = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $appointmentsText = $upcomingAppointments->isEmpty()
            ? 'No upcoming appointments.'
            : $upcomingAppointments->map(fn ($a) =>
                "- {$a->patient_name} on {$a->appointment_date} at {$a->appointment_time} ({$a->appointment_type}, {$a->status})"
                . ($a->reason ? " — Reason: {$a->reason}" : '')
              )->implode("\n");

        $patientsText = $recentPatients->isEmpty()
            ? 'No patients on record.'
            : $recentPatients->map(fn ($p) =>
                "- {$p->name}" . ($p->gender ? ", {$p->gender}" : '')
                . ($p->date_of_birth ? ', DOB: ' . $p->date_of_birth->format('Y-m-d') : '')
                . ($p->allergies ? ", Allergies: {$p->allergies}" : '')
                . ($p->medical_history ? ", History: {$p->medical_history}" : '')
              )->implode("\n");

        return $base
            . "You are the personal AI assistant for Dr. {$doctor->name}. "
            . "Specialization: " . (is_array($doctor->specialization) ? implode(', ', $doctor->specialization) : ($doctor->specialization ?? 'General')) . ". "
            . "Total patients: {$patientCount}. Pending appointments: {$pendingCount}. Appointments today: {$todayCount}.\n\n"
            . "UPCOMING APPOINTMENTS (next 10):\n{$appointmentsText}\n\n"
            . "RECENT PATIENTS (last 10):\n{$patientsText}\n\n"
            . "Help the doctor with: summarizing patient info, drafting clinical notes, explaining diagnoses or medications, "
            . "interpreting appointment data, writing referral letters, or answering medical reference questions. "
            . "Always note that any AI-generated clinical content should be reviewed by the doctor before use.";
    }

    private function adminSystem(string $base, ?\App\Models\User $user): string
    {
        $totalDoctors      = Doctor::count();
        $approvedDoctors   = Doctor::where('status', 'approved')->count();
        $pendingDoctors    = Doctor::where('status', 'pending')->count();
        $totalAppointments = Appointment::count();
        $todayAppointments = Appointment::whereDate('created_at', today())->count();
        $pendingAppts      = Appointment::where('status', 'pending')->count();

        return $base
            . "You are the AI assistant for the DokHub platform administrator. "
            . "Platform summary — Doctors: {$totalDoctors} total ({$approvedDoctors} approved, {$pendingDoctors} pending). "
            . "Appointments: {$totalAppointments} total, {$pendingAppts} pending, {$todayAppointments} booked today.\n\n"
            . "Help the admin with: interpreting platform analytics, understanding user activity, "
            . "drafting policies or announcements, answering questions about platform operations, "
            . "and providing data summaries. For destructive actions, always remind them to use the admin panel directly.";
    }
}
