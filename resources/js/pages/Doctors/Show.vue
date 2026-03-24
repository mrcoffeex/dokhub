<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { Doctor } from '@/types';

const props = defineProps<{
    doctor: Doctor;
    bookedSlots: Record<string, string[]>;
}>();

// --- Calendar state ---
const today = new Date();
const currentMonth = ref(new Date(today.getFullYear(), today.getMonth(), 1));

const selectedDate = ref<string>('');
const selectedTime = ref<string>('');

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const calendarDays = computed(() => {
    const year = currentMonth.value.getFullYear();
    const month = currentMonth.value.getMonth();
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const days: { date: string; day: number; inactive: boolean; past: boolean; available: boolean }[] = [];

    for (let i = 0; i < firstDay; i++) {
        days.push({ date: '', day: 0, inactive: true, past: false, available: false });
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateObj = new Date(year, month, d);
        const dateStr = dateObj.toISOString().split('T')[0];
        const dayOfWeek = dateObj.getDay();
        const isPast = dateObj < new Date(today.getFullYear(), today.getMonth(), today.getDate());
        const hasSchedule = props.doctor.schedules?.some(s => s.day_of_week === dayOfWeek && s.is_active) ?? false;

        days.push({
            date: dateStr,
            day: d,
            inactive: false,
            past: isPast,
            available: !isPast && hasSchedule,
        });
    }

    return days;
});

const calendarTitle = computed(() => {
    return currentMonth.value.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
});

function prevMonth() {
    const d = new Date(currentMonth.value);
    d.setMonth(d.getMonth() - 1);
    if (d >= new Date(today.getFullYear(), today.getMonth(), 1)) {
        currentMonth.value = d;
    }
}

function nextMonth() {
    const d = new Date(currentMonth.value);
    d.setMonth(d.getMonth() + 1);
    currentMonth.value = d;
}

// --- Time slots ---
const timeSlots = computed(() => {
    if (!selectedDate.value) return [];

    const dateObj = new Date(selectedDate.value + 'T00:00:00');
    const dayOfWeek = dateObj.getDay();
    const schedule = props.doctor.schedules?.find(s => s.day_of_week === dayOfWeek && s.is_active);
    if (!schedule) return [];

    const slots: string[] = [];
    const [sh, sm] = schedule.start_time.split(':').map(Number);
    const [eh, em] = schedule.end_time.split(':').map(Number);
    const duration = schedule.slot_duration_minutes;

    let current = sh * 60 + sm;
    const end = eh * 60 + em;

    while (current + duration <= end) {
        const h = Math.floor(current / 60);
        const m = current % 60;
        slots.push(`${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`);
        current += duration;
    }

    return slots;
});

function isSlotBooked(time: string): boolean {
    return (props.bookedSlots[selectedDate.value] ?? []).includes(time);
}

