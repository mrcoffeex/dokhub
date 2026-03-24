<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { Appointment } from '@/types';

defineProps<{
    appointment?: Appointment | null;
    searched?: boolean;
}>();

const form = useForm({
    reference: '',
    email: '',
});

function lookup() {
    form.post('/my-appointment');
}

function formatDate(dateStr: string) {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
    });
}

function formatTime(timeStr: string) {
    const [h, m] = timeStr.split(':').map(Number);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

const statusConfig: Record<string, { label: string; bg: string; text: string }> = {
    pending:   { label: 'Pending',   bg: 'bg-yellow-100', text: 'text-yellow-700' },
    confirmed: { label: 'Confirmed', bg: 'bg-green-100',  text: 'text-green-700' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-100',    text: 'text-red-700' },
    completed: { label: 'Completed', bg: 'bg-gray-100',   text: 'text-gray-700' },
};
</script>

<template>
    <Head title="Track My Appointment" />
    <GuestLayout>
        <div class="mx-auto max-w-xl px-4 py-20 sm:px-6">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Track Your Appointment</h1>
                <p class="mt-2 text-gray-600">Enter your reference number and email to view your appointment.</p>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-sm">
                <form @submit.prevent="lookup" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                        <input
                            v-model="form.reference"
                            type="text"
                            placeholder="APT-2026-0001"
                            required
                            class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 font-mono text-sm uppercase text-gray-900 outline-none transition placeholder:normal-case placeholder:font-sans focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                        />
                        <p v-if="form.errors.reference" class="mt-1 text-xs text-red-500">{{ form.errors.reference }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="you@example.com"
                            required
                            class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl bg-violet-600 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 disabled:opacity-60"
                    >
                        {{ form.processing ? 'Looking up...' : 'Look Up Appointment' }}
                    </button>
                </form>
            </div>

            <!-- Result: found -->
            <div v-if="searched && appointment" class="mt-8 rounded-2xl border border-green-200 bg-white shadow-sm overflow-hidden">
                <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex items-center justify-between">
                    <h2 class="font-semibold text-green-900">Appointment Found</h2>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="[statusConfig[appointment.status]?.bg, statusConfig[appointment.status]?.text]">
                        {{ statusConfig[appointment.status]?.label }}
                    </span>
                </div>
                <div class="p-6">
                    <div class="mb-4 text-center">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Reference</p>
                        <p class="font-mono text-xl font-bold text-violet-700">{{ appointment.reference }}</p>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Doctor</span>
                            <span class="text-sm font-semibold text-gray-900">Dr. {{ appointment.doctor?.name }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Date</span>
                            <span class="text-sm font-semibold text-gray-900">{{ formatDate(appointment.appointment_date) }}</span>
                        </div>
                        <div class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Time</span>
                            <span class="text-sm font-semibold text-gray-900">{{ formatTime(appointment.appointment_time) }}</span>
                        </div>
                        <div v-if="appointment.cancellation_reason" class="flex justify-between py-3">
                            <span class="text-sm text-gray-500">Cancellation Reason</span>
                            <span class="text-sm text-red-600">{{ appointment.cancellation_reason }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result: not found -->
            <div v-else-if="searched && !appointment" class="mt-8 rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-3 font-semibold text-gray-900">No appointment found</h3>
                <p class="mt-1 text-sm text-gray-500">Please check your reference number and email and try again.</p>
            </div>
        </div>
    </GuestLayout>
</template>
