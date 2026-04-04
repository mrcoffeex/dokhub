---
name: feature-test-suite
description: "Generates a complete Pest v3 test suite whenever a new feature or module is added to the codebase. Use when: adding a new feature, new module, new controller, new route group, new model, new API endpoint, writing tests for existing untested code, new booking flow, new registration flow, new middleware-protected section. Produces: factory, Unit model tests, Feature controller tests covering happy path + guards + validation + ownership + authorization."
argument-hint: "Name or path of the new feature/module, e.g. 'appointments', 'app/Http/Controllers/Doctor/PrescriptionController.php'"
---

# Feature Test Suite

Generates a complete, passing Pest v3 test suite for a new feature or module in this Laravel 11 + Inertia + PostgreSQL codebase.

## Stack Conventions

- **Framework**: Laravel 11, Pest v3, PostgreSQL (`dokhub_test` for tests)
- **Test helpers**: `tests/Pest.php` — `createApprovedDoctor()`, `actingAsDoctor()`, `actingAsProDoctor()`, `validBookingPayload()`
- **Pro middleware**: Routes under `pro` middleware require `actingAsProDoctor()` (sets `trial_ends_at` to bypass `EnsureProAccess`)
- **Factories**: `database/factories/` — each model needs its own factory with relevant states
- **RefreshDatabase**: Applied to both `Feature/` and `Unit/` in `Pest.php`
- **File locations**: `tests/Unit/` for model logic, `tests/Feature/<Group>/` for HTTP tests

## Procedure

### Step 1 — Explore the feature

Read the relevant files to understand the feature:
- The model(s): `app/Models/`
- The controller(s): `app/Http/Controllers/`
- The routes: `routes/web.php`, `routes/api.php`, `routes/settings.php`
- Any middleware applied to the route group

Answer these questions before writing any test:
1. What HTTP method and URL does each action use?
2. What validation rules does the controller enforce?
3. What middleware guards the routes? (`auth`, `doctor`, `pro`, etc.)
4. Does the controller return JSON, Inertia render, or a redirect?
5. Does the model use `SoftDeletes`, `HasFactory`, or custom casts/methods?

### Step 2 — Create or update the Factory

Check if a factory already exists in `database/factories/`. If not, create one.

**Factory conventions:**
```php
namespace Database\Factories;

use App\Models\MyModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MyModelFactory extends Factory
{
    protected $model = MyModel::class;

    public function definition(): array
    {
        return [
            // all required columns with sensible fakes
        ];
    }

    // Add named states for key variations:
    public function active(): static { return $this->state(['status' => 'active']); }
    public function inactive(): static { return $this->state(['status' => 'inactive']); }
}
```

Add `use HasFactory;` to the model if it is missing.

### Step 3 — Write Unit tests (model logic only)

File: `tests/Unit/<ModelName>ModelTest.php`

Cover every non-trivial method on the model:
- Computed attributes / accessors
- Boolean state checkers (`isApproved()`, `isInTrial()`, `hasProAccess()`, etc.)
- Static generators (`generateReference()`, `generateUniqueSlug()`)
- Relationships (assert `instanceof` of related class)
- Casts (use `\DateTimeInterface::class`, not `Carbon::class`, for date casts — the actual type may be `CarbonImmutable`)
- Time-based helpers: use range assertions (`toBeGreaterThanOrEqual` / `toBeLessThanOrEqual`) instead of exact values to avoid off-by-one timing failures

**Unit test template:**
```php
use App\Models\MyModel;

describe('MyModel::someMethod()', function () {
    it('returns true when condition A', function () {
        $model = MyModel::factory()->make(['field' => 'value']);
        expect($model->someMethod())->toBeTrue();
    });

    it('returns false when condition B', function () {
        $model = MyModel::factory()->make(['field' => 'other']);
        expect($model->someMethod())->toBeFalse();
    });
});
```

### Step 4 — Write Feature tests (HTTP layer)

File: `tests/Feature/<Group>/<Feature>Test.php`

For each controller action, write tests in this order:

