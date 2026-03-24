<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor, Appointment } from '@/types';
import { ref, watch } from 'vue';

const props = defineProps<{
    doctor: Doctor;
    appointments: {
        data: Appointment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        links: { url: string | null; label: string; active: boolean }[];
    };
    filters: { search?: string; status?: string; date?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');
const date   = ref(props.filters.date ?? '');

let debounceTimer: ReturnType<typeof setTimeout>;

function applyFilters() {
    router.get('/doctor/appointments', {
        search: search.value || undefined,
        status: status.value !== 'all' ? status.value : undefined,
        date:   date.value || undefined,
    }, { preserveState: true, replace: true });
}

watch(search, () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilters, 400);
});

watch([status, date], applyFilters);

function clearFilters() {
    search.value = '';
    status.value = 'all';
    date.value = '';
    applyFilters();
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
    pending:   { label: 'Pending',   bg: 'bg-amber-100 dark:bg-amber-900/30',   text: 'text-amber-700 dark:text-amber-400' },
    confirmed: { label: 'Confirmed', bg: 'bg-emerald-100 dark:bg-emerald-900/30', text: 'text-emerald-700 dark:text-emerald-400' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-100 dark:bg-red-900/30',       text: 'text-red-700 dark:text-red-400' },
    completed: { label: 'Completed', bg: 'bg-sky-100 dark:bg-sky-900/30',       text: 'text-sky-700 dark:text-sky-400' },
};

const hasActiveFilters = () => search.value || status.value !== 'all' || date.value;
</script>

<template>
    <Head title="My Appointments" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">My Appointments</h1>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <!-- Search -->
            <div class="relative flex-1" style="min-width: 220px;">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search patient, email, reference…"
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                />
            </div>

            <!-- Status filter -->
            <select
                v-model="status"
                class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:focus:border-violet-500"
            >
                <option value="all">All statuses</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <!-- Date filter -->
            <input
                v-model="date"
                type="date"
                class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:focus:border-violet-500"
            />

            <!-- Clear -->
            <button
                v-if="hasActiveFilters()"
                @click="clearFilters"
                class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
            >
                Clear
            </button>

            <span class="ml-auto text-sm text-gray-400 dark:text-gray-600">{{ appointments.total }} total</span>
        </div>

        <!-- Table -->
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-800/50">
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr
                            v-for="appt in appointments.data"
                            :key="appt.id"
                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                        >
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs text-gray-500 dark:text-gray-500">{{ appt.reference }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ appt.patient_name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-600">{{ appt.patient_email }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-600">{{ appt.patient_phone }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ formatDate(appt.appointment_date) }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-600">{{ formatTime(appt.appointment_time) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="max-w-xs truncate text-sm text-gray-600 dark:text-gray-400">{{ appt.reason }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="[statusConfig[appt.status]?.bg, statusConfig[appt.status]?.text]"
                                >
                                    {{ statusConfig[appt.status]?.label }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="!appointments.data.length">
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-8 w-8 text-gray-200 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm text-gray-400 dark:text-gray-600">No appointments found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="appointments.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                <p class="text-sm text-gray-400 dark:text-gray-600">
                    Page {{ appointments.current_page }} of {{ appointments.last_page }}
                </p>
                <div class="flex gap-1">
                    <template v-for="link in appointments.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                            :class="link.active
                                ? 'bg-violet-600 text-white'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-gray-300 dark:text-gray-700"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>
