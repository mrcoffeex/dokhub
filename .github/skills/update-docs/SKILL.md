---
name: update-docs
description: "Updates the DokHub documentation page whenever a new feature is added, an existing feature is revised, a new route is registered, a new model or service is introduced, or any user-facing behaviour changes. Use when: adding a feature, changing a route, updating a model, changing auth/billing/notifications/AI behaviour, revising environment variables. Produces: targeted edits to resources/js/pages/Docs.vue only — no new files."
argument-hint: "Brief description of what changed, e.g. 'added teleconsult video link feature', 'new /doctor/reports route', 'added Twilio fallback SMS'"
---

# Update Docs Skill

Keeps `resources/js/pages/Docs.vue` accurate and up-to-date after any project change.

## Documentation File

**Location**: `resources/js/pages/Docs.vue`  
**Route**: `/docs` (registered in `routes/web.php` as `Route::inertia('/docs', 'Docs')->name('docs')`)  
**Layout nav link**: `GuestLayout.vue` desktop nav + footer  

The page is a standalone Vue component (no GuestLayout wrapper) with a sticky left sidebar for section navigation.

## Sections in Docs.vue

| Section id        | Content                                                    |
|-------------------|------------------------------------------------------------|
| `overview`        | What DokHub is; patient / doctor / admin summary cards     |
| `tech-stack`      | Dependency table (framework, frontend, DB, services)       |
| `features`        | Feature checklist grouped by Patient / Doctor / Admin / Platform |
| `roles`           | User role table (guest, doctor, admin, sub-user)           |
| `booking`         | Step-by-step booking flow (numbered list)                  |
| `doctor-portal`   | Module cards with path + description                       |
| `admin-portal`    | Admin capability list                                      |
| `ai-assistant`    | AI chat widget: roles, context, rate limits                |
| `notifications`   | Email + SMS notification event table                       |
| `auth`            | Auth methods: email/password, Google OAuth, doctor signup, pro middleware |
| `api`             | Route reference table (method, path, description)          |
| `environment`     | `.env` variable groups per service                         |

## Procedure

### Step 1 — Identify what changed

Read the argument or description provided by the user. Determine which sections are affected:

- New **model / database table** → `tech-stack`, `features`, `doctor-portal` (if doctor-facing), `roles` (if new role)
- New **route** → `api` (add row), `doctor-portal` or `admin-portal` (if UI module)
- New **feature** → `features` (add checklist item), possibly `doctor-portal` / `admin-portal` (add module card)
- New **third-party service** (SMS, payment, AI) → `tech-stack`, `notifications` or `ai-assistant` or `environment`
- Changed **authentication** flow → `auth`
- New **environment variable** → `environment` (add to correct group, or create new group)
- Changed **booking flow** → `booking`
- Removed feature → remove the corresponding entry from all affected sections

### Step 2 — Read the current file

```
read_file resources/js/pages/Docs.vue (full file)
```

Understand the exact current content so edits are minimal and precise.

### Step 3 — Read changed source files

Read the actual changed files to get accurate details:
- New route → `routes/web.php`
- New controller → `app/Http/Controllers/<Name>.php`
- New model → `app/Models/<Name>.php`
- New env var → `.env.example`
- New service → `config/services.php`

Do **not** guess — read the source.

### Step 4 — Apply targeted edits

Use `replace_string_in_file` or `multi_replace_string_in_file` to edit **only the relevant data arrays** inside `Docs.vue`.

**Editing conventions:**
- All section data is stored as inline JavaScript arrays inside `v-for` directives in the template — edit those arrays directly.
- For route table rows: add to the array in the `api` section, maintaining the `[METHOD, '/path', 'Description']` format.
- For feature checklist items: add to the correct group's `items` array.
- For module cards: add to the doctor-portal or admin-portal `v-for` array with `{ name, path, desc }` shape.
- For env groups: add a `{ title, vars: [[KEY, hint], ...] }` block, or add a row to an existing group.
- For tech-stack rows: maintain the `[Layer, Technology, Version/Notes]` format.
- Update `lastUpdated` at the top of `<script setup>` to today's date whenever you make changes.

**Do NOT:**
- Rewrite the entire file
- Change styling, layout, or structure not related to the update
- Add new sections unless a truly new top-level category is introduced
- Move or reorder existing sections

### Step 5 — Verify

After editing, check for TypeScript/Vue errors:
```
get_errors resources/js/pages/Docs.vue
```

If none, the update is complete. Confirm to the user which section(s) were updated and what was added/changed.

## Example: Adding a new doctor portal module

If a new `DoctorReportsController` is added with route `GET /doctor/reports`:

1. **`api` section** — add `['GET', '/doctor/reports', 'Doctor revenue & activity reports (auth)']`
2. **`doctor-portal` section** — add `{ name: 'Reports', path: '/doctor/reports', desc: 'Monthly revenue, appointment trends, patient growth.' }`
3. **`features`** — add `'Revenue & activity reports'` under the Doctor group
4. Update `lastUpdated`

## Example: Adding a new environment variable

If `VONAGE_API_KEY` is added for a new SMS fallback:

1. **`environment` section** — add a new group `{ title: 'Vonage SMS', vars: [['VONAGE_API_KEY', 'your_key'], ['VONAGE_API_SECRET', 'your_secret']] }` or add to an existing SMS group
2. **`tech-stack`** — update the SMS row to reflect the new provider
3. **`notifications`** — update the SMS column note if behaviour changed
4. Update `lastUpdated`
