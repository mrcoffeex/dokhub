<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Patient, Diagnosis, Appointment, Doctor } from '@/types';
import { toast } from 'vue-sonner';

const props = defineProps<{
    patient: Patient;
    appointments: Appointment[];
    doctor: Doctor;
}>();

// ---- Tabs ----
type Tab = 'overview' | 'diagnoses' | 'prescriptions' | 'appointments';
const activeTab = ref<Tab>('overview');

// ---- Patient edit ----
const editingInfo = ref(false);
const infoForm = useForm({
    name:            props.patient.name ?? '',
    email:           props.patient.email ?? '',
    phone:           props.patient.phone ?? '',
    gender:          props.patient.gender ?? '',
    date_of_birth:   props.patient.date_of_birth ?? '',
    allergies:       props.patient.allergies ?? '',
    medical_history: props.patient.medical_history ?? '',
    notes:           props.patient.notes ?? '',
});
function submitInfo() {
    infoForm.patch(`/doctor/patients/${props.patient.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingInfo.value = false;
            toast.success('Patient info updated', {
                description: 'Changes have been saved.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not save changes', {
                description: 'Please review the fields and try again.',
                duration: 5000,
            });
        },
    });
}

// ---- Diagnosis form ----
const showDiagForm = ref(false);
const editingDiagId = ref<number | null>(null);
const diagForm = useForm({
    title:          '',
    symptoms:       '',
    description:    '',
    treatment:      '',
    follow_up_date: '',
    appointment_id: null as number | null,
});

function openNewDiag() {
    diagForm.reset();
    editingDiagId.value = null;
    showDiagForm.value = true;
}

function openEditDiag(d: Diagnosis) {
    diagForm.title          = d.title;
    diagForm.symptoms       = d.symptoms ?? '';
    diagForm.description    = d.description ?? '';
    diagForm.treatment      = d.treatment ?? '';
    diagForm.follow_up_date = d.follow_up_date ?? '';
    diagForm.appointment_id = d.appointment_id;
    editingDiagId.value     = d.id;
    showDiagForm.value      = true;
    activeTab.value         = 'diagnoses';
}

function submitDiag() {
    if (editingDiagId.value) {
        diagForm.put(`/doctor/patients/${props.patient.id}/diagnoses/${editingDiagId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDiagForm.value = false;
                diagForm.reset();
                editingDiagId.value = null;
                toast.success('Diagnosis updated', { duration: 4000 });
            },
            onError: () => {
                toast.error('Could not update diagnosis', { duration: 5000 });
            },
        });
    } else {
        diagForm.post(`/doctor/patients/${props.patient.id}/diagnoses`, {
            preserveScroll: true,
            onSuccess: () => {
                showDiagForm.value = false;
                diagForm.reset();
                toast.success('Diagnosis added', { duration: 4000 });
            },
            onError: () => {
                toast.error('Could not add diagnosis', { duration: 5000 });
            },
        });
    }
}

