<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Patient, Doctor, Diagnosis } from '@/types';
import { toast } from 'vue-sonner';

const props = defineProps<{
    patient: Patient;
    doctor: Doctor;
    diagnoses: Pick<Diagnosis, 'id' | 'title' | 'created_at'>[];
    diagnosis_id: number | null;
}>();

// ---- Prescription form ----
const form = useForm({
    diagnosis_id: props.diagnosis_id ?? (null as number | null),
    medications: [
        { name: '', dosage: '', frequency: '', duration: '', instructions: '' },
    ] as { name: string; dosage: string; frequency: string; duration: string; instructions: string }[],
    notes: '',
});

function addMed() {
    form.medications.push({ name: '', dosage: '', frequency: '', duration: '', instructions: '' });
}
function removeMed(i: number) {
    if (form.medications.length > 1) form.medications.splice(i, 1);
}

function submit() {
    form.post(`/doctor/patients/${props.patient.id}/prescriptions`, {
        preserveScroll: false,
        onError: () => {
            toast.error('Could not create prescription', {
                description: 'Please review the highlighted fields and try again.',
                duration: 5000,
            });
        },
    });
}

// ---- Medicine autocomplete ----
const suggestions = ref<{ name: string; type: string }[]>([]);
const activeFieldIndex = ref<number | null>(null);
const showDropdown = ref(false);
const isSearching = ref(false);
const searchedTerm = ref('');
let abortCtrl: AbortController | null = null;
let searchTimer: ReturnType<typeof setTimeout> | null = null;

function searchMedicine(term: string, idx: number) {
    activeFieldIndex.value = idx;
    if (term.length < 2) {
        suggestions.value = [];
        showDropdown.value = false;
        isSearching.value = false;
        searchedTerm.value = '';
        return;
    }

    isSearching.value = true;
    showDropdown.value = true;
    if (searchTimer) clearTimeout(searchTimer);
    if (abortCtrl) abortCtrl.abort();

    searchTimer = setTimeout(async () => {
        abortCtrl = new AbortController();
        try {
            const res = await fetch(`/api/medicines/search?q=${encodeURIComponent(term)}`, { signal: abortCtrl.signal });
            suggestions.value = await res.json();
            searchedTerm.value = term;
            showDropdown.value = true;
        } catch {
            suggestions.value = [];
        } finally {
            isSearching.value = false;
        }
    }, 300);
}

function pickSuggestion(name: string, idx: number) {
    form.medications[idx].name = name;
    suggestions.value = [];
    showDropdown.value = false;
    isSearching.value = false;
    searchedTerm.value = '';
}

function closeSuggestions() {
    setTimeout(() => {
        showDropdown.value = false;
        isSearching.value = false;
    }, 150);
}

// ---- Presets ----
const freqPresets = ['Once daily', 'Twice daily', 'Three times daily', 'Every 8 hours', 'Every 12 hours', 'As needed'];
const durationPresets = ['3 days', '5 days', '7 days', '10 days', '14 days', '1 month', '3 months'];

// ---- Preview helpers ----
const selectedDiagnosis = computed(() =>
    props.diagnoses.find(d => d.id === form.diagnosis_id) ?? null
);

const today = new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
const todayShort = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });

