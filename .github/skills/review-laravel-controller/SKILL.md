---
name: review-laravel-controller
description: "Reviews Laravel controllers for architectural compliance. Use when: auditing a controller, reviewing a controller, checking controller quality, validating controller structure, ensuring FormRequest usage, checking for business logic in controllers, verifying JsonResource responses. Enforces three rules: (1) validation via FormRequests only, (2) no inline business logic (must live in Services), (3) all responses must return a JsonResource."
argument-hint: "Path to the controller file or directory, e.g. app/Http/Controllers/AppointmentController.php"
---

# Review Laravel Controller

Reviews one or more Laravel controllers and enforces the three architectural rules for this codebase.

## Rules

| # | Rule | Pass Condition | Common Violation |
|---|------|---------------|-----------------|
| 1 | **FormRequests** | Every route method with input uses a typed `FormRequest` parameter | `$request->validate([...])` inline, or bare `Request $request` on write methods |
| 2 | **No business logic** | Controller methods delegate to a Service class; no queries, loops, conditionals, or mail sending beyond the call to the service | DB queries, `Mail::`, model creation, duplicate checks, or multi-step orchestration in the method body |
| 3 | **Inertia/JsonResource response** | Every method returns either `Inertia::render(...)` (for page responses) or a `JsonResource` (for API/JSON endpoints); no plain model, collection, or raw `response()->json()` returns | `return $model`, `return $collection`, `response()->json($array)`, or `back()` with data |

## Procedure

### 1. Identify scope

If the user named a specific file, read that file.  
If no file is given, list `app/Http/Controllers/` recursively and ask which controller(s) to review, or proceed with all of them sequentially.

### 2. For each controller

#### 2a. Read the file

Read the full controller file. Note:
- Namespace and class name
- Every public method and its parameter types
- All `use` imports

#### 2b. Apply Rule 1 — FormRequests

Search for any of these patterns (all are violations):
- `$request->validate([`
- `$request->validated()` without a typed FormRequest parameter
- A method parameter typed as `Illuminate\Http\Request` (bare `Request`) on any method that reads user input (`store`, `update`, `create`, `patch`, etc.)

A method **passes** if its parameter is a class that extends `Illuminate\Foundation\Http\FormRequest`.

Check `app/Http/Requests/` for existing FormRequests. Note missing ones.

#### 2c. Apply Rule 2 — No business logic

Flag any of the following inside a controller method body:
- Eloquent queries: `Model::where(`, `->create(`, `->update(`, `->save(`, `->delete(`, `->count(`, `->exists(`
- Mail sending: `Mail::`, `->send(`, `->queue(`
- Conditional business rules (e.g., booking limit checks, conflict checks)
- Loops over collections for transformation
- Calls to `Log::` for business events

A method **passes** if its body contains only: parameter resolution, a single call to a Service class method, and a return statement.

Check `app/Services/` for existing services. Note which service the logic should move to (name it `{Domain}Service`, e.g., `AppointmentService`).

#### 2d. Apply Rule 3 — Inertia/JsonResource response

Flag any return that is neither an Inertia response nor a JsonResource:
- `response()->json($array)` — raw JSON array (use a JsonResource instead)
- `return $model` — plain Eloquent model
- `return $collection` — plain collection
- `back()` with data payload (acceptable for redirects without data; flag only when used to return model/collection data)

A method **passes** if it returns:
- `Inertia::render('View/Name', [...])` for page responses, or
- `new XxxResource(...)` / `XxxResource::collection(...)` for JSON API endpoints

Check `app/Http/Resources/` for existing resources when a JsonResource is warranted.

### 3. Produce the review report

Output a structured report using the template in [./references/report-template.md](./references/report-template.md).

For every violation:
- Quote the offending lines
- State which rule is broken
- Give a concrete, copy-paste-ready fix

### 4. Offer to fix

Ask the user: "Would you like me to implement the fixes?" If yes:
1. Create missing FormRequest classes in `app/Http/Requests/`
2. Create missing Service classes in `app/Services/`
3. Create missing JsonResource classes in `app/Http/Resources/`
4. Refactor the controller methods to delegate to the service and return the resource

Apply fixes one controller at a time and re-validate after each.
