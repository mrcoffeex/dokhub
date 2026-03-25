<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Patient, PaginatedData } from '@/types';

const props = defineProps<{
    patients: PaginatedData<Patient>;
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

let searchTimer: ReturnType<typeof setTimeout>;
watch(search, (val) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get('/doctor/patients', { search: val || undefined }, { preserveState: true, replace: true });
    }, 350);
});

function formatDate(d: string | null): string {
    if (!d) return '—';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function initials(name: string): string {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

const genderColors: Record<string, string> = {
    male: 'bg-sky-100 text-sky-700',
    female: 'bg-pink-100 text-pink-700',
    other: 'bg-purple-100 text-purple-700',
};
</script>

<template>
    <Head title="Patients" />
    <DoctorLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Patients</h1>
                <span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                    {{ patients.total }}
                </span>
            </div>
        </template>

        <!-- Search -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative w-full sm:max-w-sm">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email or phone…"
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm shadow-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                />
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Patients are auto-registered when you confirm an appointment.
            </p>
        </div>

        <!-- Empty state -->
        <div v-if="patients.data.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-20 dark:border-gray-700 dark:bg-gray-900">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-violet-50 dark:bg-violet-900/30">
                <svg class="h-8 w-8 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <p class="mt-4 text-base font-semibold text-gray-800 dark:text-gray-200">No patients yet</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ search ? 'No patients match your search.' : 'Confirm appointments to auto-register patients here.' }}
            </p>
        </div>

        <!-- Patient grid -->
        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="patient in patients.data"
                :key="patient.id"
                :href="`/doctor/patients/${patient.id}`"
                class="group relative rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:border-violet-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-900 dark:hover:border-violet-700"
            >
                <div class="flex items-start gap-4">
                    <!-- Avatar -->
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-sm font-bold text-white shadow-sm">
                        {{ initials(patient.name) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white group-hover:text-violet-700 dark:group-hover:text-violet-300 transition-colors">
                                {{ patient.name }}
                            </p>
                            <span v-if="patient.gender" :class="genderColors[patient.gender]" class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium capitalize">
                                {{ patient.gender }}
                            </span>
                        </div>
                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ patient.email }}</p>
                        <p v-if="patient.phone" class="truncate text-xs text-gray-400 dark:text-gray-500">{{ patient.phone }}</p>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                    <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            {{ patient.diagnoses_count ?? 0 }} diagnos{{ (patient.diagnoses_count ?? 0) === 1 ? 'is' : 'es' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            {{ patient.prescriptions_count ?? 0 }} rx
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-600">Since {{ formatDate(patient.first_seen_at) }}</p>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="patients.last_page > 1" class="mt-6 flex items-center justify-center gap-1.5">
            <template v-for="link in patients.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-sm font-medium transition-colors"
                    :class="link.active
                        ? 'bg-violet-600 text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex h-8 min-w-[2rem] items-center justify-center rounded-lg px-2 text-sm text-gray-300 dark:text-gray-600"
                    v-html="link.label"
                />
            </template>
        </div>
    </DoctorLayout>
</template>
