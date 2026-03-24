<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Doctor, PaginatedData } from '@/types';

const props = defineProps<{
    doctors: PaginatedData<Doctor>;
    filters: { search?: string; status?: string };
}>();

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

let searchTimeout: ReturnType<typeof setTimeout>;

function onSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 350);
}

function applyFilters() {
    router.get('/admin/doctors', { search: search.value, status: status.value }, { preserveState: true, replace: true });
}

function approve(doctor: Doctor) {
    router.patch(`/admin/doctors/${doctor.id}/approve`, {}, { preserveScroll: true });
}

function suspend(doctor: Doctor) {
    if (confirm(`Suspend Dr. ${doctor.name}? They will no longer appear on the platform.`)) {
        router.patch(`/admin/doctors/${doctor.id}/suspend`, {}, { preserveScroll: true });
    }
}

function destroy(doctor: Doctor) {
    if (confirm(`Permanently delete Dr. ${doctor.name}? This cannot be undone.`)) {
        router.delete(`/admin/doctors/${doctor.id}`, { preserveScroll: true });
    }
}

const statusLabels: Record<string, { bg: string; text: string; dot: string; label: string }> = {
    approved:  { bg: 'bg-emerald-50 dark:bg-emerald-900/20',  text: 'text-emerald-700 dark:text-emerald-400',  dot: 'bg-emerald-500',  label: 'Approved' },
    pending:   { bg: 'bg-amber-50 dark:bg-amber-900/20',   text: 'text-amber-700 dark:text-amber-400',   dot: 'bg-amber-500',   label: 'Pending' },
    suspended: { bg: 'bg-red-50 dark:bg-red-900/20',      text: 'text-red-600 dark:text-red-400',      dot: 'bg-red-500',      label: 'Suspended' },
};
</script>

<template>
    <Head title="Manage Doctors" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Doctors</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ doctors.total }} registered in total</p>
            </div>
        </template>

        <!-- Toolbar -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 gap-2">
                <div class="relative flex-1 max-w-xs">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="search"
                        @input="onSearch"
                        type="text"
                        placeholder="Search doctors..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                    />
                </div>
                <!-- Status filter chips -->
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="opt in [
                            { value: '', label: 'All' },
                            { value: 'approved', label: 'Approved' },
                            { value: 'pending', label: 'Pending' },
                            { value: 'suspended', label: 'Suspended' },
                        ]"
                        :key="opt.value"
                        @click="status = opt.value; applyFilters()"
                        class="rounded-xl border px-3 py-2 text-xs font-semibold transition-all duration-150"
                        :class="status === opt.value
                            ? 'border-violet-300 bg-violet-600 text-white shadow-sm dark:border-violet-700'
                            : 'border-gray-200 bg-white text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'"
                    >
                        {{ opt.label }}
                    </button>
                </div>
            </div>
            <Link href="/admin/doctors/create" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 active:scale-95">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Doctor
            </Link>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/60 dark:border-gray-800 dark:bg-gray-800/40">
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Doctor</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Specialization</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Location</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Appointments</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        <tr v-for="doctor in doctors.data" :key="doctor.id" class="group transition-colors hover:bg-gray-50/70 dark:hover:bg-gray-800/40">
                            <!-- Doctor -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Avatar with initials fallback -->
                                    <div class="relative h-10 w-10 shrink-0">
                                        <img
                                            v-if="doctor.avatar_url"
                                            :src="doctor.avatar_url"
                                            :alt="doctor.name"
                                            class="h-10 w-10 rounded-xl object-cover ring-1 ring-gray-100 dark:ring-gray-700"
                                            @error="($event.target as HTMLImageElement).style.display = 'none'"
                                        />
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 text-sm font-bold text-white">
                                            {{ doctor.name?.charAt(0)?.toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Dr. {{ doctor.name }}</p>
                                        <p class="truncate text-xs text-gray-400 dark:text-gray-500">{{ doctor.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Specialization -->
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-lg bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 dark:bg-violet-900/20 dark:text-violet-300">
                                    {{ doctor.specialization }}
                                </span>
                            </td>
                            <!-- Location -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    <svg v-if="doctor.location" class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ doctor.location ?? '—' }}
                                </div>
                            </td>
                            <!-- Appointments count -->
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex h-7 min-w-[1.75rem] items-center justify-center rounded-lg bg-gray-100 px-2 text-sm font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ doctor.appointments_count ?? 0 }}
                                </span>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                                    :class="[statusLabels[doctor.status]?.bg, statusLabels[doctor.status]?.text]"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full" :class="statusLabels[doctor.status]?.dot"></span>
                                    {{ statusLabels[doctor.status]?.label }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Approve -->
                                    <button
                                        v-if="doctor.status !== 'approved'"
                                        @click="approve(doctor)"
                                        title="Approve"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 transition-all hover:bg-emerald-100 hover:shadow-sm active:scale-95 dark:bg-emerald-900/20 dark:text-emerald-400 dark:hover:bg-emerald-900/40"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </button>
                                    <!-- Suspend -->
                                    <button
                                        v-if="doctor.status === 'approved'"
                                        @click="suspend(doctor)"
                                        title="Suspend"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition-all hover:bg-amber-100 hover:shadow-sm active:scale-95 dark:bg-amber-900/20 dark:text-amber-400 dark:hover:bg-amber-900/40"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Suspend
                                    </button>
                                    <!-- Edit -->
                                    <Link
                                        :href="`/admin/doctors/${doctor.id}/edit`"
                                        title="Edit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-all hover:bg-gray-100 hover:shadow-sm active:scale-95 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </Link>
                                    <!-- Delete -->
                                    <button
                                        @click="destroy(doctor)"
                                        title="Delete"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition-all hover:bg-red-100 hover:shadow-sm active:scale-95 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty state -->
                        <tr v-if="!doctors.data.length">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="mx-auto flex w-fit flex-col items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                                        <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">No doctors found</p>
                                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Try adjusting your search or filter criteria</p>
                                    </div>
                                    <Link href="/admin/doctors/create" class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-violet-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Doctor
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="doctors.last_page > 1" class="flex flex-col gap-3 border-t border-gray-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-semibold text-gray-700 dark:text-gray-300">{{ doctors.from }}–{{ doctors.to }}</span> of <span class="font-semibold text-gray-700 dark:text-gray-300">{{ doctors.total }}</span> doctors
                </p>
                <div class="flex gap-1">
                    <template v-for="link in doctors.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2.5 text-sm font-medium transition-all active:scale-95"
                            :class="link.active
                                ? 'bg-violet-600 text-white shadow-sm'
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
