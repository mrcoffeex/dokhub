<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Prescription, Doctor } from '@/types';

const props = defineProps<{
    prescription: Prescription;
    doctor: Doctor;
}>();

function formatDate(d: string | null): string {
    if (!d) return '—';
    return new Date(d.includes('T') ? d : d + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
}
function formatDateShort(d: string | null): string {
    if (!d) return '—';
    return new Date(d.includes('T') ? d : d + 'T00:00:00').toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric',
    });
}

function print() {
    window.print();
}
</script>

<template>
    <Head :title="`Prescription ${prescription.reference}`" />
    <DoctorLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <Link href="/doctor/patients" class="text-gray-500 hover:text-violet-600 transition-colors">Patients</Link>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <Link v-if="prescription.patient" :href="`/doctor/patients/${prescription.patient.id}`" class="text-gray-500 hover:text-violet-600 transition-colors">
                    {{ prescription.patient.name }}
                </Link>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-semibold text-gray-900 dark:text-white">{{ prescription.reference }}</span>
            </div>
        </template>

        <!-- Action bar (hidden in print) -->
        <div class="mb-6 flex items-center gap-3 print:hidden">
            <button
                @click="print"
                class="flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Prescription
            </button>
            <Link
                v-if="prescription.patient"
                :href="`/doctor/patients/${prescription.patient.id}/prescriptions/create`"
                class="flex items-center gap-2 rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
            >
                New Prescription
            </Link>
        </div>

        <!-- Prescription document -->
        <div id="rx-print-area" class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900 print:border-0 print:shadow-none print:rounded-none">

            <!-- Letterhead -->
            <div class="flex items-start justify-between border-b-4 border-violet-600 p-8 print:p-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-600 print:bg-violet-600">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900 dark:text-white">DokHub Medical</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dr. {{ doctor.name }}</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ doctor.specialization }}</p>
                    <p v-if="doctor.qualification" class="text-xs text-gray-500 dark:text-gray-500">{{ doctor.qualification }}</p>
                    <p v-if="doctor.location" class="text-xs text-gray-500 dark:text-gray-500 mt-1">📍 {{ doctor.location }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-semibold mb-1">Prescription</p>
                    <p class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ prescription.reference }}</p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Date: {{ formatDateShort(prescription.created_at) }}</p>
                </div>
            </div>

            <!-- Patient info -->
            <div class="grid grid-cols-3 gap-6 border-b border-gray-100 bg-gray-50 px-8 py-5 dark:border-gray-800 dark:bg-gray-800/50 print:bg-gray-50">
                <div>
                    <p class="text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-semibold mb-1">Patient</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ prescription.patient?.name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ prescription.patient?.email }}</p>
                </div>
                <div v-if="prescription.patient?.phone">
                    <p class="text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-semibold mb-1">Phone</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ prescription.patient.phone }}</p>
                </div>
                <div v-if="prescription.diagnosis">
                    <p class="text-xs uppercase tracking-wider text-gray-400 dark:text-gray-500 font-semibold mb-1">Diagnosis</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ prescription.diagnosis.title }}</p>
                </div>
            </div>

            <!-- Rx symbol + medications -->
            <div class="px-8 py-6">
                <div class="flex items-center gap-3 mb-5">
                    <span class="text-4xl font-bold text-violet-600 dark:text-violet-400 italic leading-none">℞</span>
                    <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700" />
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-gray-900 dark:border-gray-200">
                            <th class="pb-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">#</th>
                            <th class="pb-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Medicine</th>
                            <th class="pb-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Dosage</th>
                            <th class="pb-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Frequency</th>
                            <th class="pb-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Duration</th>
                            <th class="pb-2 text-left text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="(med, i) in prescription.medications" :key="i" class="py-3">
                            <td class="py-3 pr-3 text-gray-400 dark:text-gray-600 font-medium">{{ i + 1 }}.</td>
                            <td class="py-3 pr-3 font-semibold text-gray-900 dark:text-white">{{ med.name }}</td>
                            <td class="py-3 pr-3 text-gray-700 dark:text-gray-300">{{ med.dosage }}</td>
                            <td class="py-3 pr-3 text-gray-700 dark:text-gray-300">{{ med.frequency }}</td>
                            <td class="py-3 pr-3 text-gray-700 dark:text-gray-300">{{ med.duration }}</td>
                            <td class="py-3 text-gray-500 dark:text-gray-400 italic text-xs">{{ med.instructions || '—' }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Notes -->
                <div v-if="prescription.notes" class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/40 dark:bg-amber-900/20">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider mb-1">Additional Instructions</p>
                    <p class="text-sm text-amber-800 dark:text-amber-300 whitespace-pre-wrap">{{ prescription.notes }}</p>
                </div>
            </div>

            <!-- Signature footer -->
            <div class="flex items-end justify-between border-t border-gray-200 px-8 py-6 dark:border-gray-700">
                <div class="text-xs text-gray-400 dark:text-gray-600">
                    <p>Generated by DokHub Medical System</p>
                    <p>This is a computer-generated prescription.</p>
                </div>
                <div class="text-center">
                    <div class="mb-1 h-12 w-36 border-b-2 border-gray-800 dark:border-gray-200" />
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Dr. {{ doctor.name }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ doctor.specialization }}</p>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>

