# Controller Review: `{ControllerName}`

## Summary

| Rule | Status | Violations |
|------|--------|-----------|
| FormRequests | ✅ Pass / ❌ Fail | N |
| No business logic | ✅ Pass / ❌ Fail | N |
| Inertia/JsonResource response | ✅ Pass / ❌ Fail | N |

---

## Rule 1 — FormRequests

### `{methodName}()` ❌

**Violation:** Inline validation with `$request->validate([...])` instead of a FormRequest.

```php
// Current (violates Rule 1)
public function store(Request $request, Doctor $doctor)
{
    $validated = $request->validate([
        'patient_name' => ['required', 'string', 'max:100'],
        // ...
    ]);
```

**Fix:** Create `app/Http/Requests/StoreAppointmentRequest.php` and type-hint it:

```php
// Fixed
public function store(StoreAppointmentRequest $request, Doctor $doctor)
{
    $validated = $request->validated();
```

```php
// app/Http/Requests/StoreAppointmentRequest.php
class StoreAppointmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'patient_name'     => ['required', 'string', 'max:100'],
            'patient_email'    => ['required', 'email', 'max:150'],
            'patient_phone'    => ['required', 'string', 'max:25'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason'           => ['nullable', 'string', 'max:500'],
        ];
    }
}
```

---

## Rule 2 — No business logic

### `{methodName}()` ❌

**Violation:** Booking limit check, conflict check, and `Mail::` call are business logic in the controller.

```php
// Current (violates Rule 2) — lines 28-55
$dailyBookings = Appointment::where('patient_email', $validated['patient_email'])
    ->whereDate('created_at', today())
    ->count();

if ($dailyBookings >= 5) { ... }
// ...
Mail::to($appointment->patient_email)->send(...);
```

**Fix:** Move to `app/Services/AppointmentService.php`:

```php
// Fixed controller method
public function store(StoreAppointmentRequest $request, Doctor $doctor): AppointmentResource
{
    abort_unless($doctor->isApproved(), 404);
    $appointment = $this->appointmentService->book($doctor, $request->validated());
    return new AppointmentResource($appointment);
}
```

```php
// app/Services/AppointmentService.php
class AppointmentService
{
    public function book(Doctor $doctor, array $data): Appointment
    {
        // daily booking limit check
        // slot conflict check
        // create appointment
        // send confirmation mail
    }
}
```

---

## Rule 3 — Inertia/JsonResource response

### `{methodName}()` ❌

**Violation:** Returns a raw `response()->json(...)` or plain model instead of an `Inertia::render(...)` page response or a typed `JsonResource`.

```php
// Current (violates Rule 3)
return response()->json($appointment->load('doctor'));
```

**Fix (page response):** Use `Inertia::render()`:

```php
return Inertia::render('Booking/Success', [
    'appointment' => $appointment->load('doctor'),
]);
```

**Fix (JSON API endpoint):** Create `app/Http/Resources/AppointmentResource.php` and return it:

```php
return new AppointmentResource($appointment->load('doctor'));
```

---

## Recommended next steps

1. Create `app/Services/` directory
2. Create missing FormRequest classes: `{list}`
3. Create missing Resource classes: `{list}`
4. Refactor controller to inject service via constructor