function formatTime(time: string): string {
    const [h, m] = time.split(':').map(Number);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

function selectDate(date: string) {
    selectedDate.value = date;
    selectedTime.value = '';
}

// --- Booking form ---
const form = useForm({
    patient_name: '',
    patient_email: '',
    patient_phone: '',
    appointment_date: '',
    appointment_time: '',
    reason: '',
});

const step = ref<1 | 2>(1);

function goToStep2() {
    if (!selectedDate.value || !selectedTime.value) return;
    form.appointment_date = selectedDate.value;
    form.appointment_time = selectedTime.value;
    step.value = 2;
}

function submitBooking() {
    form.post(`/doctors/${props.doctor.id}/book`);
}
</script>

<template>
    <Head :title="`Dr. ${doctor.name} — Book Appointment`" />
    <GuestLayout>
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Back -->
            <Link href="/doctors" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-900">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Doctors
            </Link>

            <div class="grid gap-8 lg:grid-cols-3">

                <!-- Doctor Profile -->
                <div class="space-y-5 lg:col-span-1">
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-start gap-4">
                            <img :src="doctor.avatar_url" :alt="doctor.name" class="h-20 w-20 rounded-2xl object-cover ring-2 ring-gray-100" />
                            <div>
                                <h1 class="text-xl font-bold text-gray-900">Dr. {{ doctor.name }}</h1>
                                <span class="mt-1 inline-block rounded-full bg-violet-100 px-3 py-0.5 text-sm font-medium text-violet-700">
                                    {{ doctor.specialization }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div v-if="doctor.qualification" class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                {{ doctor.qualification }}
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ doctor.experience_years }}+ years experience
                            </div>
                            <div v-if="doctor.location" class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ doctor.location }}
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-100 pt-5">
                            <p class="text-sm font-semibold text-gray-900">Consultation Fee</p>
                            <p class="mt-1 text-2xl font-bold text-violet-600">${{ doctor.consultation_fee }}</p>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div v-if="doctor.bio" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-900">About</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">{{ doctor.bio }}</p>
                    </div>

                    <!-- Schedule overview -->
                    <div v-if="doctor.schedules && doctor.schedules.length" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-900">Weekly Schedule</h3>
                        <div class="mt-3 space-y-1.5">
                            <div v-for="s in doctor.schedules.filter(s => s.is_active)" :key="s.id" class="flex items-center justify-between text-sm">
                                <span class="text-gray-700">{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][s.day_of_week] }}</span>
                                <span class="font-medium text-gray-900">{{ formatTime(s.start_time) }} – {{ formatTime(s.end_time) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Panel -->
                <div class="lg:col-span-2">
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <!-- Step indicator -->
                        <div class="flex items-center border-b border-gray-100 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold" :class="step === 1 ? 'bg-violet-600 text-white' : 'bg-green-500 text-white'">
                                    <span v-if="step === 1">1</span>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <span class="text-sm font-medium" :class="step === 1 ? 'text-gray-900' : 'text-gray-500'">Select Date & Time</span>
                            </div>
                            <div class="mx-4 h-px flex-1 bg-gray-200" />
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold" :class="step === 2 ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-400'">
                                    2
                                </div>
                                <span class="text-sm font-medium" :class="step === 2 ? 'text-gray-900' : 'text-gray-400'">Your Details</span>
                            </div>
                        </div>

                        <!-- Step 1: Calendar + Time Slots -->
                        <div v-if="step === 1" class="p-6">
                            <!-- Calendar -->
                            <div>
                                <div class="mb-4 flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900">{{ calendarTitle }}</h3>
                                    <div class="flex gap-1">
                                        <button @click="prevMonth" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                        </button>
                                        <button @click="nextMonth" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-7 gap-1 text-center">
                                    <div v-for="day in dayNames" :key="day" class="py-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                        {{ day }}
                                    </div>
                                    <template v-for="(d, idx) in calendarDays" :key="idx">
                                        <div v-if="d.inactive" />
                                        <button
                                            v-else
                                            :disabled="!d.available"
                                            @click="d.available && selectDate(d.date)"
                                            class="relative flex h-9 w-full items-center justify-center rounded-xl text-sm font-medium transition-all"
                                            :class="{
                                                'bg-violet-600 text-white shadow-md shadow-violet-200': selectedDate === d.date,
                                                'cursor-not-allowed text-gray-300': !d.available,
                                                'text-gray-700 hover:bg-violet-50 hover:text-violet-700': d.available && selectedDate !== d.date,
                                            }"
                                        >
                                            {{ d.day }}
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Time Slots -->
                            <div v-if="selectedDate" class="mt-8">
                                <h3 class="mb-4 font-semibold text-gray-900">
                                    Available Times
                                    <span class="ml-2 text-sm font-normal text-gray-500">
                                        {{ new Date(selectedDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric' }) }}
                                    </span>
                                </h3>
                                <div v-if="timeSlots.length" class="grid grid-cols-3 gap-2 sm:grid-cols-4 md:grid-cols-5">
                                    <button
                                        v-for="time in timeSlots"
                                        :key="time"
                                        :disabled="isSlotBooked(time)"
                                        @click="!isSlotBooked(time) && (selectedTime = time)"
                                        class="rounded-xl border px-3 py-2 text-sm font-medium transition-all"
                                        :class="{
                                            'border-violet-600 bg-violet-600 text-white shadow-md': selectedTime === time,
                                            'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-300 line-through': isSlotBooked(time),
                                            'border-gray-200 text-gray-700 hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700': !isSlotBooked(time) && selectedTime !== time,
                                        }"
                                    >
                                        {{ formatTime(time) }}
                                    </button>
                                </div>
                                <p v-else class="text-sm text-gray-500">No available slots for this day.</p>
                            </div>

                            <div v-else class="mt-8 rounded-xl bg-gray-50 py-10 text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Select an available date to see time slots</p>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <button
                                    @click="goToStep2"
                                    :disabled="!selectedDate || !selectedTime"
                                    class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-violet-700 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Continue
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Patient Details Form -->
                        <form v-if="step === 2" @submit.prevent="submitBooking" class="p-6">
                            <!-- Selected appointment summary -->
                            <div class="mb-6 flex items-center justify-between rounded-xl bg-violet-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-500">Your Appointment</p>
                                    <p class="mt-0.5 text-sm font-semibold text-violet-900">
                                        {{ new Date(selectedDate + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) }}
                                        at {{ formatTime(selectedTime) }}
                                    </p>
                                </div>
                                <button type="button" @click="step = 1" class="text-xs font-semibold text-violet-600 hover:text-violet-700">Change</button>
                            </div>

                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                                        <input
                                            v-model="form.patient_name"
                                            type="text"
                                            placeholder="John Doe"
                                            required
                                            class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                            :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': form.errors.patient_name }"
                                        />
                                        <p v-if="form.errors.patient_name" class="mt-1 text-xs text-red-500">{{ form.errors.patient_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number <span class="text-red-500">*</span></label>
                                        <input
                                            v-model="form.patient_phone"
                                            type="tel"
                                            placeholder="+1 555 000 0000"
                                            required
                                            class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                            :class="{ 'border-red-400': form.errors.patient_phone }"
                                        />
                                        <p v-if="form.errors.patient_phone" class="mt-1 text-xs text-red-500">{{ form.errors.patient_phone }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.patient_email"
                                        type="email"
                                        placeholder="you@example.com"
                                        required
                                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                        :class="{ 'border-red-400': form.errors.patient_email }"
                                    />
                                    <p v-if="form.errors.patient_email" class="mt-1 text-xs text-red-500">{{ form.errors.patient_email }}</p>
                                    <p class="mt-1 text-xs text-gray-500">Your appointment confirmation will be sent to this email.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Reason for Visit <span class="text-gray-400">(Optional)</span></label>
                                    <textarea
                                        v-model="form.reason"
                                        rows="3"
                                        placeholder="Briefly describe your symptoms or what you'd like to discuss..."
                                        class="mt-1.5 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                    />
                                </div>
                            </div>

                            <!-- Error if slot was taken -->
                            <div v-if="form.errors.appointment_time" class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">
                                {{ form.errors.appointment_time }}
                            </div>

                            <div class="mt-6 flex items-center justify-between">
                                <button type="button" @click="step = 1" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                                    ← Back
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-violet-700 disabled:opacity-60"
                                >
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <span>{{ form.processing ? 'Booking...' : 'Confirm Appointment' }}</span>
                                </button>
                            </div>

                            <p class="mt-4 text-center text-xs text-gray-400">
                                No account required · Instant confirmation · Free cancellation 24h before
                            </p>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </GuestLayout>
</template>
