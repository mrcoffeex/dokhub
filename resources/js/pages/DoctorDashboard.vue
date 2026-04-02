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
        cancelled_appointments: number;
    };
}>();

const doctor      = computed(() => page.props.doctor);
const appointments = computed(() => page.props.appointments);
const stats       = computed(() => page.props.stats);

const hasAvatar = computed(() => {
    const url = doctor.value?.avatar_url;
    return !!url && url.trim() !== '';
});

const completionRate = computed(() => {
    const total = stats.value?.total_appointments ?? 0;
    const done  = stats.value?.completed_appointments ?? 0;
    return total > 0 ? Math.round((done / total) * 100) : 0;
});

const todayLabel = computed(() =>
    new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })
);

function segmentWidth(key: 'completed' | 'confirmed' | 'pending' | 'cancelled'): string {
    const total = stats.value?.total_appointments ?? 0;
    if (!total) return '0%';
    const map = {
        completed: stats.value?.completed_appointments ?? 0,
        confirmed: stats.value?.confirmed_appointments ?? 0,
        pending:   stats.value?.pending_appointments   ?? 0,
        cancelled: stats.value?.cancelled_appointments ?? 0,
    };
    return `${Math.round((map[key] / total) * 100)}%`;
}

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
    pending:   { label: 'Pending',   bg: 'bg-amber-100 dark:bg-amber-900/30',    text: 'text-amber-700 dark:text-amber-400' },
    confirmed: { label: 'Confirmed', bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-100 dark:bg-red-900/30',        text: 'text-red-700 dark:text-red-400' },
    completed: { label: 'Completed', bg: 'bg-sky-100 dark:bg-sky-900/30',        text: 'text-sky-700 dark:text-sky-400' },
};
</script>