#### 4a. Happy path
```php
it('creates a resource successfully', function () {
    $doctor = actingAsDoctor();            // or actingAsProDoctor() for pro routes
    $this->post('/route', $payload)->assertOk();         // Inertia render
    // OR ->assertRedirect();                            // redirect response
    // OR ->assertJson([...]);                           // JSON API
    $this->assertDatabaseHas('table', [...]);
});
```

**Response type decision:**
| Controller return | Test assertion |
|---|---|
| `Inertia::render(...)` | `->assertOk()` |
| `back()->with(...)` or `redirect()` | `->assertRedirect()` |
| `response()->json(...)` | `->assertOk()->assertJson([...])` |
| `response()->json(..., 422)` | `->assertUnprocessable()` |

#### 4b. Auth guard
```php
it('redirects unauthenticated users', function () {
    $this->get('/protected-route')->assertRedirect(route('login'));
});
```

#### 4c. Ownership / authorization
```php
it('returns 403 for another doctors resource', function () {
    $doctor = actingAsDoctor();
    $other  = createApprovedDoctor();
    $resource = SomeModel::factory()->create(['doctor_id' => $other->id]);

    $this->patch("/doctor/resources/{$resource->id}", $payload)->assertForbidden();
});
```

#### 4d. Validation
```php
it('requires the title field', function () {
    $doctor = actingAsDoctor();
    $this->post('/route', ['title' => ''])->assertSessionHasErrors('title');
    // OR for JSON: ->assertJsonValidationErrors('title')
});
```

#### 4e. Business rule guards
Cover each custom guard in the controller: rate limits, daily limits, duplicate checks, status checks, slot conflicts, etc.

### Step 5 — Register helpers in Pest.php (if needed)

If the tests require a shared payload builder or actor helper not already in `tests/Pest.php`, add it there:

```php
function myFeaturePayload(array $overrides = []): array
{
    return array_merge([
        'field' => 'default_value',
    ], $overrides);
}
```

Do **not** add one-off helpers — only add to `Pest.php` if the helper will be used across multiple test files.

### Step 6 — Run and fix

Run only the new test files first:
```bash
php artisan test --filter="MyFeatureTest"
```

Common failures and fixes:

| Failure | Fix |
|---|---|
| `Call to undefined method Model::factory()` | Add `use HasFactory;` to the model |
| `Failed asserting that CarbonImmutable is Carbon` | Use `\DateTimeInterface::class` in `toBeInstanceOf()` |
| Off-by-one on day/time assertions | Use range assertions: `toBeGreaterThanOrEqual(N)->toBeLessThanOrEqual(N+1)` |
| `Expected 302 but received 200` | Controller returns `Inertia::render()` — use `assertOk()` not `assertRedirect()` |
| `Expected 302 but received 200` (JSON controller) | Controller returns `response()->json()` — use `assertOk()->assertJson([...])` |
| 403 on pro-middleware route | Use `actingAsProDoctor()` instead of `actingAsDoctor()` |
| Auth test login fails | Fortify blocks non-admin, non-approved-doctor logins — use `User::factory()->create(['role' => 'admin'])` |
| `assertSoftDeleted` vs `toBeNull` | If model uses `SoftDeletes`, use `$this->assertSoftDeleted($model)` |

Then run the full suite:
```bash
php artisan test
```

All tests must pass with **0 failures** before the task is complete.

## Checklist

Before declaring done:
- [ ] Factory exists with all required states
- [ ] `HasFactory` is on the model
- [ ] Unit tests cover all model methods with non-trivial logic
- [ ] Feature tests cover: happy path, auth guard, ownership, validation, business rules
- [ ] Response type assertions match what the controller actually returns
- [ ] Pro-middleware routes use `actingAsProDoctor()`
- [ ] `php artisan test` shows 0 failures

## Reference: Route → Auth Helper Mapping

| Route group | Auth helper |
|---|---|
| Public routes | No auth needed |
| `auth` + `doctor` middleware | `actingAsDoctor()` |
| `auth` + `doctor` + `pro` middleware | `actingAsProDoctor()` |
| Admin routes | `User::factory()->create(['role' => 'admin'])` + `actingAs()` |
