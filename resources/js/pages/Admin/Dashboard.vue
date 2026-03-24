<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Appointment, PageProps } from '@/types';

defineProps<{
    stats: {
        total_doctors: number;
        approved_doctors: number;
        pending_doctors: number;
        total_appointments: number;
        today_appointments: number;
        pending_appointments: number;
    };
    recent_appointments: Appointment[];
}>();

const page = usePage<PageProps>();
const adminName = computed(() => (page.props.auth as any)?.user?.name?.split(' ')[0] ?? 'Admin');

const today = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

function formatDate(dateStr: string) {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatTime(timeStr: string) {
    const [h, m] = timeStr.split(':').map(Number);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

const statusConfig: Record<string, { label: string; bg: string; text: string; dot: string }> = {
    pending:   { label: 'Pending',   bg: 'bg-amber-50 dark:bg-amber-900/20',   text: 'text-amber-700 dark:text-amber-400',   dot: 'bg-amber-500' },
    confirmed: { label: 'Confirmed', bg: 'bg-emerald-50 dark:bg-emerald-900/20', text: 'text-emerald-700 dark:text-emerald-400', dot: 'bg-emerald-500' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-50 dark:bg-red-900/20',       text: 'text-red-600 dark:text-red-400',        dot: 'bg-red-500' },
    completed: { label: 'Completed', bg: 'bg-sky-50 dark:bg-sky-900/20',       text: 'text-sky-600 dark:text-sky-400',        dot: 'bg-sky-500' },
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Good {{ new Date().getHours() < 12 ? 'morning' : new Date().getHours() < 17 ? 'afternoon' : 'evening' }}, {{ adminName }} 👋
                </h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ today }}</p>
            </div>
        </template>

        <!-- Pending doctors alert banner -->
        <div
            v-if="stats.pending_doctors > 0"
            class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 dark:border-amber-800/40 dark:bg-amber-900/10"
        >
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        {{ stats.pending_doctors }} doctor{{ stats.pending_doctors > 1 ? 's' : '' }} awaiting approval
                    </p>
                    <p class="text-xs text-amber-600 dark:text-amber-500">Review their applications and approve or reject them.</p>
                </div>
            </div>
            <Link
                href="/admin/doctors?status=pending"
                class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-amber-600 active:scale-95"
            >
                Review now
            </Link>
        </div>

        <!-- Stats grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

            <!-- Total Doctors -->
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl bg-violet-500"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Doctors</p>
                        <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ stats.total_doctors }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 dark:bg-violet-900/30">
                        <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        {{ stats.approved_doctors }} active
                    </span>
                    <span v-if="stats.pending_doctors > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ stats.pending_doctors }} pending
                    </span>
                </div>
                <Link href="/admin/doctors" class="absolute inset-0" aria-label="View doctors" />
            </div>

            <!-- Total Appointments -->
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl bg-sky-500"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Appointments</p>
                        <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ stats.total_appointments }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-50 dark:bg-sky-900/30">
                        <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span v-if="stats.pending_appointments > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                        {{ stats.pending_appointments }} pending
                    </span>
                    <span v-else class="text-xs text-gray-400 dark:text-gray-500">No pending reviews</span>
                </div>
                <Link href="/admin/appointments" class="absolute inset-0" aria-label="View appointments" />
            </div>

            <!-- Today's Appointments -->
            <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="absolute left-0 top-0 h-full w-1 rounded-l-2xl bg-emerald-500"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Today's Appointments</p>
                        <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ stats.today_appointments }}</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-900/30">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-4 text-xs text-gray-400 dark:text-gray-500">Scheduled for today</p>
                <Link :href="`/admin/appointments?date=${new Date().toISOString().slice(0,10)}`" class="absolute inset-0" aria-label="View today's appointments" />
            </div>
        </div>

        <!-- Quick actions -->
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Quick actions:</span>
            <Link href="/admin/doctors/create" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Doctor
            </Link>
            <Link href="/admin/appointments" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-750">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                View Appointments
            </Link>
        </div>

        <!-- Recent appointments -->
        <div class="mt-8 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Appointments</h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Latest activity across the platform</p>
                </div>
                <Link href="/admin/appointments" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 transition hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300">
                    View all
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Reference</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Patient</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Doctor</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Date & Time</th>
                            <th class="px-6 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr v-for="apt in recent_appointments" :key="apt.id" class="transition-colors hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                            <td class="px-6 py-4">
                                <span class="rounded-lg bg-gray-100 px-2 py-1 font-mono text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    {{ apt.reference }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ apt.patient_name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ apt.patient_email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-bold text-white">
                                        {{ apt.doctor?.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">Dr. {{ apt.doctor?.name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ apt.doctor?.specialization }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatDate(apt.appointment_date) }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatTime(apt.appointment_time) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="[statusConfig[apt.status]?.bg, statusConfig[apt.status]?.text]"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="statusConfig[apt.status]?.dot"></span>
                                    {{ statusConfig[apt.status]?.label }}
                                </span>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="!recent_appointments.length">
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="mx-auto flex w-fit flex-col items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                                        <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No appointments yet</p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Appointments will appear here once patients start booking</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
