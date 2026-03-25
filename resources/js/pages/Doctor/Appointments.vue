<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor, Appointment } from '@/types';
import { ref, watch } from 'vue';
import { toast } from 'vue-sonner';

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

// ---- Status update ----
const cancelModal = ref(false);
const cancellingId = ref<number | null>(null);
const cancelForm = useForm({ status: 'cancelled' as const, cancellation_reason: '' });

function updateStatus(appt: Appointment, newStatus: 'confirmed' | 'completed' | 'cancelled') {
    if (newStatus === 'cancelled') {
        cancellingId.value = appt.id;
        cancelForm.reset();
        cancelModal.value = true;
        return;
    }
    router.patch(`/doctor/appointments/${appt.id}/status`, { status: newStatus }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Status updated', {
                description: `Appointment marked as ${newStatus}.`,
                duration: 3000,
            });
        },
        onError: () => {
            toast.error('Could not update status', { duration: 4000 });
        },
    });
}

function submitCancel() {
    if (!cancellingId.value) return;
    cancelForm.patch(`/doctor/appointments/${cancellingId.value}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            cancelModal.value = false;
            cancellingId.value = null;
            toast.success('Appointment cancelled', {
                description: 'The patient will be notified.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not cancel appointment', { duration: 4000 });
        },
    });
}

// ---- Add patient ----
function addPatient(appt: Appointment) {
    router.post(`/doctor/appointments/${appt.id}/add-patient`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Patient added', {
                description: 'Patient has been linked to this appointment.',
                duration: 3000,
            });
        },
        onError: () => {
            toast.error('Could not add patient', { duration: 4000 });
        },
    });
}

const actionConfig: Record<string, { next: { label: string; status: 'confirmed' | 'completed' | 'cancelled'; cls: string }[] }> = {
    pending:   { next: [
        { label: 'Confirm',    status: 'confirmed', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-800' },
        { label: 'Cancel',    status: 'cancelled', cls: 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800' },
    ]},
    confirmed: { next: [
        { label: 'Complete',  status: 'completed', cls: 'bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100 dark:bg-sky-900/20 dark:text-sky-300 dark:border-sky-800' },
        { label: 'Cancel',   status: 'cancelled', cls: 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800' },
    ]},
    completed: { next: [] },
    cancelled: { next: [] },
};
</script>

<template>
    <Head title="My Appointments" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">My Appointments</h1>
        </template>

        <!-- Search & Filters -->
        <div class="mb-6 space-y-3">
            <!-- Search bar -->
            <div class="relative">
                <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by patient name, email or reference…"
                    class="w-full rounded-2xl border border-gray-200 bg-white py-3 pl-11 pr-10 text-sm shadow-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                />
                <button
                    v-if="search"
                    @click="search = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-0.5 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300"
                    aria-label="Clear search"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Filter bar -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Status segmented control (scrollable on very small screens) -->
                <div class="w-full overflow-x-auto sm:w-auto">
                    <div class="flex min-w-max items-center gap-0.5 rounded-xl border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-800/60">
                    <button
                        v-for="opt in [
                            { value: 'all',       label: 'All' },
                            { value: 'pending',   label: 'Pending' },
                            { value: 'confirmed', label: 'Confirmed' },
                            { value: 'completed', label: 'Completed' },
                            { value: 'cancelled', label: 'Cancelled' },
                        ]"
                        :key="opt.value"
                        @click="status = opt.value"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150"
                        :class="status === opt.value
                            ? opt.value === 'all'       ? 'bg-white shadow-sm text-gray-800 dark:bg-gray-700 dark:text-gray-100'
                            : opt.value === 'pending'   ? 'bg-amber-100 text-amber-700 shadow-sm dark:bg-amber-900/40 dark:text-amber-300'
                            : opt.value === 'confirmed' ? 'bg-emerald-100 text-emerald-700 shadow-sm dark:bg-emerald-900/40 dark:text-emerald-300'
                            : opt.value === 'completed' ? 'bg-sky-100 text-sky-700 shadow-sm dark:bg-sky-900/40 dark:text-sky-300'
                                                        : 'bg-red-100 text-red-600 shadow-sm dark:bg-red-900/40 dark:text-red-300'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-white/60 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700/60'"
                    >
                        {{ opt.label }}
                    </button>
                </div>
                </div>

                <!-- Date picker -->
                <div class="relative flex items-center">
                    <svg class="pointer-events-none absolute left-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <input
                        v-model="date"
                        type="date"
                        class="rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm text-gray-700 shadow-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:focus:border-violet-500"
                        :class="date ? 'border-violet-300 dark:border-violet-700' : ''"
                    />
                    <button
                        v-if="date"
                        @click="date = ''"
                        class="absolute right-2 rounded p-0.5 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300"
                        aria-label="Clear date"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Clear all -->
                <button
                    v-if="hasActiveFilters()"
                    @click="clearFilters"
                    class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium text-gray-500 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-red-800 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear filters
                </button>

                <!-- Result count -->
                <div class="ml-auto flex items-center gap-1.5 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 dark:border-gray-800 dark:bg-gray-800/60">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ appointments.total }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ appointments.total === 1 ? 'appointment' : 'appointments' }}</span>
                </div>
            </div>
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
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Actions</th>
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
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5">
                                    <button
                                        @click="addPatient(appt)"
                                        class="rounded-lg border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 transition-colors hover:bg-violet-100 dark:bg-violet-900/20 dark:text-violet-300 dark:border-violet-800"
                                        title="Add to patients"
                                    >
                                        + Add Patient
                                    </button>
                                    <span v-if="!actionConfig[appt.status]?.next?.length" class="text-xs text-gray-300 dark:text-gray-700">—</span>
                                    <button
                                        v-for="action in actionConfig[appt.status]?.next"
                                        :key="action.status"
                                        @click="updateStatus(appt, action.status)"
                                        class="rounded-lg border px-2.5 py-1 text-xs font-semibold transition-colors"
                                        :class="action.cls"
                                    >
                                        {{ action.label }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!appointments.data.length">
                            <td colspan="6" class="px-6 py-14 text-center">
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

        <!-- Cancel modal -->
        <Teleport to="body">
            <div v-if="cancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
                <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-900">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Cancel Appointment</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Please provide a reason for cancellation (optional).</p>
                    <textarea
                        v-model="cancelForm.cancellation_reason"
                        rows="3"
                        placeholder="Reason for cancellation…"
                        class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    />
                    <div class="mt-4 flex gap-3">
                        <button
                            @click="submitCancel"
                            :disabled="cancelForm.processing"
                            class="flex-1 rounded-xl bg-red-600 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                        >
                            Confirm Cancellation
                        </button>
                        <button
                            @click="cancelModal = false"
                            class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            Go Back
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DoctorLayout>
</template>