function formatDate(d: string | null): string {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
</script>

<template>
    <Head :title="`New Prescription — ${patient.name}`" />
    <DoctorLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <Link href="/doctor/patients" class="text-gray-500 hover:text-violet-600 transition-colors">Patients</Link>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <Link :href="`/doctor/patients/${patient.id}`" class="text-gray-500 hover:text-violet-600 transition-colors">{{ patient.name }}</Link>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-semibold text-gray-900 dark:text-white">New Prescription</span>
            </div>
        </template>

        <!-- Two-column layout: form + live preview -->
        <div class="flex min-h-0 gap-8 xl:items-start">

            <!-- ======== LEFT: FORM ======== -->
            <div class="min-w-0 flex-1">
                <div class="mx-auto max-w-2xl space-y-5">
                    <!-- Diagnosis link -->
                    <div v-if="diagnoses.length > 0" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">
                            <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-300">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </span>
                            Link to Diagnosis <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                        </label>
                        <select v-model="form.diagnosis_id" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option :value="null">— None —</option>
                            <option v-for="d in diagnoses" :key="d.id" :value="d.id">
                                {{ d.title }} ({{ formatDate(d.created_at) }})
                            </option>
                        </select>
                    </div>

                    <!-- Medications -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <!-- Card header -->
                        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/60 px-5 py-3.5 dark:border-gray-800 dark:bg-gray-800/40">
                            <div class="flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-100 dark:bg-violet-900/40">
                                    <svg class="h-4 w-4 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </span>
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Medications</h3>
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">
                                    {{ form.medications.length }}
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="addMed"
                                class="flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-violet-700 active:scale-95"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Medicine
                            </button>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div
                                    v-for="(med, i) in form.medications"
                                    :key="i"
                                    class="group relative p-5 transition-colors hover:bg-gray-50/50 dark:hover:bg-gray-800/30"
                                >
                                    <!-- Row header -->
                                    <div class="mb-4 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-violet-600 text-xs font-bold text-white">{{ i + 1 }}</span>
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                                {{ med.name || 'New medication' }}
                                            </span>
                                        </div>
                                        <button
                                            v-if="form.medications.length > 1"
                                            type="button"
                                            @click="removeMed(i)"
                                            class="flex items-center gap-1 rounded-lg border border-red-100 bg-red-50 px-2.5 py-1 text-xs font-medium text-red-500 opacity-0 transition hover:bg-red-100 hover:text-red-700 group-hover:opacity-100 dark:border-red-900/30 dark:bg-red-900/20 dark:text-red-400"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Remove
                                        </button>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <!-- Medicine name with autocomplete -->
                                        <div class="relative sm:col-span-2">
                                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">
                                                Medicine Name <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <input
                                                    v-model="med.name"
                                                    @input="searchMedicine(med.name, i)"
                                                    @blur="closeSuggestions"
                                                    type="text"
                                                    required
                                                    placeholder="Search medicine name (powered by NIH RxNorm)…"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-10 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    autocomplete="off"
                                                />
                                                <div class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2">
                                                    <svg v-if="isSearching && activeFieldIndex === i" class="h-4 w-4 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                                    </svg>
                                                    <svg v-else-if="med.name && !showDropdown" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <p v-if="form.errors[`medications.${i}.name`]" class="mt-1 text-xs text-red-500">{{ form.errors[`medications.${i}.name`] }}</p>

                                            <!-- Autocomplete dropdown -->
                                            <transition
                                                enter-active-class="transition duration-100 ease-out"
                                                enter-from-class="scale-y-95 opacity-0"
                                                enter-to-class="scale-y-100 opacity-100"
                                                leave-active-class="transition duration-75 ease-in"
                                                leave-from-class="scale-y-100 opacity-100"
                                                leave-to-class="scale-y-95 opacity-0"
                                            >
                                                <div
                                                    v-if="showDropdown && activeFieldIndex === i"
                                                    class="absolute left-0 right-0 top-full z-30 mt-1.5 origin-top overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
                                                >
                                                    <div v-if="isSearching" class="flex items-center gap-3 px-4 py-3.5 text-sm text-gray-500 dark:text-gray-400">
                                                        <svg class="h-4 w-4 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24">
                                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                                        </svg>
                                                        Searching medicines…
                                                    </div>
                                                    <div v-else-if="!isSearching && searchedTerm && suggestions.length === 0" class="flex items-center gap-3 px-4 py-3.5 text-sm text-gray-400 dark:text-gray-500">
                                                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        No matches for "<strong class="text-gray-600 dark:text-gray-300">{{ searchedTerm }}</strong>". Type manually.
                                                    </div>
                                                    <template v-else>
                                                        <div class="border-b border-gray-100 px-3 py-1.5 dark:border-gray-800">
                                                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">NIH RxNorm suggestions</p>
                                                        </div>
                                                        <button
                                                            v-for="s in suggestions"
                                                            :key="s.name"
                                                            type="button"
                                                            @mousedown.prevent="pickSuggestion(s.name, i)"
                                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-gray-700 transition-colors hover:bg-violet-50 hover:text-violet-700 dark:text-gray-300 dark:hover:bg-violet-900/30 dark:hover:text-violet-300"
                                                        >
                                                            <span
                                                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-bold"
                                                                :class="s.type === 'brand'
                                                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
                                                                    : 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400'"
                                                            >
                                                                {{ s.type === 'brand' ? '®' : '℞' }}
                                                            </span>
                                                            <span class="min-w-0 flex-1 truncate">{{ s.name }}</span>
                                                            <span
                                                                v-if="s.type === 'brand'"
                                                                class="shrink-0 rounded-full bg-amber-100 px-1.5 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"
                                                            >BRAND</span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </transition>
                                        </div>

                                        <!-- Dosage -->
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Dosage <span class="text-red-500">*</span></label>
                                            <input v-model="med.dosage" type="text" required placeholder="e.g. 500mg, 10ml" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500" />
                                            <p v-if="form.errors[`medications.${i}.dosage`]" class="mt-1 text-xs text-red-500">{{ form.errors[`medications.${i}.dosage`] }}</p>
                                        </div>

                                        <!-- Frequency -->
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Frequency <span class="text-red-500">*</span></label>
                                            <input v-model="med.frequency" type="text" required :list="`freq-presets-${i}`" placeholder="e.g. Twice daily" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500" />
                                            <datalist :id="`freq-presets-${i}`">
                                                <option v-for="f in freqPresets" :key="f" :value="f" />
                                            </datalist>
                                        </div>

                                        <!-- Duration -->
                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">Duration <span class="text-red-500">*</span></label>
                                            <input v-model="med.duration" type="text" required :list="`dur-presets-${i}`" placeholder="e.g. 7 days" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500" />
                                            <datalist :id="`dur-presets-${i}`">
                                                <option v-for="d in durationPresets" :key="d" :value="d" />
                                            </datalist>
                                        </div>

                                        <!-- Instructions -->
                                        <div class="sm:col-span-2">
                                            <label class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-400">
                                                Instructions <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                                            </label>
                                            <input v-model="med.instructions" type="text" placeholder="e.g. Take after meals, avoid sunlight…" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add medicine inline footer -->
                            <div class="border-t border-dashed border-gray-200 px-5 py-3 dark:border-gray-700">
                                <button type="button" @click="addMed" class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-violet-300 py-2.5 text-sm font-medium text-violet-600 transition hover:border-violet-400 hover:bg-violet-50/60 dark:border-violet-700 dark:text-violet-400 dark:hover:bg-violet-900/20">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Add another medicine
                                </button>
                            </div>

                            <!-- Additional notes -->
                            <div class="border-t border-gray-100 p-5 dark:border-gray-800">
                                <label class="mb-1.5 block text-sm font-semibold text-gray-800 dark:text-white">
                                    <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded bg-gray-100 dark:bg-gray-700">
                                        <svg class="h-3 w-3 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </span>
                                    Additional Notes
                                </label>
                                <textarea v-model="form.notes" rows="3" placeholder="Any additional instructions, dietary advice, follow-up notes…" class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500" />
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 active:scale-95 disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ form.processing ? 'Saving…' : 'Save Prescription' }}
                                </button>
                                <Link :href="`/doctor/patients/${patient.id}`" class="rounded-xl border border-gray-200 px-6 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                                    Cancel
                                </Link>
                                <div class="ml-auto flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Medicine data via NIH RxNorm
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ======== RIGHT: LIVE PREVIEW ======== -->
            <div class="hidden w-[520px] shrink-0 xl:block">
                <div class="sticky top-6">
                    <!-- Preview header -->
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-violet-100 dark:bg-violet-900/40">
                                <svg class="h-3.5 w-3.5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </span>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Live Preview</h3>
                        </div>
                        <span class="rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-600 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                            DRAFT
                        </span>
                    </div>

                    <!-- Prescription document — exact same structure as Show.vue -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900 text-[13px]">

                        <!-- Letterhead -->
                        <div class="flex items-start justify-between border-b-4 border-violet-600 px-6 py-5">
                            <div>
                                <div class="flex items-center gap-2.5 mb-2.5">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600">
                                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                        </svg>
                                    </div>
                                    <span class="text-base font-bold text-gray-900 dark:text-white">DokHub Medical</span>
                                </div>
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Dr. {{ doctor.name }}</h1>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ doctor.specialization }}</p>
                                <p v-if="doctor.qualification" class="text-xs text-gray-500 dark:text-gray-500">{{ doctor.qualification }}</p>
                                <p v-if="doctor.location" class="mt-0.5 text-xs text-gray-500 dark:text-gray-500">📍 {{ doctor.location }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-0.5">Prescription</p>
                                <p class="text-base font-bold text-violet-400 dark:text-violet-500 italic">PREVIEW</p>
                                <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Date: {{ todayShort }}</p>
                            </div>
                        </div>

                        <!-- Patient info -->
                        <div class="grid grid-cols-3 gap-4 border-b border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-0.5">Patient</p>
                                <p class="font-bold text-gray-900 dark:text-white">{{ patient.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ patient.email }}</p>
                            </div>
                            <div v-if="patient.phone">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-0.5">Phone</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ patient.phone }}</p>
                            </div>
                            <div v-if="selectedDiagnosis">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-0.5">Diagnosis</p>
                                <p class="text-gray-700 dark:text-gray-300">{{ selectedDiagnosis.title }}</p>
                            </div>
                        </div>

                        <!-- ℞ + Medications table -->
                        <div class="px-6 py-5">
                            <div class="mb-4 flex items-center gap-2.5">
                                <span class="text-3xl font-bold italic leading-none text-violet-600 dark:text-violet-400">℞</span>
                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700" />
                            </div>

                            <!-- Empty state -->
                            <div
                                v-if="form.medications.every(m => !m.name)"
                                class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-gray-200 py-8 dark:border-gray-700"
                            >
                                <svg class="h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                <p class="text-xs text-gray-400 dark:text-gray-600">Medications will appear here as you type</p>
                            </div>

                            <!-- Medications table -->
                            <table v-else class="w-full">
                                <thead>
                                    <tr class="border-b-2 border-gray-900 dark:border-gray-200">
                                        <th class="pb-1.5 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">#</th>
                                        <th class="pb-1.5 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Medicine</th>
                                        <th class="pb-1.5 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Dosage</th>
                                        <th class="pb-1.5 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Freq.</th>
                                        <th class="pb-1.5 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Duration</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="(med, i) in form.medications" :key="i">
                                        <td class="py-2 pr-2 font-medium text-gray-400 dark:text-gray-600">{{ i + 1 }}.</td>
                                        <td class="py-2 pr-2 font-semibold text-gray-900 dark:text-white">
                                            <span v-if="med.name">{{ med.name }}</span>
                                            <span v-else class="italic text-gray-300 dark:text-gray-700">—</span>
                                        </td>
                                        <td class="py-2 pr-2 text-gray-700 dark:text-gray-300">{{ med.dosage || '—' }}</td>
                                        <td class="py-2 pr-2 text-gray-700 dark:text-gray-300">{{ med.frequency || '—' }}</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ med.duration || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Per-med instructions (below table, matching Show.vue style) -->
                            <div v-if="form.medications.some(m => m.instructions)" class="mt-3 space-y-1">
                                <template v-for="(med, i) in form.medications" :key="i">
                                    <p v-if="med.instructions" class="text-xs italic text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold not-italic text-gray-700 dark:text-gray-300">{{ i + 1 }}. {{ med.name || '—' }}:</span>
                                        {{ med.instructions }}
                                    </p>
                                </template>
                            </div>

                            <!-- Notes -->
                            <div v-if="form.notes" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3.5 dark:border-amber-800/40 dark:bg-amber-900/20">
                                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Additional Instructions</p>
                                <p class="text-xs text-amber-800 dark:text-amber-300 whitespace-pre-wrap">{{ form.notes }}</p>
                            </div>
                        </div>

                        <!-- Signature footer -->
                        <div class="flex items-end justify-between border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                            <div class="text-xs text-gray-400 dark:text-gray-600">
                                <p>Generated by DokHub Medical System</p>
                                <p>This is a computer-generated prescription.</p>
                            </div>
                            <div class="text-center">
                                <div class="mb-1 h-10 w-28 border-b-2 border-gray-800 dark:border-gray-200" />
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Dr. {{ doctor.name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ doctor.specialization }}</p>
                            </div>
                        </div>
                    </div>

                    <p class="mt-2 text-center text-xs text-gray-400 dark:text-gray-600">Preview updates as you type · Final output matches this layout</p>
                </div>
            </div>

        </div>
    </DoctorLayout>
</template>