function deleteDiag(d: Diagnosis) {
    if (!confirm(`Delete diagnosis "${d.title}"?`)) return;
    router.delete(`/doctor/patients/${props.patient.id}/diagnoses/${d.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Diagnosis deleted', { duration: 3000 });
        },
        onError: () => {
            toast.error('Could not delete diagnosis', { duration: 4000 });
        },
    });
}

// ---- Helpers ----
function formatDate(d: string | null, time = false): string {
    if (!d) return '—';
    const opts: Intl.DateTimeFormatOptions = { month: 'short', day: 'numeric', year: 'numeric' };
    if (time) opts.hour = '2-digit', opts.minute = '2-digit';
    return new Date(d.includes('T') ? d : d + 'T00:00:00').toLocaleDateString('en-US', opts);
}
function initials(name: string): string {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}
const statusConfig: Record<string, { label: string; cls: string }> = {
    pending:   { label: 'Pending',   cls: 'bg-amber-100 text-amber-700' },
    confirmed: { label: 'Confirmed', cls: 'bg-emerald-100 text-emerald-700' },
    cancelled: { label: 'Cancelled', cls: 'bg-red-100 text-red-700' },
    completed: { label: 'Completed', cls: 'bg-sky-100 text-sky-700' },
};
const confirmedAppts = computed(() => props.appointments.filter(a => ['confirmed', 'completed'].includes(a.status)));
</script>

<template>
    <Head :title="patient.name" />
    <DoctorLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <Link href="/doctor/patients" class="text-gray-500 hover:text-violet-600 dark:text-gray-400 dark:hover:text-violet-400 transition-colors">
                    Patients
                </Link>
                <svg class="h-4 w-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-semibold text-gray-900 dark:text-white">{{ patient.name }}</span>
            </div>
        </template>

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <!-- LEFT: Patient card -->
            <div class="shrink-0 lg:w-72">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <!-- Avatar + Name -->
                    <div class="flex flex-col items-center gap-3 border-b border-gray-100 p-6 dark:border-gray-800">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-xl font-bold text-white shadow">
                            {{ initials(patient.name) }}
                        </div>
                        <div class="text-center">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">{{ patient.name }}</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ patient.email }}</p>
                        </div>
                        <button @click="editingInfo = !editingInfo" class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-violet-300 hover:text-violet-700 dark:border-gray-700 dark:text-gray-400 dark:hover:text-violet-400">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ editingInfo ? 'Cancel' : 'Edit Info' }}
                        </button>
                    </div>

                    <!-- Edit form -->
                    <form v-if="editingInfo" @submit.prevent="submitInfo" class="border-b border-gray-100 p-4 dark:border-gray-800 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                v-model="infoForm.name"
                                type="text"
                                required
                                :class="infoForm.errors.name
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 focus:ring-1 focus:ring-violet-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.name" class="mt-1 text-xs text-red-500">{{ infoForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                            <input
                                v-model="infoForm.email"
                                type="email"
                                :class="infoForm.errors.email
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 focus:ring-1 focus:ring-violet-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.email" class="mt-1 text-xs text-red-500">{{ infoForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                            <input
                                v-model="infoForm.phone"
                                type="tel"
                                :class="infoForm.errors.phone
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 focus:ring-1 focus:ring-violet-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.phone" class="mt-1 text-xs text-red-500">{{ infoForm.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Gender</label>
                            <select
                                v-model="infoForm.gender"
                                :class="infoForm.errors.gender
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            >
                                <option value="">— Select —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p v-if="infoForm.errors.gender" class="mt-1 text-xs text-red-500">{{ infoForm.errors.gender }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Date of Birth</label>
                            <input
                                v-model="infoForm.date_of_birth"
                                type="date"
                                :class="infoForm.errors.date_of_birth
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.date_of_birth" class="mt-1 text-xs text-red-500">{{ infoForm.errors.date_of_birth }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Allergies</label>
                            <textarea
                                v-model="infoForm.allergies"
                                rows="2"
                                :class="infoForm.errors.allergies
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.allergies" class="mt-1 text-xs text-red-500">{{ infoForm.errors.allergies }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Medical History</label>
                            <textarea
                                v-model="infoForm.medical_history"
                                rows="3"
                                :class="infoForm.errors.medical_history
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.medical_history" class="mt-1 text-xs text-red-500">{{ infoForm.errors.medical_history }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Internal Notes</label>
                            <textarea
                                v-model="infoForm.notes"
                                rows="2"
                                :class="infoForm.errors.notes
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.notes" class="mt-1 text-xs text-red-500">{{ infoForm.errors.notes }}</p>
                        </div>
                        <button type="submit" :disabled="infoForm.processing" class="w-full rounded-xl bg-violet-600 py-2 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-50">
                            Save Changes
                        </button>
                    </form>

                    <!-- Info display -->
                    <div v-else class="space-y-3 p-4 text-sm">
                        <div v-if="patient.phone" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ patient.phone }}
                        </div>
                        <div v-if="patient.gender" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 capitalize">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ patient.gender }}
                            <span v-if="patient.age" class="text-gray-400">· {{ patient.age }} yrs</span>
                        </div>
                        <div v-if="patient.date_of_birth" class="text-xs text-gray-500 dark:text-gray-400 pl-6">
                            DOB: {{ formatDate(patient.date_of_birth) }}
                        </div>
                        <div v-if="patient.allergies" class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                            <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1">⚠ Allergies</p>
                            <p class="text-xs text-red-700 dark:text-red-300 whitespace-pre-wrap">{{ patient.allergies }}</p>
                        </div>
                        <div v-if="patient.medical_history" class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Medical History</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ patient.medical_history }}</p>
                        </div>
                        <div v-if="patient.notes" class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Notes</p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 whitespace-pre-wrap">{{ patient.notes }}</p>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-600 pt-1 border-t border-gray-100 dark:border-gray-800">
                            Patient since {{ formatDate(patient.first_seen_at) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Tabs -->
            <div class="flex-1 min-w-0">
                <!-- Tab nav -->
                <div class="mb-4 flex gap-1 rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
                    <button
                        v-for="tab in (['overview', 'diagnoses', 'prescriptions', 'appointments'] as const)"
                        :key="tab"
                        @click="activeTab = tab"
                        class="flex-1 rounded-lg py-2 text-xs font-semibold capitalize transition-all"
                        :class="activeTab === tab
                            ? 'bg-white shadow-sm text-violet-700 dark:bg-gray-900 dark:text-violet-300'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    >
                        {{ tab }}
                        <span v-if="tab === 'diagnoses'" class="ml-1 text-gray-400">({{ patient.diagnoses?.length ?? 0 }})</span>
                        <span v-if="tab === 'prescriptions'" class="ml-1 text-gray-400">({{ patient.prescriptions?.length ?? 0 }})</span>
                    </button>
                </div>

                <!-- OVERVIEW TAB -->
                <div v-if="activeTab === 'overview'" class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm dark:border-violet-900/30 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Visits</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ appointments.length }}</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm dark:border-indigo-900/30 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Diagnoses</p>
                        <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ patient.diagnoses?.length ?? 0 }}</p>
                        <button @click="activeTab = 'diagnoses'" class="mt-1 text-xs text-indigo-500 hover:underline">View all →</button>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Prescriptions</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ patient.prescriptions?.length ?? 0 }}</p>
                        <button @click="activeTab = 'prescriptions'" class="mt-1 text-xs text-emerald-500 hover:underline">View all →</button>
                    </div>

                    <!-- Quick actions -->
                    <div class="sm:col-span-3 flex flex-wrap gap-3">
                        <button
                            @click="openNewDiag(); activeTab = 'diagnoses'"
                            class="flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Diagnosis
                        </button>
                        <Link
                            :href="`/doctor/patients/${patient.id}/prescriptions/create`"
                            class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Write Prescription
                        </Link>
                    </div>

                    <!-- Recent diagnosis -->
                    <div v-if="(patient.diagnoses?.length ?? 0) > 0" class="sm:col-span-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <p class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Latest Diagnosis</p>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                            <div class="flex items-start justify-between">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ patient.diagnoses![0].title }}</p>
                                <span class="text-xs text-gray-400">{{ formatDate(patient.diagnoses![0].created_at) }}</span>
                            </div>
                            <p v-if="patient.diagnoses![0].symptoms" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                <span class="font-medium text-gray-600 dark:text-gray-300">Symptoms:</span> {{ patient.diagnoses![0].symptoms }}
                            </p>
                            <p v-if="patient.diagnoses![0].treatment" class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                <span class="font-medium text-gray-600 dark:text-gray-300">Treatment:</span> {{ patient.diagnoses![0].treatment }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- DIAGNOSES TAB -->
                <div v-else-if="activeTab === 'diagnoses'">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Diagnosis History</p>
                        <button @click="openNewDiag" class="flex items-center gap-1.5 rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white hover:bg-violet-700 transition">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Diagnosis
                        </button>
                    </div>

                    <!-- Diagnosis form -->
                    <form v-if="showDiagForm" @submit.prevent="submitDiag" class="mb-4 rounded-2xl border border-violet-200 bg-violet-50 p-5 dark:border-violet-800 dark:bg-violet-900/20 space-y-3">
                        <h3 class="text-sm font-semibold text-violet-800 dark:text-violet-200">{{ editingDiagId ? 'Edit Diagnosis' : 'New Diagnosis' }}</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Diagnosis Title <span class="text-red-500">*</span></label>
                            <input
                                v-model="diagForm.title"
                                type="text"
                                required
                                placeholder="e.g. Type 2 Diabetes, Acute Pharyngitis…"
                                :class="diagForm.errors.title
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 focus:ring-1 focus:ring-violet-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="diagForm.errors.title" class="mt-1 text-xs text-red-500">{{ diagForm.errors.title }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Symptoms</label>
                                <textarea
                                    v-model="diagForm.symptoms"
                                    rows="3"
                                    placeholder="Describe symptoms…"
                                    :class="diagForm.errors.symptoms
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                    class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                />
                                <p v-if="diagForm.errors.symptoms" class="mt-1 text-xs text-red-500">{{ diagForm.errors.symptoms }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Clinical Notes</label>
                                <textarea
                                    v-model="diagForm.description"
                                    rows="3"
                                    placeholder="Detailed clinical notes…"
                                    :class="diagForm.errors.description
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                    class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                />
                                <p v-if="diagForm.errors.description" class="mt-1 text-xs text-red-500">{{ diagForm.errors.description }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Treatment Plan</label>
                            <textarea
                                v-model="diagForm.treatment"
                                rows="2"
                                placeholder="Treatment plan, medications, lifestyle advice…"
                                :class="diagForm.errors.treatment
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="diagForm.errors.treatment" class="mt-1 text-xs text-red-500">{{ diagForm.errors.treatment }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Follow-up Date</label>
                                <input
                                    v-model="diagForm.follow_up_date"
                                    type="date"
                                    :class="diagForm.errors.follow_up_date
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                />
                                <p v-if="diagForm.errors.follow_up_date" class="mt-1 text-xs text-red-500">{{ diagForm.errors.follow_up_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Linked Appointment</label>
                                <select
                                    v-model="diagForm.appointment_id"
                                    :class="diagForm.errors.appointment_id
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-violet-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                >
                                    <option :value="null">— None —</option>
                                    <option v-for="a in confirmedAppts" :key="a.id" :value="a.id">
                                        {{ a.reference }} — {{ formatDate(a.appointment_date) }}
                                    </option>
                                </select>
                                <p v-if="diagForm.errors.appointment_id" class="mt-1 text-xs text-red-500">{{ diagForm.errors.appointment_id }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" :disabled="diagForm.processing" class="rounded-xl bg-violet-600 px-5 py-2 text-sm font-semibold text-white hover:bg-violet-700 disabled:opacity-50 transition">
                                {{ editingDiagId ? 'Update' : 'Save Diagnosis' }}
                            </button>
                            <button type="button" @click="showDiagForm = false" class="rounded-xl border border-gray-200 px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 transition">
                                Cancel
                            </button>
                        </div>
                    </form>

                    <!-- Diagnosis list -->
                    <div v-if="(patient.diagnoses?.length ?? 0) === 0 && !showDiagForm" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">No diagnoses recorded yet</p>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="diag in patient.diagnoses"
                            :key="diag.id"
                            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ diag.title }}</h3>
                                        <span v-if="diag.follow_up_date" class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                            Follow-up: {{ formatDate(diag.follow_up_date) }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ formatDate(diag.created_at) }}
                                        <span v-if="diag.appointment"> · {{ diag.appointment.reference }}</span>
                                    </p>
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    <Link
                                        :href="`/doctor/patients/${patient.id}/prescriptions/create?diagnosis_id=${diag.id}`"
                                        class="flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        Rx
                                    </Link>
                                    <button @click="openEditDiag(diag)" class="rounded-lg border border-gray-200 p-1.5 text-gray-400 hover:border-violet-300 hover:text-violet-600 transition dark:border-gray-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="deleteDiag(diag)" class="rounded-lg border border-gray-200 p-1.5 text-gray-400 hover:border-red-300 hover:text-red-500 transition dark:border-gray-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2 sm:grid-cols-2 text-xs">
                                <div v-if="diag.symptoms" class="rounded-lg bg-orange-50 p-3 dark:bg-orange-900/20">
                                    <p class="font-semibold text-orange-700 dark:text-orange-400 mb-1">Symptoms</p>
                                    <p class="text-orange-700 dark:text-orange-300 whitespace-pre-wrap">{{ diag.symptoms }}</p>
                                </div>
                                <div v-if="diag.description" class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                                    <p class="font-semibold text-blue-700 dark:text-blue-400 mb-1">Clinical Notes</p>
                                    <p class="text-blue-700 dark:text-blue-300 whitespace-pre-wrap">{{ diag.description }}</p>
                                </div>
                                <div v-if="diag.treatment" class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20 sm:col-span-2">
                                    <p class="font-semibold text-green-700 dark:text-green-400 mb-1">Treatment</p>
                                    <p class="text-green-700 dark:text-green-300 whitespace-pre-wrap">{{ diag.treatment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRESCRIPTIONS TAB -->
                <div v-else-if="activeTab === 'prescriptions'">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Prescriptions</p>
                        <Link
                            :href="`/doctor/patients/${patient.id}/prescriptions/create`"
                            class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Prescription
                        </Link>
                    </div>

                    <div v-if="(patient.prescriptions?.length ?? 0) === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">No prescriptions yet</p>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="rx in patient.prescriptions"
                            :key="rx.id"
                            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ rx.reference }}</p>
                                    <p class="text-xs text-gray-400">{{ formatDate(rx.created_at) }}
                                        <span v-if="rx.diagnosis"> · {{ rx.diagnosis.title }}</span>
                                    </p>
                                </div>
                                <Link
                                    :href="`/doctor/prescriptions/${rx.id}`"
                                    class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-violet-300 hover:text-violet-600 transition dark:border-gray-700 dark:text-gray-400"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    View &amp; Print
                                </Link>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span
                                    v-for="med in rx.medications"
                                    :key="med.name"
                                    class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                >
                                    {{ med.name }} {{ med.dosage }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- APPOINTMENTS TAB -->
                <div v-else-if="activeTab === 'appointments'">
                    <p class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointment History</p>
                    <div v-if="appointments.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <p class="text-sm text-gray-500">No appointments found.</p>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="appt in appointments"
                            :key="appt.id"
                            class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div class="shrink-0 text-center">
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ new Date(appt.appointment_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short' }) }}</p>
                                <p class="text-xl font-bold text-gray-800 dark:text-gray-100 leading-tight">{{ new Date(appt.appointment_date + 'T00:00:00').getDate() }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ appt.reference }}</p>
                                <p class="text-xs text-gray-400">{{ appt.appointment_time?.slice(0, 5) }} · {{ appt.reason ?? 'No reason specified' }}</p>
                            </div>
                            <span :class="statusConfig[appt.status]?.cls" class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                {{ statusConfig[appt.status]?.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>