<template>
    <Head :title="`Dr. ${doctor?.name} — Dashboard`" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Dashboard</h1>
        </template>

        <!-- Welcome Banner -->
        <div class="mb-6 overflow-hidden rounded-2xl bg-gradient-to-br from-orange-600 to-indigo-700 px-5 py-5 sm:px-7 sm:py-6 text-white shadow-md">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <!-- Doctor identity -->
                <div class="flex items-center gap-4">
                    <div class="relative shrink-0">
                        <img v-if="hasAvatar" :src="doctor?.avatar_url" alt="avatar" class="h-16 w-16 rounded-full object-cover ring-2 ring-white/40 shadow-md" />
                        <div v-else class="flex h-16 w-16 items-center justify-center rounded-full bg-white/20 text-2xl font-bold ring-2 ring-white/30">
                            {{ doctor?.name?.charAt(0)?.toUpperCase() }}
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-indigo-700 bg-green-400"></span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-orange-200">Welcome back</p>
                        <h2 class="text-xl font-bold leading-tight">Dr. {{ doctor?.name }}</h2>
                        <p class="mt-0.5 text-sm text-orange-200">
                            {{ doctor?.specialization?.join(', ') }}
                            <span v-if="doctor?.location">&nbsp;·&nbsp;{{ doctor.location }}</span>
                            <span v-if="doctor?.experience_years">&nbsp;·&nbsp;{{ doctor.experience_years }} yrs exp</span>
                        </p>
                    </div>
                </div>
                <!-- Quick KPIs -->
                <div class="flex flex-col gap-3 sm:items-end">
                    <p class="text-xs font-medium text-orange-200">{{ todayLabel }}</p>
                    <div class="flex flex-wrap gap-2">
                        <div class="rounded-xl bg-white/15 px-4 py-2.5 text-center backdrop-blur-sm">
                            <p class="text-xl font-bold">{{ stats?.confirmed_appointments ?? 0 }}</p>
                            <p class="text-[11px] text-orange-200">Upcoming</p>
                        </div>
                        <div class="rounded-xl bg-amber-400/25 px-4 py-2.5 text-center backdrop-blur-sm">
                            <p class="text-xl font-bold text-amber-100">{{ stats?.pending_appointments ?? 0 }}</p>
                            <p class="text-[11px] text-orange-200">Pending</p>
                        </div>
                        <div class="rounded-xl bg-white/15 px-4 py-2.5 text-center backdrop-blur-sm">
                            <p class="text-xl font-bold">{{ completionRate }}%</p>
                            <p class="text-[11px] text-orange-200">Done</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid (5 cards) -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
            <!-- Total -->
            <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm dark:border-orange-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Total</p>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/30">
                        <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-gray-900 dark:text-white">{{ stats?.total_appointments ?? 0 }}</p>
                <div class="mt-2.5 h-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-orange-400 transition-all duration-700" :style="{ width: `${completionRate}%` }"></div>
                </div>
                <p class="mt-1 text-[11px] text-gray-400 dark:text-gray-600">{{ completionRate }}% completion</p>
            </div>

            <!-- Pending -->
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm dark:border-amber-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Pending</p>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <svg class="h-4 w-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-amber-600 dark:text-amber-400">{{ stats?.pending_appointments ?? 0 }}</p>
                <p v-if="(stats?.pending_appointments ?? 0) > 0" class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                    ● Needs action
                </p>
                <p v-else class="mt-2 text-[11px] text-gray-400 dark:text-gray-600">Awaiting review</p>
            </div>

            <!-- Confirmed -->
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Confirmed</p>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats?.confirmed_appointments ?? 0 }}</p>
                <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-600">Ready to see</p>
            </div>

            <!-- Completed -->
            <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm dark:border-sky-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Completed</p>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 dark:bg-sky-900/30">
                        <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-sky-600 dark:text-sky-400">{{ stats?.completed_appointments ?? 0 }}</p>
                <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-600">Past sessions</p>
            </div>

            <!-- Cancelled -->
            <div class="rounded-2xl border border-red-50 bg-white p-5 shadow-sm dark:border-red-900/20 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Cancelled</p>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30">
                        <svg class="h-4 w-4 text-red-500 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold text-red-500 dark:text-red-400">{{ stats?.cancelled_appointments ?? 0 }}</p>
                <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-600">Not attended</p>
            </div>
        </div>

        <!-- Analytics Row -->
        <div class="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Status breakdown stacked bar -->
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:col-span-2 lg:col-span-2">
                <h3 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointment Overview</h3>
                <div class="flex h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="bg-sky-400 transition-all duration-700" :style="{ width: segmentWidth('completed') }" :title="`Completed: ${stats?.completed_appointments}`"></div>
                    <div class="bg-emerald-400 transition-all duration-700" :style="{ width: segmentWidth('confirmed') }" :title="`Confirmed: ${stats?.confirmed_appointments}`"></div>
                    <div class="bg-amber-400 transition-all duration-700" :style="{ width: segmentWidth('pending') }" :title="`Pending: ${stats?.pending_appointments}`"></div>
                    <div class="bg-red-300 transition-all duration-700" :style="{ width: segmentWidth('cancelled') }" :title="`Cancelled: ${stats?.cancelled_appointments}`"></div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-x-6 gap-y-2.5 sm:grid-cols-4">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-sky-400"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Completed</span>
                        <span class="ml-auto text-xs font-semibold text-gray-700 dark:text-gray-300">{{ stats?.completed_appointments ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-400"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Confirmed</span>
                        <span class="ml-auto text-xs font-semibold text-gray-700 dark:text-gray-300">{{ stats?.confirmed_appointments ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Pending</span>
                        <span class="ml-auto text-xs font-semibold text-gray-700 dark:text-gray-300">{{ stats?.pending_appointments ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-300"></span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Cancelled</span>
                        <span class="ml-auto text-xs font-semibold text-gray-700 dark:text-gray-300">{{ stats?.cancelled_appointments ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Completion rate SVG donut -->
            <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 self-start text-sm font-semibold text-gray-700 dark:text-gray-200">Completion Rate</h3>
                <div class="relative">
                    <svg class="h-28 w-28 -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" stroke-width="9" class="stroke-gray-100 dark:stroke-gray-800" />
                        <circle
                            cx="50" cy="50" r="40" fill="none" stroke-width="9"
                            stroke-linecap="round"
                            class="stroke-sky-400 transition-all duration-700"
                            :stroke-dasharray="`${completionRate * 2.5133} 251.33`"
                        />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ completionRate }}%</span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">completed</span>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-500">
                    {{ stats?.completed_appointments ?? 0 }} of {{ stats?.total_appointments ?? 0 }} total
                </p>
            </div>

            <!-- Consultation Fee -->
            <div class="flex flex-col justify-between rounded-2xl border border-teal-100 bg-white p-5 shadow-sm dark:border-teal-900/30 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Consultation Fee</h3>
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-900/30">
                        <svg class="h-4 w-4 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                </div>

                <div class="mt-4 flex flex-col items-center justify-center flex-1 py-3">
                    <div v-if="doctor?.consultation_fee" class="text-center">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-teal-500 dark:text-teal-400">Rate</p>
                        <p class="mt-1 text-4xl font-bold text-teal-600 dark:text-teal-400">
                            ₱<span>{{ Number(doctor.consultation_fee).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) }}</span>
                        </p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">per session</p>
                    </div>
                    <div v-else class="text-center">
                        <svg class="mx-auto mb-2 h-8 w-8 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-400 dark:text-gray-600">Not set</p>
                    </div>
                </div>

                <Link href="/doctor/profile" class="mt-3 flex items-center justify-center gap-1.5 rounded-xl border border-teal-100 bg-teal-50/60 py-2 text-xs font-semibold text-teal-700 transition hover:bg-teal-100 dark:border-teal-900/30 dark:bg-teal-900/20 dark:text-teal-400 dark:hover:bg-teal-900/30">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Update fee
                </Link>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="grid gap-6">

            <!-- Appointments Table -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Recent Appointments</h2>
                    <Link href="/doctor/appointments" class="text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
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
