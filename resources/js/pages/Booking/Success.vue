<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { Appointment } from '@/types';

const props = defineProps<{
    appointment: Appointment;
}>();

function formatDate(dateStr: string) {
    const datePart = dateStr.substring(0, 10); // handles both "YYYY-MM-DD" and ISO datetime strings
    return new Date(datePart + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatTime(timeStr: string) {
    const [h, m] = timeStr.split(':').map(Number);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}
</script>

<template>
    <Head title="Appointment Confirmed" />
    <GuestLayout>
        <div class="mx-auto max-w-2xl px-4 py-20 sm:px-6 lg:px-8">
            <!-- Success icon -->
            <div class="mb-8 flex justify-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-green-100 ring-8 ring-green-50">
                    <svg class="h-10 w-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>

            <div class="text-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Appointment Confirmed!</h1>
                <p class="mt-3 text-gray-600">A confirmation has been sent to <span class="font-semibold text-gray-800">{{ appointment.patient_email }}</span></p>
            </div>

            <!-- Reference badge -->
            <div class="mt-8 rounded-2xl border-2 border-violet-200 bg-violet-50 p-6 text-center">
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-500">Appointment Reference</p>
                <p class="mt-2 font-mono text-2xl font-bold tracking-wide text-violet-700">{{ appointment.reference }}</p>
                <p class="mt-1 text-xs text-violet-500">Save this for future reference</p>
            </div>

            <!-- Details card -->
            <div class="mt-6 rounded-2xl border border-gray-100 bg-white shadow-sm">
                <div class="p-6">
                    <h2 class="font-semibold text-gray-900">Appointment Details</h2>
                    <div class="mt-4 divide-y divide-gray-100">
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Doctor</span>
                            <span class="text-sm font-semibold text-gray-900">Dr. {{ appointment.doctor?.name }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Specialization</span>
                            <span class="text-sm font-semibold text-gray-900">{{ appointment.doctor?.specialization }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Date</span>
                            <span class="text-sm font-semibold text-gray-900">{{ formatDate(appointment.appointment_date) }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Time</span>
                            <span class="text-sm font-semibold text-gray-900">{{ formatTime(appointment.appointment_time) }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Patient</span>
                            <span class="text-sm font-semibold text-gray-900">{{ appointment.patient_name }}</span>
                        </div>
                        <div v-if="appointment.reason" class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Reason</span>
                            <span class="text-sm font-semibold text-gray-900 text-right max-w-xs">{{ appointment.reason }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info box -->
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <div class="flex gap-3">
                    <svg class="h-5 w-5 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm text-amber-800">
                        <p class="font-semibold">Before your appointment</p>
                        <ul class="mt-1 list-disc pl-4 space-y-0.5 text-amber-700">
                            <li>Bring a valid photo ID</li>
                            <li>Arrive 10 minutes early</li>
                            <li>Cancel at least 24 hours in advance if needed</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <Link href="/doctors" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                    Book Another Appointment
                </Link>
                <Link href="/my-appointment" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Track My Appointment
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
