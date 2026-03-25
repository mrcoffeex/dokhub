<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, onMounted } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Patient, Doctor, Diagnosis } from '@/types';

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
    form.post(`/doctor/patients/${props.patient.id}/prescriptions`, { preserveScroll: false });
}

// ---- Medicine autocomplete ----
const suggestions = ref<string[]>([]);
const activeFieldIndex = ref<number | null>(null);
const showDropdown = ref(false);
let abortCtrl: AbortController | null = null;

async function searchMedicine(term: string, idx: number) {
    activeFieldIndex.value = idx;
    if (term.length < 2) { suggestions.value = []; showDropdown.value = false; return; }

    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    try {
        const res = await fetch(`/api/medicines/search?q=${encodeURIComponent(term)}`, { signal: abortCtrl.signal });
        suggestions.value = await res.json();
        showDropdown.value = suggestions.value.length > 0;
    } catch {
        suggestions.value = [];
    }
}

function pickSuggestion(name: string, idx: number) {
    form.medications[idx].name = name;
    suggestions.value = [];
    showDropdown.value = false;
}

function closeSuggestions() {
    setTimeout(() => { showDropdown.value = false; }, 150);
}

// ---- Frequency presets ----
const freqPresets = ['Once daily', 'Twice daily', 'Three times daily', 'Every 8 hours', 'Every 12 hours', 'As needed'];
const durationPresets = ['3 days', '5 days', '7 days', '10 days', '14 days', '1 month', '3 months'];

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

        <div class="mx-auto max-w-3xl">
            <!-- Letterhead preview -->
            <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-center justify-between bg-gradient-to-r from-violet-600 to-indigo-600 px-6 py-4 text-white">
                    <div>
                        <p class="text-xs font-medium text-violet-200">DokHub Medical</p>
                        <h2 class="text-lg font-bold">Dr. {{ doctor.name }}</h2>
                        <p class="text-sm text-violet-200">{{ doctor.specialization }} · {{ doctor.qualification }}</p>
                    </div>
                    <div class="text-right text-xs text-violet-200">
                        <p>{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                        <p v-if="doctor.location">{{ doctor.location }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 border-b border-gray-100 bg-gray-50 px-6 py-3 dark:border-gray-800 dark:bg-gray-800/50">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Patient</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ patient.name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ patient.email }}</p>
                    </div>
                    <div v-if="patient.phone">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Phone</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ patient.phone }}</p>
                    </div>
                    <div v-if="patient.age">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Age</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ patient.age }} years</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Diagnosis link -->
                <div v-if="diagnoses.length > 0" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Link to Diagnosis (optional)</label>
                    <select v-model="form.diagnosis_id" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-100">
                        <option :value="null">— None —</option>
                        <option v-for="d in diagnoses" :key="d.id" :value="d.id">
                            {{ d.title }} ({{ formatDate(d.created_at) }})
                        </option>
                    </select>
                </div>

                <!-- Medications -->
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Medications</h3>
                        <button type="button" @click="addMed" class="flex items-center gap-1.5 rounded-lg bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition dark:bg-violet-900/30 dark:text-violet-300">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Medicine
                        </button>
                    </div>

                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div
                            v-for="(med, i) in form.medications"
                            :key="i"
                            class="p-5"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">{{ i + 1 }}</span>
                                <button
                                    v-if="form.medications.length > 1"
                                    type="button"
                                    @click="removeMed(i)"
                                    class="text-xs text-red-400 hover:text-red-600 transition"
                                >Remove</button>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <!-- Medicine name with autocomplete -->
                                <div class="relative sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Medicine Name <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="med.name"
                                        @input="searchMedicine(med.name, i)"
                                        @blur="closeSuggestions"
                                        type="text"
                                        required
                                        placeholder="Type to search medicines (RxNorm)"
                                        class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-100"
                                        autocomplete="off"
                                    />
                                    <p v-if="form.errors[`medications.${i}.name`]" class="mt-1 text-xs text-red-500">{{ form.errors[`medications.${i}.name`] }}</p>

                                    <!-- Autocomplete dropdown -->
                                    <div
                                        v-if="showDropdown && activeFieldIndex === i && suggestions.length"
                                        class="absolute top-full left-0 right-0 z-20 mt-1 rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900 overflow-hidden"
                                    >
                                        <button
                                            v-for="s in suggestions"
                                            :key="s"
                                            type="button"
                                            @mousedown.prevent="pickSuggestion(s, i)"
                                            class="block w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-violet-50 hover:text-violet-700 dark:text-gray-300 dark:hover:bg-violet-900/30 transition-colors"
                                        >{{ s }}</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Dosage <span class="text-red-500">*</span></label>
                                    <input v-model="med.dosage" type="text" required placeholder="e.g. 500mg, 10ml" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400" />
                                    <p v-if="form.errors[`medications.${i}.dosage`]" class="mt-1 text-xs text-red-500">{{ form.errors[`medications.${i}.dosage`] }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Frequency <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input v-model="med.frequency" type="text" required list="freq-presets" placeholder="e.g. Twice daily" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400" />
                                        <datalist id="freq-presets">
                                            <option v-for="f in freqPresets" :key="f" :value="f" />
                                        </datalist>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Duration <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input v-model="med.duration" type="text" required list="dur-presets" placeholder="e.g. 7 days" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400" />
                                        <datalist id="dur-presets">
                                            <option v-for="d in durationPresets" :key="d" :value="d" />
                                        </datalist>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Instructions (optional)</label>
                                    <input v-model="med.instructions" type="text" placeholder="e.g. Take after meals, Avoid sunlight…" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional notes -->
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <label class="block text-sm font-semibold text-gray-800 dark:text-white mb-2">Additional Notes</label>
                    <textarea v-model="form.notes" rows="3" placeholder="Any additional instructions, dietary advice, follow-up notes…" class="w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-100" />
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 disabled:opacity-50"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Prescription
                    </button>
                    <Link :href="`/doctor/patients/${patient.id}`" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-medium text-gray-600 hover:bg-gray-50 transition dark:border-gray-700 dark:text-gray-400">
                        Cancel
                    </Link>
                    <p class="ml-auto text-xs text-gray-400 dark:text-gray-500">Medicine data via NIH RxNorm</p>
                </div>
            </form>
        </div>
    </DoctorLayout>
</template>
