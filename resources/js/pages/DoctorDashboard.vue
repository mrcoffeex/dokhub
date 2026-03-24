<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor, Appointment, PageProps } from '@/types';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage<PageProps & {
    doctor: Doctor;
    appointments: Appointment[];
    stats: {
        total_appointments: number;
        completed_appointments: number;
        pending_appointments: number;
        confirmed_appointments: number;
    };
}>();

const doctor = computed(() => page.props.doctor);
const appointments = computed(() => page.props.appointments);
const stats = computed(() => page.props.stats);

function formatDate(dateStr: string) {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(timeStr: string) {
    const [h, m] = timeStr.split(':').map(Number);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

const statusConfig: Record<string, { label: string; bg: string; text: string }> = {
    pending:   { label: 'Pending',   bg: 'bg-amber-100 dark:bg-amber-900/30',   text: 'text-amber-700 dark:text-amber-400' },
    confirmed: { label: 'Confirmed', bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-100 dark:bg-red-900/30',       text: 'text-red-700 dark:text-red-400' },
    completed: { label: 'Completed', bg: 'bg-sky-100 dark:bg-sky-900/30',       text: 'text-sky-700 dark:text-sky-400' },
};
</script>

<template>
    <Head :title="`Dr. ${doctor?.name} — Dashboard`" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Dashboard</h1>
        </template>

        <!-- Welcome banner -->
        <div class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-8 py-7 text-white shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-violet-200">Good day,</p>
                    <h2 class="mt-1 text-2xl font-bold">Dr. {{ doctor?.name }}</h2>
                    <p class="mt-1 text-sm text-violet-200">{{ doctor?.specialization }} &middot; {{ doctor?.location }}</p>
                </div>
                <div class="hidden sm:flex">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-white/30 bg-white/20 text-2xl font-bold text-white">
                        {{ doctor?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid — consistent color system -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total — violet -->
            <div class="rounded-2xl border border-violet-100 bg-white p-6 shadow-sm dark:border-violet-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-900/30">
                        <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">{{ stats?.total_appointments ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">All appointments</p>
            </div>

            <!-- Pending — amber -->
            <div class="rounded-2xl border border-amber-100 bg-white p-6 shadow-sm dark:border-amber-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/30">
                        <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ stats?.pending_appointments ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Awaiting review</p>
            </div>

            <!-- Confirmed — emerald -->
            <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Confirmed</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/30">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats?.confirmed_appointments ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Ready to see</p>
            </div>

            <!-- Completed — sky -->
            <div class="rounded-2xl border border-sky-100 bg-white p-6 shadow-sm dark:border-sky-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-900/30">
                        <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold text-sky-600 dark:text-sky-400">{{ stats?.completed_appointments ?? 0 }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Past sessions</p>
            </div>
        </div>

        <!-- Two-column layout: Profile + Appointments -->
        <div class="grid gap-6 lg:grid-cols-3">

            <!-- Profile Card -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Profile</h2>
                    <Link href="/doctor/profile" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                        Edit
                    </Link>
                </div>

                <!-- Avatar -->
                <div class="mb-6 flex flex-col items-center text-center">
                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-3xl font-bold text-white shadow-md">
                        {{ doctor?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <p class="mt-3 text-base font-bold text-gray-900 dark:text-white">Dr. {{ doctor?.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ doctor?.specialization }}</p>
                </div>

                <div class="space-y-3.5 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <div class="flex justify-between gap-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Qualification</span>
                        <span class="text-right text-sm text-gray-700 dark:text-gray-300">{{ doctor?.qualification ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Experience</span>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ doctor?.experience_years ?? '—' }} yrs</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Consult Fee</span>
                        <span class="text-sm font-semibold text-violet-600 dark:text-violet-400">${{ doctor?.consultation_fee ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Location</span>
                        <span class="text-right text-sm text-gray-700 dark:text-gray-300">{{ doctor?.location ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between gap-2">
                        <span class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Languages</span>
                        <span class="text-right text-sm text-gray-700 dark:text-gray-300">{{ Array.isArray(doctor?.languages) ? doctor.languages.join(', ') : (doctor?.languages ?? '—') }}</span>
                    </div>
                </div>
            </div>

            <!-- Appointments Table -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Recent Appointments</h2>
                    <Link href="/doctor/appointments" class="text-sm font-medium text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300">
                        View all →
                    </Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr
                                v-for="appointment in appointments"
                                :key="appointment.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appointment.patient_name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-600">{{ appointment.patient_phone }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ formatDate(appointment.appointment_date) }}</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-600">{{ formatTime(appointment.appointment_time) }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="max-w-xs truncate text-sm text-gray-600 dark:text-gray-400">{{ appointment.reason }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                        :class="[statusConfig[appointment.status]?.bg, statusConfig[appointment.status]?.text]"
                                    >
                                        {{ statusConfig[appointment.status]?.label }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!appointments?.length">
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-8 w-8 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <p class="text-sm text-gray-400 dark:text-gray-600">No appointments yet</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>