<style>
/* Half A4 = A5 portrait (148 × 210 mm) */
@page {
    size: 148mm 210mm;
    margin: 5mm 7mm;
}

@media print {
    .print\:hidden { display: none !important; }
    aside, header, nav { display: none !important; }
    .pl-64 { padding-left: 0 !important; }

    body {
        background: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #rx-print-area {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        font-size: 8.5pt !important;
    }

    /* ── Letterhead ─────────────────────────────────────────── */
    #rx-print-area > div:first-child {
        padding: 4mm 5mm !important;
    }
    #rx-print-area > div:first-child  .gap-3   { gap: 2mm !important; }
    #rx-print-area > div:first-child  .mb-3    { margin-bottom: 1.5mm !important; }
    #rx-print-area > div:first-child  .mt-2    { margin-top: 1mm !important; }
    #rx-print-area > div:first-child  h1       { font-size: 12pt !important; line-height: 1.2 !important; }
    #rx-print-area > div:first-child  .text-xl { font-size: 10pt !important; }
    #rx-print-area > div:first-child  .text-lg { font-size: 9.5pt !important; }
    #rx-print-area > div:first-child  .text-2xl { font-size: 12pt !important; }
    #rx-print-area > div:first-child  .h-10,
    #rx-print-area > div:first-child  .w-10   { height: 26px !important; width: 26px !important; }
    #rx-print-area > div:first-child  .h-6,
    #rx-print-area > div:first-child  .w-6    { height: 15px !important; width: 15px !important; }
    #rx-print-area > div:first-child  .rounded-lg { border-radius: 5px !important; }

    /* ── Patient info band ──────────────────────────────────── */
    #rx-print-area > div:nth-child(2) {
        padding: 2.5mm 5mm !important;
        gap: 3mm !important;
        column-gap: 3mm !important;
    }

    /* ── ℞ + medications ────────────────────────────────────── */
    #rx-print-area > div:nth-child(3) {
        padding: 3mm 5mm !important;
    }
    #rx-print-area > div:nth-child(3) .mb-5   { margin-bottom: 2mm !important; }
    #rx-print-area > div:nth-child(3) .text-4xl { font-size: 17pt !important; }
    #rx-print-area > div:nth-child(3) table   { font-size: 7.5pt !important; }
    #rx-print-area > div:nth-child(3) table th,
    #rx-print-area > div:nth-child(3) table td {
        padding-top: 1.5mm !important;
        padding-bottom: 1.5mm !important;
        padding-right: 2mm !important;
    }
    #rx-print-area > div:nth-child(3) .mt-6   { margin-top: 3mm !important; }
    #rx-print-area > div:nth-child(3) .p-4    { padding: 2.5mm !important; }

    /* ── Signature footer ───────────────────────────────────── */
    #rx-print-area > div:last-child {
        padding: 3mm 5mm !important;
    }
    #rx-print-area > div:last-child .h-12  { height: 18px !important; }
    #rx-print-area > div:last-child .w-36  { width: 80px !important; }
    #rx-print-area > div:last-child .mb-1  { margin-bottom: 1mm !important; }
}
</style>
