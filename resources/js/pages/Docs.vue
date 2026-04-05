<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const lastUpdated = 'April 5, 2026';

const sections = [
    { id: 'overview',        label: 'Overview' },
    { id: 'tech-stack',      label: 'Tech Stack' },
    { id: 'features',        label: 'Features' },
    { id: 'roles',           label: 'User Roles' },
    { id: 'booking',         label: 'Booking Flow' },
    { id: 'doctor-portal',   label: 'Doctor Portal' },
    { id: 'admin-portal',    label: 'Admin Portal' },
    { id: 'ai-assistant',    label: 'AI Assistant' },
    { id: 'notifications',   label: 'Notifications' },
    { id: 'auth',            label: 'Authentication' },
    { id: 'api',             label: 'API & Routes' },
    { id: 'environment',     label: 'Environment Setup' },
];

const activeSection = ref('overview');

function onScroll() {
    for (const s of [...sections].reverse()) {
        const el = document.getElementById(s.id);
        if (el && el.getBoundingClientRect().top <= 120) {
            activeSection.value = s.id;
            return;
        }
    }
    activeSection.value = sections[0].id;
}

function scrollTo(id: string) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<template>
    <Head title="Documentation — DokHub" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950" @scroll.passive="onScroll">

        <!-- Top nav -->
        <header class="sticky top-0 z-10 border-b border-gray-200 bg-white/90 backdrop-blur dark:border-gray-800 dark:bg-gray-950/90">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
                <Link href="/" class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                    <img src="/logo.png" alt="DokHub" class="h-7 w-auto" />
                    DokHub
                    <span class="ml-1 rounded-md bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">Docs</span>
                </Link>
                <Link href="/" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to home
                </Link>
            </div>
        </header>

        <div class="mx-auto flex max-w-7xl gap-0 px-4 py-10 sm:px-6 lg:px-8">

            <!-- Sidebar -->
            <aside class="hidden w-56 shrink-0 lg:block">
                <div class="sticky top-20">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-600">On this page</p>
                    <nav class="flex flex-col gap-0.5">
                        <button
                            v-for="s in sections"
                            :key="s.id"
                            type="button"
                            :class="[
                                'rounded-lg px-3 py-1.5 text-left text-sm transition',
                                activeSection === s.id
                                    ? 'bg-orange-50 font-semibold text-orange-700 dark:bg-orange-900/20 dark:text-orange-400'
                                    : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200',
                            ]"
                            @click="scrollTo(s.id)"
                        >
                            {{ s.label }}
                        </button>
                    </nav>
                </div>
            </aside>

            <!-- Content -->
            <main class="min-w-0 flex-1 pl-0 lg:pl-12" @scroll.passive="onScroll">

                <!-- Page header -->
                <div class="mb-10 border-b border-gray-200 pb-8 dark:border-gray-800">
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 dark:border-blue-800/40 dark:bg-blue-900/20">
                        <svg class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Project Documentation</span>
                    </div>
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">DokHub Documentation</h1>
                    <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
                        Complete technical and functional reference for the DokHub healthcare appointment platform.
                    </p>
                    <p class="mt-2 text-sm text-gray-400 dark:text-gray-600">Last updated: {{ lastUpdated }}</p>
                </div>

                <!-- ── Overview ── -->
                <section :id="sections[0].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Overview</h2>
                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        DokHub is a full-stack healthcare appointment booking platform built for the Philippines. It connects patients with
                        doctors through a seamless online booking experience, while giving doctors a complete clinic management portal
                        to manage appointments, patients, prescriptions, diagnoses, vitals, inventory, and billing.
                    </p>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-2 text-2xl font-bold text-orange-600">Patients</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Book appointments online, track status, receive confirmations.</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-2 text-2xl font-bold text-emerald-600">Doctors</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Full clinic portal with patient records, scheduling, billing, and AI assistance.</p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-2 text-2xl font-bold text-violet-600">Admins</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Approve doctors, monitor the platform, manage users and settings.</p>
                        </div>
                    </div>
                </section>

                <!-- ── Tech Stack ── -->
                <section :id="sections[1].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Tech Stack</h2>
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Layer</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Technology</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Version / Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900/50">
                                <tr v-for="row in [
                                    ['Backend',         'Laravel',              '13'],
                                    ['Frontend',        'Vue 3 + TypeScript',   'Composition API, script setup'],
                                    ['SSR Bridge',      'Inertia.js',           'v2'],
                                    ['Styling',         'Tailwind CSS',         'v4'],
                                    ['Database',        'PostgreSQL',           'dokhub / dokhub_test'],
                                    ['Testing',         'Pest',                 'v4 + PHPUnit 12'],
                                    ['Auth',            'Laravel Fortify',      'email/password + Google OAuth'],
                                    ['Payments',        'PayMongo',             'card & GCash checkout'],
                                    ['SMS',             'MessageBird',          'appointment notifications'],
                                    ['AI',              'Google Gemini',        'gemini-2.0-flash-lite (free tier)'],
                                    ['Maps',            'Leaflet.js',           'doctor location on profile'],
                                    ['Charts',          'ApexCharts',           'doctor analytics dashboard'],
                                ]" :key="row[0]">
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ row[0] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ row[1] }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-500">{{ row[2] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ── Features ── -->
                <section :id="sections[2].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Features</h2>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div v-for="group in [
                            {
                                title: 'Patient-Facing',
                                color: 'orange',
                                items: [
                                    'Browse & search doctor directory',
                                    'Filter by specialization, location, insurance',
                                    'Book in-person or teleconsult appointments',
                                    'Receive email & SMS confirmations',
                                    'Track appointment status via reference code',
                                    'Leave doctor reviews after completion',
                                    'Guest AI chatbot for booking guidance',
                                ],
                            },
                            {
                                title: 'Doctor Portal',
                                color: 'emerald',
                                items: [
                                    'Dashboard with analytics & charts',
                                    'Appointment calendar & status management',
                                    'Full patient records (vitals, diagnoses, prescriptions, documents)',
                                    'Prescription & diagnosis builder',
                                    'Inventory management with movement logs',
                                    'Billing & payment tracking',
                                    'Schedule configuration (days, hours, slots)',
                                    'Sub-user (staff) management',
                                    'AI assistant with real patient & appointment context',
                                    'Promotional poster generator',
                                ],
                            },
                            {
                                title: 'Admin Portal',
                                color: 'violet',
                                items: [
                                    'Doctor approval & rejection workflow',
                                    'Platform-wide analytics',
                                    'User management',
                                    'AI assistant with platform stats',
                                ],
                            },
                            {
                                title: 'Platform',
                                color: 'blue',
                                items: [
                                    'Dark / light theme toggle',
                                    'Google OAuth login',
                                    'Email verification',
                                    'Inertia.js SPA navigation (no full-page reloads)',
                                    'Rate-limited AI chat endpoint',
                                    'Spam protection via hCaptcha on booking forms',
                                    'PayMongo webhook integration',
                                    'Activity logging',
                                ],
                            },
                        ]" :key="group.title"
                            class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="mb-3 font-semibold text-gray-900 dark:text-white">{{ group.title }}</h3>
                            <ul class="space-y-1.5">
                                <li v-for="item in group.items" :key="item" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    {{ item }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- ── User Roles ── -->
                <section :id="sections[3].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">User Roles</h2>
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Role</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Access</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Notes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900/50">
                                <tr v-for="row in [
                                    ['guest',  'Unauthenticated visitor',           'Can browse doctors, book appointments, use AI chatbot, look up appointments'],
                                    ['doctor', 'Authenticated, role=doctor',        'Must complete registration and be approved by admin before accessing the portal'],
                                    ['admin',  'Authenticated, role=admin',         'Full platform management; isAdmin flag on User model'],
                                    ['sub-user', 'Authenticated, linked to doctor', 'Staff account; access to doctor portal scoped to linked doctor'],
                                ]" :key="row[0]">
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ row[0] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ row[1] }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-500">{{ row[2] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ── Booking Flow ── -->
                <section :id="sections[4].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Booking Flow</h2>
                    <ol class="space-y-4">
                        <li v-for="(step, i) in [
                            { title: 'Browse doctors', desc: 'Patient visits /doctors, filters by specialization, location, or insurance, and opens a doctor profile.' },
                            { title: 'Select slot', desc: 'Available time slots are shown based on the doctor\'s DoctorSchedule configuration. Patient picks a date and time.' },
                            { title: 'Fill booking form', desc: 'Patient enters name, email, phone (PH local format 09xxxxxxxxx), appointment type, and reason. hCaptcha is verified server-side.' },
                            { title: 'Confirmation emails & SMS', desc: 'AppointmentReceived mail is sent to the patient. Doctor is notified. An SMS is dispatched via MessageBird (PH numbers).' },
                            { title: 'Doctor confirms or cancels', desc: 'In the doctor portal the appointment status moves: pending → confirmed → completed, or pending → cancelled.' },
                            { title: 'Status emails', desc: 'AppointmentStatusConfirmed, AppointmentCancelled, and AppointmentCompleted mails are sent automatically on status changes.' },
                            { title: 'Lookup by reference', desc: 'Patient can visit /my-appointment and enter their reference code to check status without an account.' },
                        ]" :key="i"
                            class="flex gap-4 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-orange-600 text-sm font-bold text-white">{{ i + 1 }}</div>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ step.title }}</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ step.desc }}</p>
                            </div>
                        </li>
                    </ol>
                </section>

                <!-- ── Doctor Portal ── -->
                <section :id="sections[5].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Doctor Portal</h2>
                    <p class="mb-5 text-gray-600 dark:text-gray-400">
                        Accessible at <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">/doctor-dashboard</code>
                        after login. Doctor must be <strong>approved</strong> by an admin after registration.
                        Pro features (analytics, sub-users, AI) require an active plan or trial.
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div v-for="module in [
                            { name: 'Dashboard',      path: '/doctor-dashboard',            desc: 'Overview stats, upcoming appointments, recent patients, revenue charts.' },
                            { name: 'Appointments',   path: '/doctor/appointments',          desc: 'List, filter, confirm, cancel, complete appointments. Add patient from appointment.' },
                            { name: 'Patients',       path: '/doctor/patients',              desc: 'Full patient CRM: records, vitals, diagnoses, prescriptions, documents.' },
                            { name: 'Schedule',       path: '/doctor/schedule',              desc: 'Configure working days, hours, and slot duration.' },
                            { name: 'Inventory',      path: '/doctor/inventory',             desc: 'Track medicines and supplies; record stock-in / stock-out movements.' },
                            { name: 'Analytics',      path: '/doctor/analytics',             desc: 'Revenue graphs, appointment trends (Pro plan).' },
                            { name: 'Billing',        path: '/doctor/billing',               desc: 'Subscription management, PayMongo checkout.' },
                            { name: 'Profile',        path: '/doctor/profile',               desc: 'Edit public profile, specializations, fees, location, id documents.' },
                            { name: 'Sub-users',      path: '/doctor/sub-users',             desc: 'Create and manage staff accounts linked to the doctor.' },
                            { name: 'Poster',         path: '/doctor/poster',                desc: 'Generate a branded promotional poster.' },
                        ]" :key="module.name"
                            class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ module.name }}</p>
                                <code class="shrink-0 rounded bg-gray-100 px-1.5 py-0.5 text-[10px] text-gray-500 dark:bg-gray-800">{{ module.path }}</code>
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ module.desc }}</p>
                        </div>
                    </div>
                </section>

                <!-- ── Admin Portal ── -->
                <section :id="sections[6].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Admin Portal</h2>
                    <p class="mb-5 text-gray-600 dark:text-gray-400">
                        Accessible at <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">/admin</code>.
                        Admin accounts are identified by the <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">role = admin</code> column on the <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">users</code> table.
                    </p>
                    <ul class="space-y-3">
                        <li v-for="item in [
                            { name: 'Doctor Approvals',  desc: 'Review submitted doctor registrations (with uploaded ID documents), approve or reject with a reason.' },
                            { name: 'Users',             desc: 'View all registered users, roles, and activity.' },
                            { name: 'Analytics',         desc: 'Platform-wide stats: total doctors, appointments today, registrations over time.' },
                            { name: 'AI Assistant',      desc: 'AI chatbot pre-loaded with platform summary (doctor counts, appointment totals, today\'s activity).' },
                        ]" :key="item.name"
                            class="flex gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ item.desc }}</p>
                            </div>
                        </li>
                    </ul>
                </section>

                <!-- ── AI Assistant ── -->
                <section :id="sections[7].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">AI Assistant</h2>
                    <p class="mb-5 text-gray-600 dark:text-gray-400">
                        A floating chat widget powered by <strong>Google Gemini</strong> (<code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">gemini-2.0-flash-lite</code>) is available on all pages.
                        It adapts its context and persona based on the current user role.
                    </p>
                    <div class="space-y-4">
                        <div v-for="role in [
                            {
                                role: 'Guest',
                                color: 'blue',
                                context: 'No database context.',
                                helps: 'Find the right doctor, understand booking process, look up appointment by reference, general health questions.',
                            },
                            {
                                role: 'Doctor',
                                color: 'emerald',
                                context: 'Loaded with: upcoming appointments (next 10), recent patients (last 10), pending count, today\'s count, total patient count.',
                                helps: 'Summarise patient info, draft clinical notes, interpret appointment data, write referral letters, medical reference.',
                            },
                            {
                                role: 'Admin',
                                color: 'violet',
                                context: 'Loaded with: total/approved/pending doctors, total/pending/today appointments.',
                                helps: 'Interpret analytics, draft policies, summarise platform activity.',
                            },
                        ]" :key="role.role"
                            class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-gray-900">
                            <div class="mb-2 flex items-center gap-2">
                                <span :class="[
                                    'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                                    role.color === 'blue'    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                    role.color === 'emerald' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' :
                                                               'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400',
                                ]">{{ role.role }}</span>
                            </div>
                            <div class="grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <p class="mb-1 font-medium text-gray-700 dark:text-gray-300">Real-time context</p>
                                    <p class="text-gray-500 dark:text-gray-400">{{ role.context }}</p>
                                </div>
                                <div>
                                    <p class="mb-1 font-medium text-gray-700 dark:text-gray-300">Helps with</p>
                                    <p class="text-gray-500 dark:text-gray-400">{{ role.helps }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-300">
                        <strong>Rate limits:</strong> The free Gemini tier allows 1,500 requests/day and 30 requests/minute.
                        When limits are hit, the widget displays a friendly message including the retry wait time.
                        History is stored per-role in the PHP session (max 20 conversation turns).
                    </div>
                </section>

                <!-- ── Notifications ── -->
                <section :id="sections[8].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Notifications</h2>
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Event</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Email</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">SMS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900/50">
                                <tr v-for="row in [
                                    ['Appointment booked',    'AppointmentReceived → patient',         'Patient (PH numbers only)'],
                                    ['Appointment confirmed', 'AppointmentStatusConfirmed → patient',  '—'],
                                    ['Appointment completed', 'AppointmentCompleted → patient',        '—'],
                                    ['Appointment cancelled', 'AppointmentCancelled → patient',        '—'],
                                    ['Doctor approved',       'DoctorApproved → doctor',               '—'],
                                ]" :key="row[0]">
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ row[0] }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ row[1] }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-500">{{ row[2] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        SMS is delivered via <strong>MessageBird</strong>. Only Philippine numbers (<code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">09xxxxxxxxx</code>) are supported without additional carrier approval.
                    </p>
                </section>

                <!-- ── Authentication ── -->
                <section :id="sections[9].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Authentication</h2>
                    <ul class="space-y-3">
                        <li v-for="item in [
                            { title: 'Email / Password',  desc: 'Standard Laravel Fortify login with email verification. Passwords must pass the default Fortify strength rules.' },
                            { title: 'Google OAuth',      desc: 'Handled by SocialAuthController. Supports both patient and doctor intent via /auth/google/{intent}.' },
                            { title: 'Doctor signup',     desc: 'POST /auth/signup/doctor — multi-field registration including ID document upload. Account is pending until admin approves.' },
                            { title: 'Pro middleware',    desc: 'EnsureProAccess middleware guards Pro-only routes. Passes if trial_ends_at or pro_expires_at is in the future.' },
                        ]" :key="item.title"
                            class="flex gap-3 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ item.title }}</p>
                                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ item.desc }}</p>
                            </div>
                        </li>
                    </ul>
                </section>

                <!-- ── API & Routes ── -->
                <section :id="sections[10].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">API &amp; Routes</h2>
                    <p class="mb-4 text-gray-600 dark:text-gray-400">All routes are defined in <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">routes/web.php</code>, <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">routes/api.php</code>, and <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">routes/settings.php</code>.</p>
                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Method</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Path</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-900/50">
                                <tr v-for="row in [
                                    ['GET',  '/',                              'Landing page'],
                                    ['GET',  '/doctors',                       'Doctor directory'],
                                    ['GET',  '/doctors/{doctor}',              'Doctor public profile'],
                                    ['POST', '/doctors/{doctor}/book',         'Submit appointment booking'],
                                    ['POST', '/doctors/{doctor}/reviews',      'Submit doctor review'],
                                    ['GET',  '/my-appointment',                'Appointment lookup form'],
                                    ['POST', '/my-appointment',                'Lookup appointment by reference'],
                                    ['POST', '/ai/chat',                       'Send message to AI (session-based)'],
                                    ['POST', '/ai/chat/clear',                 'Clear AI session history'],
                                    ['POST', '/auth/signup/doctor',            'Doctor registration'],
                                    ['GET',  '/auth/google/{intent}',          'OAuth redirect'],
                                    ['GET',  '/auth/google/callback',          'OAuth callback'],
                                    ['POST', '/paymongo/webhook',              'PayMongo payment webhook'],
                                    ['GET',  '/doctor-dashboard',              'Doctor dashboard (auth)'],
                                    ['GET',  '/doctor/appointments',           'Doctor appointment list (auth)'],
                                    ['GET',  '/doctor/patients',               'Doctor patients CRM (auth)'],
                                    ['GET',  '/admin',                         'Admin overview (auth + admin)'],
                                ]" :key="row[1]">
                                    <td class="px-4 py-3">
                                        <span :class="[
                                            'rounded px-1.5 py-0.5 text-xs font-bold',
                                            row[0] === 'GET'   ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                            row[0] === 'POST'  ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                            row[0] === 'PATCH' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
                                                                  'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                        ]">{{ row[0] }}</span>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">{{ row[1] }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ row[2] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ── Environment Setup ── -->
                <section :id="sections[11].id" class="mb-14 scroll-mt-24">
                    <h2 class="mb-4 text-2xl font-bold text-gray-900 dark:text-white">Environment Setup</h2>
                    <p class="mb-4 text-gray-600 dark:text-gray-400">
                        Copy <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">.env.example</code> to <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">.env</code> and fill in the required values.
                    </p>
                    <div class="space-y-4">
                        <div v-for="group in [
                            {
                                title: 'Database',
                                vars: [
                                    ['DB_CONNECTION',   'pgsql'],
                                    ['DB_DATABASE',     'dokhub'],
                                    ['DB_USERNAME',     'your_db_user'],
                                    ['DB_PASSWORD',     'your_db_password'],
                                ],
                            },
                            {
                                title: 'Mail',
                                vars: [
                                    ['MAIL_MAILER',     'smtp'],
                                    ['MAIL_HOST',       'smtp.example.com'],
                                    ['MAIL_FROM_ADDRESS', 'noreply@dokhub.ph'],
                                ],
                            },
                            {
                                title: 'MessageBird SMS',
                                vars: [
                                    ['MESSAGEBIRD_ACCESS_KEY', 'your_key'],
                                    ['MESSAGEBIRD_ORIGINATOR', 'DokHub'],
                                ],
                            },
                            {
                                title: 'Google OAuth',
                                vars: [
                                    ['GOOGLE_CLIENT_ID',     'your_client_id'],
                                    ['GOOGLE_CLIENT_SECRET', 'your_client_secret'],
                                    ['GOOGLE_REDIRECT_URI',  'https://yourdomain/auth/google/callback'],
                                ],
                            },
                            {
                                title: 'PayMongo',
                                vars: [
                                    ['PAYMONGO_PUBLIC_KEY',   'pk_live_...'],
                                    ['PAYMONGO_SECRET_KEY',   'sk_live_...'],
                                    ['PAYMONGO_WEBHOOK_SECRET', 'whsk_...'],
                                ],
                            },
                            {
                                title: 'Google Gemini AI',
                                vars: [
                                    ['GEMINI_API_KEY',  'your_key — get at aistudio.google.com'],
                                    ['GEMINI_MODEL',    'gemini-2.0-flash-lite'],
                                ],
                            },
                            {
                                title: 'hCaptcha',
                                vars: [
                                    ['HCAPTCHA_SECRET', 'your_secret'],
                                    ['HCAPTCHA_SITEKEY', 'your_sitekey'],
                                ],
                            },
                        ]" :key="group.title"
                            class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <div class="border-b border-gray-100 bg-gray-50 px-4 py-2.5 dark:border-gray-800 dark:bg-gray-900">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-400">{{ group.title }}</p>
                            </div>
                            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div v-for="v in group.vars" :key="v[0]" class="flex items-center gap-4 bg-white px-4 py-2.5 dark:bg-gray-900/50">
                                    <code class="w-56 shrink-0 text-xs font-semibold text-gray-800 dark:text-gray-200">{{ v[0] }}</code>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ v[1] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>
</template>
