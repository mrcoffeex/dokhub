<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Appointment, PaginatedData } from '@/types';

const props = defineProps<{
    appointments: PaginatedData<Appointment>;
    filters: { search?: string; status?: string; date?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const date = ref(props.filters.date ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 350);
}

function applyFilters() {
    router.get('/admin/appointments', { search: search.value, status: status.value, date: date.value }, { preserveState: true, replace: true });
}

function clearDate() {
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

const statusConfig: Record<string, { label: string; bg: string; text: string; dot: string }> = {
    pending:   { label: 'Pending',   bg: 'bg-amber-50 dark:bg-amber-900/20',    text: 'text-amber-700 dark:text-amber-400',    dot: 'bg-amber-500' },
    confirmed: { label: 'Confirmed', bg: 'bg-emerald-50 dark:bg-emerald-900/20', text: 'text-emerald-700 dark:text-emerald-400', dot: 'bg-emerald-500' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-50 dark:bg-red-900/20',        text: 'text-red-600 dark:text-red-400',         dot: 'bg-red-500' },
    completed: { label: 'Completed', bg: 'bg-sky-50 dark:bg-sky-900/20',        text: 'text-sky-600 dark:text-sky-400',         dot: 'bg-sky-500' },
};
</script>

<template>
    <Head title="Appointments" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Appointments</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ appointments.total }} total appointments</p>
            </div>
        </template>

        <!-- Filters toolbar -->
        <div class="mb-6 flex flex-col gap-3">
            <!-- Search — full width on mobile -->
            <div class="relative w-full sm:max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="search"
                    @input="onSearch"
                    type="text"
                    placeholder="Search patient, reference..."
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                />
            </div>

            <!-- Status chips + date — scrollable row on mobile -->
            <div class="flex items-center gap-2 overflow-x-auto pb-0.5 sm:flex-wrap sm:overflow-visible sm:pb-0">
                <!-- Status filter chips -->
                <div class="flex gap-1.5">
                    <button
                        v-for="opt in [
                            { value: '', label: 'All' },
                            { value: 'pending', label: 'Pending' },
                            { value: 'confirmed', label: 'Confirmed' },
                            { value: 'completed', label: 'Completed' },
                            { value: 'cancelled', label: 'Cancelled' },
                        ]"
                        :key="opt.value"
                        @click="status = opt.value; applyFilters()"
                        class="shrink-0 rounded-xl border px-3 py-2 text-xs font-semibold transition-all duration-150"
                        :class="status === opt.value
                            ? 'border-orange-300 bg-orange-600 text-white shadow-sm dark:border-orange-700'
                            : 'border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <!-- Date filter -->
                <div class="relative shrink-0 flex items-center">
                    <input
                        v-model="date"
                        @change="applyFilters"
                        type="date"
                        class="rounded-xl border border-gray-200 bg-white py-2.5 pl-4 pr-3 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                        :class="{ 'border-orange-400 ring-2 ring-orange-100': date }"
                    />
                    <button
                        v-if="date"
                        @click="clearDate"
                        class="absolute right-2 flex h-5 w-5 items-center justify-center rounded-full bg-gray-200 text-gray-500 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-400"
                        title="Clear date"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table card -->
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Reference</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Patient</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Doctor</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Date & Time</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr v-for="apt in appointments.data" :key="apt.id" class="group transition-colors hover:bg-gray-50/70 dark:hover:bg-gray-800/40">

                            <!-- Reference -->
                            <td class="px-5 py-4">
                                <span class="rounded-lg bg-gray-100 px-2 py-1 font-mono text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                    {{ apt.reference }}
                                </span>
                            </td>

                            <!-- Patient -->
                            <td class="px-5 py-4">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ apt.patient_name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ apt.patient_email }}</p>
                                <p v-if="apt.patient_phone" class="text-xs text-gray-400 dark:text-gray-500">{{ apt.patient_phone }}</p>
                            </td>

                            <!-- Doctor -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-indigo-600 text-xs font-bold text-white">
                                        {{ apt.doctor?.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Dr. {{ apt.doctor?.name }}</p>
                                        <span class="inline-block rounded-md bg-orange-50 px-1.5 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">
                                            {{ Array.isArray(apt.doctor?.specialization) ? apt.doctor.specialization.join(', ') : apt.doctor?.specialization }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Date & Time -->
                            <td class="px-5 py-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ formatDate(apt.appointment_date) }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ formatTime(apt.appointment_time) }}</p>
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">
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
                        <tr v-if="!appointments.data.length">
                            <td colspan="5" class="px-6 py-20 text-center">
                                <div class="mx-auto flex w-fit flex-col items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                                        <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No appointments found</p>
                                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or filter criteria</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="appointments.last_page > 1" class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-semibold text-gray-700 dark:text-gray-300">{{ appointments.from }}–{{ appointments.to }}</span>
                    of <span class="font-semibold text-gray-700 dark:text-gray-300">{{ appointments.total }}</span> appointments
                </p>
                <div class="flex gap-1">
                    <template v-for="link in appointments.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2.5 text-sm font-medium transition-all active:scale-95"
                            :class="link.active
                                ? 'bg-orange-600 text-white shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"
                            v-html="link.label"
                        />
                        <span v-else class="flex h-8 min-w-[2rem] items-center justify-center px-2.5 text-sm text-gray-300 dark:text-gray-600" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
