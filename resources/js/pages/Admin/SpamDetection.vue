<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface FlaggedEntry {
    patient_email: string;
    patient_name: string;
    total_bookings: number;
    bookings_24h: number;
    bookings_7d: number;
    unique_doctors: number;
    cancelled_count: number;
    pending_count: number;
    cancel_rate: number;
    last_booking_at: string;
    risk: 'high' | 'medium' | 'low';
    reasons: string[];
}

const props = defineProps<{
    flagged: FlaggedEntry[];
    stats: { total: number; high: number; medium: number; low: number };
    filter: string;
}>();

const riskConfig = {
    high: {
        label: 'High Risk',
        badge: 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-800/40',
        dot: 'bg-red-500',
        row: 'border-l-4 border-red-400',
    },
    medium: {
        label: 'Medium Risk',
        badge: 'bg-amber-50 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-400 dark:ring-amber-800/40',
        dot: 'bg-amber-500',
        row: 'border-l-4 border-amber-400',
    },
    low: {
        label: 'Low Risk',
        badge: 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-400 dark:ring-yellow-800/40',
        dot: 'bg-yellow-400',
        row: 'border-l-4 border-yellow-300',
    },
} as const;

function setFilter(risk: string) {
    router.get('/admin/spam-detection', { risk }, { preserveState: true, replace: true });
}

function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}
</script>

<template>
    <Head title="Spam Detection" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Booking Spam Detection</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">Flagged visitors with suspicious booking activity</p>
            </div>
        </template>

        <!-- Stats cards -->
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <!-- Total Flagged -->
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Flagged</p>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Unique emails</p>
            </div>
            <!-- High Risk -->
            <div class="rounded-2xl border border-red-100 bg-red-50/50 p-5 shadow-sm dark:border-red-900/30 dark:bg-red-900/10">
                <p class="text-xs font-semibold uppercase tracking-wider text-red-500 dark:text-red-400">High Risk</p>
                <p class="mt-2 text-3xl font-bold text-red-700 dark:text-red-300">{{ stats.high }}</p>
                <p class="mt-1 text-xs text-red-400 dark:text-red-500">Immediate attention</p>
            </div>
            <!-- Medium Risk -->
            <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-900/10">
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-500 dark:text-amber-400">Medium Risk</p>
                <p class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">{{ stats.medium }}</p>
                <p class="mt-1 text-xs text-amber-400 dark:text-amber-500">Worth monitoring</p>
            </div>
            <!-- Low Risk -->
            <div class="rounded-2xl border border-yellow-100 bg-yellow-50/50 p-5 shadow-sm dark:border-yellow-900/30 dark:bg-yellow-900/10">
                <p class="text-xs font-semibold uppercase tracking-wider text-yellow-600 dark:text-yellow-500">Low Risk</p>
                <p class="mt-2 text-3xl font-bold text-yellow-700 dark:text-yellow-300">{{ stats.low }}</p>
                <p class="mt-1 text-xs text-yellow-500 dark:text-yellow-600">Minor irregularities</p>
            </div>
        </div>

        <!-- Detection criteria info -->
        <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50/60 px-5 py-4 dark:border-blue-900/30 dark:bg-blue-900/10">
            <div class="flex items-start gap-3">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-xs font-semibold text-blue-800 dark:text-blue-300">Detection criteria</p>
                    <p class="mt-0.5 text-xs text-blue-600 dark:text-blue-400">
                        Visitors are flagged when they make <span class="font-medium">more than 5 bookings within a 24-hour window</span>.
                        Risk level: <span class="font-medium">Low</span> (6–6), <span class="font-medium">Medium</span> (7–9), <span class="font-medium">High</span> (10+).
                    </p>
                </div>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="mb-4 flex flex-wrap gap-2">
            <button
                v-for="tab in [
                    { key: 'all',    label: 'All', count: stats.total },
                    { key: 'high',   label: 'High Risk', count: stats.high },
                    { key: 'medium', label: 'Medium Risk', count: stats.medium },
                    { key: 'low',    label: 'Low Risk', count: stats.low },
                ]"
                :key="tab.key"
                @click="setFilter(tab.key)"
                class="inline-flex items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-semibold transition-colors"
                :class="filter === tab.key
                    ? 'bg-violet-600 text-white shadow-sm'
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-750'"
            >
                {{ tab.label }}
                <span
                    class="inline-flex h-4 min-w-[1rem] items-center justify-center rounded-full px-1 text-[10px] font-bold"
                    :class="filter === tab.key ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'"
                >
                    {{ tab.count }}
                </span>
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <!-- Empty state -->
            <div v-if="!flagged.length" class="flex flex-col items-center gap-3 px-6 py-20 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 dark:bg-emerald-900/20">
                    <svg class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No suspicious activity detected</p>
                <p class="max-w-xs text-xs text-gray-400 dark:text-gray-500">
                    {{ filter !== 'all' ? 'No entries match this risk filter.' : 'All visitor booking patterns look normal.' }}
                </p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 dark:border-gray-800 dark:bg-gray-800/50">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Visitor</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Risk</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Flags</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">24h</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">7d</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Doctors</th>
                            <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Cancel %</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Last Booking</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr
                            v-for="entry in flagged"
                            :key="entry.patient_email"
                            class="transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-800/30"
                            :class="riskConfig[entry.risk].row"
                        >
                            <!-- Visitor -->
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ entry.patient_name }}</p>
                                <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ entry.patient_email }}</p>
                            </td>

                            <!-- Risk badge -->
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="riskConfig[entry.risk].badge"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="riskConfig[entry.risk].dot"></span>
                                    {{ riskConfig[entry.risk].label }}
                                </span>
                            </td>

                            <!-- Flags/reasons -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <span
                                        v-for="reason in entry.reasons"
                                        :key="reason"
                                        class="inline-block rounded-lg bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                    >
                                        {{ reason }}
                                    </span>
                                </div>
                            </td>

                            <!-- Bookings 24h -->
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-lg px-2 text-xs font-bold"
                                    :class="entry.bookings_24h >= 5
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'
                                        : entry.bookings_24h >= 3
                                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                >
                                    {{ entry.bookings_24h }}
                                </span>
                            </td>

                            <!-- Bookings 7d -->
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ entry.bookings_7d }}</span>
                            </td>

                            <!-- Total bookings -->
                            <td class="px-5 py-4 text-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ entry.total_bookings }}</span>
                            </td>

                            <!-- Unique doctors -->
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-lg px-2 text-xs font-bold"
                                    :class="entry.unique_doctors >= 6
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'
                                        : entry.unique_doctors >= 4
                                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                >
                                    {{ entry.unique_doctors }}
                                </span>
                            </td>

                            <!-- Cancel rate -->
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex h-7 min-w-[2.5rem] items-center justify-center rounded-lg px-2 text-xs font-bold"
                                    :class="entry.cancel_rate >= 80
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'
                                        : entry.cancel_rate >= 60
                                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
                                            : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                >
                                    {{ entry.cancel_rate }}%
                                </span>
                            </td>

                            <!-- Last booking -->
                            <td class="px-5 py-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(entry.last_booking_at) }}</p>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4">
                                <Link
                                    :href="`/admin/appointments?search=${encodeURIComponent(entry.patient_email)}`"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 active:scale-95 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    View bookings
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
