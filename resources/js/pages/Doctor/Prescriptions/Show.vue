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
    return new Date(d.includes('T') ? d : d + 'T00:00:00').toLocaleDateString(
        'en-US',
        {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        },
    );
}
function formatDateShort(d: string | null): string {
    if (!d) return '—';
    return new Date(d.includes('T') ? d : d + 'T00:00:00').toLocaleDateString(
        'en-US',
        {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        },
    );
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
                <Link
                    href="/doctor/patients"
                    class="text-gray-500 transition-colors hover:text-orange-600"
                    >Patients</Link
                >
                <svg
                    class="h-4 w-4 text-gray-300"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
                <Link
                    v-if="prescription.patient"
                    :href="`/doctor/patients/${prescription.patient.id}`"
                    class="text-gray-500 transition-colors hover:text-orange-600"
                >
                    {{ prescription.patient.name }}
                </Link>
                <svg
                    class="h-4 w-4 text-gray-300"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                    prescription.reference
                }}</span>
            </div>
        </template>

        <!-- Action bar (hidden in print) -->
        <div class="mb-6 flex items-center gap-3 print:hidden">
            <button
                @click="print"
                class="flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
                    />
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
        <div
            id="rx-print-area"
            class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900 print:rounded-none print:border-0 print:shadow-none"
        >
            <!-- Letterhead -->
            <div
                class="flex items-start justify-between border-b-4 border-orange-600 p-8 print:p-6"
            >
                <div>
                    <div class="mb-3 flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-600 print:bg-orange-600"
                        >
                            <svg
                                class="h-6 w-6 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-xl font-bold text-gray-900 dark:text-white"
                            >DokHub Medical</span
                        >
                    </div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        Dr.
                        {{
                            doctor.name +
                            (doctor.qualification
                                ? ', ' + doctor.qualification
                                : '')
                        }}
                    </h1>
                    <div class="mt-1 mb-1 flex flex-wrap gap-1">
                        <span
                            v-for="(s, index) in doctor.specialization"
                            :key="s"
                            class="inline-block text-sm font-medium text-orange-700"
                        >
                            {{ s
                            }}<span
                                v-if="index < doctor.specialization.length - 1"
                            >
                                -
                            </span>
                        </span>
                    </div>
                    <p
                        v-if="doctor.location"
                        class="mt-1 text-xs text-gray-500 dark:text-gray-500"
                    >
                        {{ doctor.location }}
                    </p>
                </div>
                <div class="text-right">
                    <p
                        class="mb-1 text-xs font-semibold tracking-wider text-gray-400 uppercase dark:text-gray-500"
                    >
                        Prescription
                    </p>
                    <p
                        class="text-lg font-bold text-orange-600 dark:text-orange-400"
                    >
                        {{ prescription.reference }}
                    </p>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Date: {{ formatDateShort(prescription.created_at) }}
                    </p>
                </div>
            </div>

            <!-- Patient info -->
            <div
                class="grid grid-cols-3 gap-6 border-b border-gray-100 bg-gray-50 px-8 py-5 dark:border-gray-800 dark:bg-gray-800/50 print:bg-gray-50"
            >
                <div>
                    <p
                        class="mb-1 text-xs font-semibold tracking-wider text-gray-400 uppercase dark:text-gray-500"
                    >
                        Patient
                    </p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">
                        {{ prescription.patient?.name }}
                    </p>
                </div>
            </div>

            <!-- Rx symbol + medications -->
            <div class="px-8 py-6">
                <div class="mb-5 flex items-center gap-3">
                    <span
                        class="text-4xl leading-none font-bold text-orange-600 italic dark:text-orange-400"
                        >℞</span
                    >
                    <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700" />
                </div>

                <table class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b-2 border-gray-900 dark:border-gray-200"
                        >
                            <th
                                class="pb-2 text-left font-bold tracking-wider text-gray-700 uppercase dark:text-gray-300"
                            >
                                #
                            </th>
                            <th
                                class="pb-2 text-left font-bold tracking-wider text-gray-700 uppercase dark:text-gray-300"
                            >
                                Medicine
                            </th>
                            <th
                                class="pb-2 text-left font-bold tracking-wider text-gray-700 uppercase dark:text-gray-300"
                            >
                                Dosage
                            </th>
                            <th
                                class="pb-2 text-left font-bold tracking-wider text-gray-700 uppercase dark:text-gray-300"
                            >
                                Frequency
                            </th>
                            <th
                                class="pb-2 text-left font-bold tracking-wider text-gray-700 uppercase dark:text-gray-300"
                            >
                                Duration
                            </th>
                        </tr>
                    </thead>
                    <tbody class="">
                        <tr
                            v-for="(med, i) in prescription.medications"
                            :key="i"
                            class="py-3"
                        >
                            <td
                                class="py-3 pr-3 font-medium text-gray-400 dark:text-gray-600"
                            >
                                {{ i + 1 }}.
                            </td>
                            <td
                                class="py-3 pr-3 font-semibold text-gray-900 dark:text-white"
                            >
                                {{ med.name }}
                            </td>
                            <td
                                class="py-3 pr-3 text-gray-700 dark:text-gray-300"
                            >
                                {{ med.dosage }}
                            </td>
                            <td
                                class="py-3 pr-3 text-gray-700 dark:text-gray-300"
                            >
                                {{ med.frequency }}
                            </td>
                            <td
                                class="py-3 pr-3 text-gray-700 dark:text-gray-300"
                            >
                                {{ med.duration }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Per-med instructions -->
                <div
                    v-if="prescription.medications.some((m) => m.instructions)"
                    class="rx-med-instructions mt-3 space-y-1"
                >
                    <template
                        v-for="(med, i) in prescription.medications"
                        :key="i"
                    >
                        <p
                            v-if="med.instructions"
                            class="text-xs text-gray-500 italic dark:text-gray-400"
                        >
                            <span
                                class="font-semibold text-gray-700 not-italic dark:text-gray-300"
                                >{{ i + 1 }}. {{ med.name || '—' }}:
                            </span>
                            {{ med.instructions }}
                        </p>
                    </template>
                </div>

                <!-- Notes -->
                <div
                    v-if="prescription.notes"
                    class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/40 dark:bg-amber-900/20"
                >
                    <p
                        class="mb-1 text-xs font-semibold tracking-wider text-amber-700 uppercase dark:text-amber-400"
                    >
                        Additional Instructions
                    </p>
                    <p
                        class="text-sm whitespace-pre-wrap text-amber-800 dark:text-amber-300"
                    >
                        {{ prescription.notes }}
                    </p>
                </div>
            </div>

            <!-- Signature footer -->
            <div
                class="flex items-end justify-between border-t border-gray-200 px-8 py-6 dark:border-gray-700"
            >
                <div class="text-xs text-gray-400 dark:text-gray-600">
                    <p>Generated by DokHub</p>
                    <p>This is a computer-generated prescription.</p>
                </div>
                <div class="text-center">
                    <p
                        class="text-xs font-semibold text-gray-700 dark:text-gray-300"
                    >
                        Dr.
                        {{
                            doctor.name +
                            (doctor.qualification
                                ? ', ' + doctor.qualification
                                : '')
                        }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        {{ doctor.specialization.join(', ') }}
                    </p>
                </div>
            </div>
        </div>
    </DoctorLayout>
</template>

<style>
/* A6 portrait (105 × 148 mm) */
@page {
    size: 105mm 148mm;
    margin: 1mm 1mm;
}

@media print {
    .print\:hidden {
        display: none !important;
    }
    aside,
    header,
    nav {
        display: none !important;
    }
    .pl-64 {
        padding-left: 0 !important;
    }

    body {
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #rx-print-area {
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        zoom: 0.9;
    }

    /* ── Letterhead ─────────────────────────────────────────── */
    #rx-print-area > div:first-child {
        padding: 3mm 4mm !important;
    }
    #rx-print-area > div:first-child h1 {
        font-size: 9pt !important;
        line-height: 1.3 !important;
    }
    #rx-print-area > div:first-child .text-xl {
        font-size: 8pt !important;
    }
    #rx-print-area > div:first-child .text-lg {
        font-size: 8pt !important;
    }
    #rx-print-area > div:first-child .text-sm,
    #rx-print-area > div:first-child .text-xs {
        font-size: 7pt !important;
    }
    #rx-print-area > div:first-child .h-10,
    #rx-print-area > div:first-child .w-10 {
        height: 22px !important;
        width: 22px !important;
    }
    #rx-print-area > div:first-child .h-6,
    #rx-print-area > div:first-child .w-6 {
        height: 13px !important;
        width: 13px !important;
    }
    #rx-print-area > div:first-child .mb-3 {
        margin-bottom: 1mm !important;
    }
    #rx-print-area > div:first-child .mt-2 {
        margin-top: 0.5mm !important;
    }

    /* ── Patient info band ──────────────────────────────────── */
    #rx-print-area > div:nth-child(2) {
        padding: 2mm 4mm !important;
    }
    #rx-print-area > div:nth-child(2) * {
        font-size: 7.5pt !important;
    }

    /* ── ℞ + medications ────────────────────────────────────── */
    #rx-print-area > div:nth-child(3) {
        padding: 2mm 4mm !important;
    }
    #rx-print-area > div:nth-child(3) .mb-5 {
        margin-bottom: 1.5mm !important;
    }
    #rx-print-area > div:nth-child(3) .text-4xl {
        font-size: 14pt !important;
    }
    #rx-print-area > div:nth-child(3) table {
        font-size: 7pt !important;
    }
    #rx-print-area > div:nth-child(3) table th,
    #rx-print-area > div:nth-child(3) table td {
        padding-top: 1mm !important;
        padding-bottom: 1mm !important;
        padding-right: 1.5mm !important;
    }
    #rx-print-area > div:nth-child(3) .mt-6 {
        margin-top: 2mm !important;
    }
    #rx-print-area > div:nth-child(3) .p-4 {
        padding: 2mm !important;
    }
    #rx-print-area > div:nth-child(3) .p-4 * {
        font-size: 7pt !important;
    }
    #rx-print-area .rx-med-instructions,
    #rx-print-area .rx-med-instructions * {
        font-size: 7.5pt !important;
    }

    /* ── Signature footer ───────────────────────────────────── */
    #rx-print-area > div:last-child {
        padding: 2mm 4mm !important;
    }
    #rx-print-area > div:last-child * {
        font-size: 7pt !important;
    }
}
</style>
