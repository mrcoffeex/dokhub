<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import { toast } from 'vue-sonner';

const form = useForm({
    name:            '',
    phone:           '',
    email:           '',
    gender:          '' as '' | 'male' | 'female' | 'other',
    date_of_birth:   '',
    allergies:       '',
    medical_history: '',
    notes:           '',
});

function submit() {
    form.post('/doctor/patients', {
        onSuccess: () => {
            toast.success('Patient registered', {
                description: 'Walk-in patient has been added to your records.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not register patient', {
                description: 'Please review the highlighted fields and try again.',
                duration: 5000,
            });
        },
    });
}
</script>

<template>
    <Head title="Register Walk-in Patient" />
    <DoctorLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <Link
                    href="/doctor/patients"
                    class="flex items-center gap-1.5 text-gray-400 transition hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Patients
                </Link>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <span class="font-semibold text-gray-900 dark:text-white">Register Walk-in</span>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">

            <!-- Page hero -->
            <div class="mb-6 flex items-center gap-4 rounded-2xl border border-orange-100 bg-gradient-to-br from-orange-50 to-indigo-50 p-5 dark:border-orange-900/30 dark:from-orange-950/30 dark:to-indigo-950/30">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-orange-600 shadow-sm">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-900 dark:text-white">New Walk-in Patient</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Register a patient who visits in person. Name and phone are required — all other fields are optional.
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5">

                <!-- ── Section: Basic information ──────────────────────── -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Basic Information</h3>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Required to create the patient record.</p>
                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">
                        <!-- Full name -->
                        <div class="sm:col-span-2">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Full Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="e.g. Maria Santos"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Phone Number <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    placeholder="+63 912 345 6789"
                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': form.errors.phone }"
                                />
                            </div>
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email <span class="text-xs font-normal text-gray-400">(optional)</span>
                            </label>
                            <div class="relative">
                                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="patient@example.com"
                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': form.errors.email }"
                                />
                            </div>
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- ── Section: Personal details ───────────────────────── -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Personal Details</h3>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Optional — helps personalise care and reports.</p>
                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">
                        <!-- Gender -->
                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                            <div class="flex gap-2">
                                <button
                                    v-for="g in ['male', 'female', 'other']"
                                    :key="g"
                                    type="button"
                                    @click="form.gender = form.gender === g ? '' : (g as 'male' | 'female' | 'other')"
                                    class="flex-1 rounded-xl border py-2.5 text-sm font-medium capitalize transition"
                                    :class="form.gender === g
                                        ? g === 'male'   ? 'border-sky-400 bg-sky-50 text-sky-700 dark:border-sky-600 dark:bg-sky-900/30 dark:text-sky-300'
                                        : g === 'female' ? 'border-pink-400 bg-pink-50 text-pink-700 dark:border-pink-600 dark:bg-pink-900/30 dark:text-pink-300'
                                        :                  'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800'"
                                >
                                    {{ g }}
                                </button>
                            </div>
                        </div>

                        <!-- Date of birth -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                            <input
                                v-model="form.date_of_birth"
                                type="date"
                                :max="new Date().toISOString().split('T')[0]"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400': form.errors.date_of_birth }"
                            />
                            <p v-if="form.errors.date_of_birth" class="mt-1 text-xs text-red-500">{{ form.errors.date_of_birth }}</p>
                        </div>
                    </div>
                </div>

                <!-- ── Section: Medical background ────────────────────── -->
                <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Medical Background</h3>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Pre-existing conditions, known allergies, and clinical notes.</p>
                    </div>

                    <div class="space-y-5 p-6">
                        <!-- Allergies -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Known Allergies
                            </label>
                            <textarea
                                v-model="form.allergies"
                                rows="2"
                                placeholder="e.g. Penicillin, shellfish, latex…"
                                class="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                            />
                        </div>

                        <!-- Medical history -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Medical History
                            </label>
                            <textarea
                                v-model="form.medical_history"
                                rows="3"
                                placeholder="e.g. Hypertension (2018), appendectomy (2020)…"
                                class="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                            />
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Clinical Notes <span class="text-xs font-normal text-gray-400">(for your reference)</span>
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                placeholder="Any other notes you want to track about this patient…"
                                class="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                            />
                        </div>
                    </div>
                </div>

                <!-- ── Actions ─────────────────────────────────────────── -->
                <div class="flex items-center justify-end gap-3 pb-8">
                    <Link
                        href="/doctor/patients"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        {{ form.processing ? 'Registering…' : 'Register Patient' }}
                    </button>
                </div>

            </form>
        </div>
    </DoctorLayout>
</template>
