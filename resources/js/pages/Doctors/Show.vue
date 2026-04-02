<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { toast } from 'vue-sonner';
import VueHcaptcha from '@hcaptcha/vue3-hcaptcha';
import GuestLayout from '@/layouts/GuestLayout.vue';
import ClinicMapView from '@/components/ClinicMapView.vue';
import type { Doctor, DoctorReview, ReviewStats } from '@/types';

const IS_PRODUCTION = import.meta.env.PROD;
const HCAPTCHA_SITEKEY = import.meta.env.VITE_HCAPTCHA_SITEKEY as string;

const props = defineProps<{
    doctor: Doctor;
    bookedSlots: Record<string, string[]>;
    reviews: DoctorReview[];
    reviewStats: ReviewStats;
}>();

const page = usePage();
const reviewSuccess = computed(() => (page.props.flash as any)?.review_success ?? null);
const rateLimitError = computed(() => (page.props.errors as Record<string, string>)?.rate_limit ?? null);

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
        // Format date as local YYYY-MM-DD to avoid UTC shifts from toISOString()
        const dateStr = `${dateObj.getFullYear()}-${String(dateObj.getMonth() + 1).padStart(2, '0')}-${String(dateObj.getDate()).padStart(2, '0')}`;
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
    // Parse selectedDate (YYYY-MM-DD) as a local date to avoid timezone shifts
    const [y, m, d] = selectedDate.value.split('-').map(Number);
    const dateObj = new Date(y, m - 1, d);
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

// Parse a YYYY-MM-DD string into a local Date (avoids UTC shift issues)
function parseDateString(dateStr: string): Date {
    const [y, m, d] = dateStr.split('-').map(Number);
    return new Date(y, m - 1, d);
}

function formatSelectedDate(dateStr: string, options?: Intl.DateTimeFormatOptions): string {
    if (!dateStr) return '';
    return parseDateString(dateStr).toLocaleDateString('en-US', options);
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
    form.post(`/doctors/${props.doctor.slug}/book`, {
        onError: () => {
            toast.error('Booking failed', {
                description: 'Please review your details and try again.',
                duration: 5000,
            });
        },
    });
}

// --- Review form ---
const showReviewForm = ref(false);
const hoverRating = ref(0);
const hcaptchaRef = ref<InstanceType<typeof VueHcaptcha> | null>(null);
const reviewForm = useForm({
    patient_name:   '',
    patient_email:  '',
    rating:         0,
    comment:        '',
    hcaptcha_token: '',
});

function onCaptchaVerified(token: string) {
    reviewForm.hcaptcha_token = token;
}

function onCaptchaExpired() {
    reviewForm.hcaptcha_token = '';
}

function submitReview() {
    if (IS_PRODUCTION && !reviewForm.hcaptcha_token) {
        hcaptchaRef.value?.execute();
        return;
    }
    reviewForm.post(`/doctors/${props.doctor.slug}/reviews`, {
        preserveScroll: true,
        onSuccess: () => {
            reviewForm.reset();
            hoverRating.value = 0;
            hcaptchaRef.value?.reset();
            showReviewForm.value = false;
            toast.success('Review submitted', {
                description: 'Thank you! Your review is pending approval.',
                duration: 5000,
            });
        },
        onError: () => {
            hcaptchaRef.value?.reset();
            reviewForm.hcaptcha_token = '';
            toast.error('Could not submit review', {
                description: 'Please check your details and try again.',
                duration: 5000,
            });
        },
    });
}

function starLabel(n: number): string {
    return ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][n] ?? '';
}

function starType(rating: number, pos: number): 'full' | 'half' | 'empty' {
    if (rating >= pos) return 'full';
    if (rating >= pos - 0.5) return 'half';
    return 'empty';
}

function ratingBarWidth(star: number): string {
    if (!props.reviewStats.total) return '0%';
    return Math.round(((props.reviewStats.counts[star] ?? 0) / props.reviewStats.total) * 100) + '%';
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
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span v-for="s in doctor.specialization" :key="s" class="inline-block rounded-full bg-orange-100 px-3 py-0.5 text-sm font-medium text-orange-700">
                                        {{ s }}
                                    </span>
                                </div>
                                <!-- Inline rating summary -->
                                <div v-if="reviewStats.total > 0" class="mt-2 flex items-center gap-1.5">
                                    <div class="flex gap-0.5">
                                        <template v-for="s in 5" :key="s">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" :fill="reviewStats.average >= s ? '#FBBF24' : (reviewStats.average >= s - 0.5 ? 'url(#hg_header)' : '#E5E7EB')">
                                                <defs><linearGradient id="hg_header" x1="0" x2="1" y1="0" y2="0"><stop offset="50%" stop-color="#FBBF24"/><stop offset="50%" stop-color="#E5E7EB"/></linearGradient></defs>
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <span class="text-xs font-bold text-gray-800">{{ reviewStats.average }}</span>
                                    <span class="text-xs text-gray-400">({{ reviewStats.total }})</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div v-if="doctor.qualification" class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                                {{ doctor.qualification }}
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ doctor.experience_years }}+ years experience
                            </div>
                            <div v-if="doctor.location" class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ doctor.location }}
                            </div>
                            <div v-if="doctor.insurance && doctor.insurance.length" class="flex items-start gap-2 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3 4h-6l3-4zm0 6v7" />
                                </svg>
                                <div>
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="ins in doctor.insurance" :key="ins" class="inline-block rounded-full bg-gray-100 px-3 py-0.5 text-sm font-medium text-gray-700">
                                            {{ ins }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">Accepted insurance</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-100 pt-5">
                            <p class="text-sm font-semibold text-gray-900">Consultation Fee</p>
                            <p class="mt-1 text-2xl font-bold text-orange-600">₱{{ doctor.consultation_fee }}</p>
                        </div>
                    </div>

                    <!-- Bio -->
                    <div v-if="doctor.bio" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <h3 class="font-semibold text-gray-900">About</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">{{ doctor.bio }}</p>
                    </div>

                    <!-- Clinic map -->
                    <div v-if="doctor.latitude && doctor.longitude" class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <div class="border-b border-gray-100 px-4 py-3">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900">
                                <svg class="h-4 w-4 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                </svg>
                                Clinic Location
                            </h3>
                        </div>
                        <div class="p-3">
                            <ClinicMapView
                                :lat="doctor.latitude"
                                :lng="doctor.longitude"
                                :doctor-name="doctor.name"
                                :address="doctor.location"
                            />
                        </div>
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

                <!-- Booking Panel + Reviews -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm">
                        <!-- Step indicator -->
                        <div class="flex items-center border-b border-gray-100 px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold" :class="step === 1 ? 'bg-orange-600 text-white' : 'bg-green-500 text-white'">
                                    <span v-if="step === 1">1</span>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <span class="text-sm font-medium" :class="step === 1 ? 'text-gray-900' : 'text-gray-500'">Select Date & Time</span>
                            </div>
                            <div class="mx-4 h-px flex-1 bg-gray-200" />
                            <div class="flex items-center gap-3">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold" :class="step === 2 ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-400'">
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
                                                'bg-orange-600 text-white shadow-md shadow-orange-200': selectedDate === d.date,
                                                'cursor-not-allowed text-gray-300': !d.available,
                                                'text-gray-700 hover:bg-orange-50 hover:text-orange-700': d.available && selectedDate !== d.date,
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
                                        {{ formatSelectedDate(selectedDate, { weekday: 'long', month: 'short', day: 'numeric' }) }}
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
                                            'border-orange-600 bg-orange-600 text-white shadow-md': selectedTime === time,
                                            'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-300 line-through': isSlotBooked(time),
                                            'border-gray-200 text-gray-700 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700': !isSlotBooked(time) && selectedTime !== time,
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
                                    class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-orange-700 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    Continue
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Patient Details Form -->
                        <form v-if="step === 2" @submit.prevent="submitBooking" class="p-6">
                            <!-- Selected appointment summary -->
                            <div class="mb-6 flex items-center justify-between rounded-xl bg-orange-50 px-4 py-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">Your Appointment</p>
                                    <p class="mt-0.5 text-sm font-semibold text-orange-900">
                                        {{ formatSelectedDate(selectedDate, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) }}
                                        at {{ formatTime(selectedTime) }}
                                    </p>
                                </div>
                                <button type="button" @click="step = 1" class="text-xs font-semibold text-orange-600 hover:text-orange-700">Change</button>
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
                                            class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
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
                                            class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
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
                                        class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
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
                                        class="mt-1.5 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                    />
                                </div>
                            </div>

                            <!-- Error if already booked on this date -->
                            <div v-if="form.errors.appointment_date" class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                {{ form.errors.appointment_date }}
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
                                    class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-orange-700 disabled:opacity-60"
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
                            <p class="mt-2 text-center text-xs text-gray-400">
                                By confirming, you agree to our
                                <a href="/terms-of-service" target="_blank" class="font-medium text-gray-500 underline underline-offset-2 hover:text-orange-600">Terms of Service</a>
                                and
                                <a href="/privacy-policy" target="_blank" class="font-medium text-gray-500 underline underline-offset-2 hover:text-orange-600">Privacy Policy</a>.
                            </p>
                        </form>
                    </div>

                    <!-- ─── Reviews ─── -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-900">Patient Reviews</h3>
                            <span class="rounded-full bg-orange-50 px-2.5 py-0.5 text-xs font-semibold text-orange-700">{{ reviewStats.total }}</span>
                        </div>

                        <!-- Aggregate row -->
                        <div v-if="reviewStats.total > 0" class="mt-4">
                            <div class="flex items-end gap-3">
                                <span class="text-5xl font-extrabold leading-none text-gray-900">{{ reviewStats.average }}</span>
                                <div class="pb-1">
                                    <div class="flex gap-0.5">
                                        <template v-for="s in 5" :key="s">
                                            <svg v-if="starType(reviewStats.average, s) === 'full'" class="h-5 w-5" viewBox="0 0 24 24" fill="#FBBF24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                            <svg v-else-if="starType(reviewStats.average, s) === 'half'" class="h-5 w-5" viewBox="0 0 24 24">
                                                <defs><linearGradient id="hg_agg" x1="0" x2="1" y1="0" y2="0"><stop offset="50%" stop-color="#FBBF24"/><stop offset="50%" stop-color="#E5E7EB"/></linearGradient></defs>
                                                <path fill="url(#hg_agg)" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="#E5E7EB"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                        </template>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-500">{{ reviewStats.total }} review{{ reviewStats.total !== 1 ? 's' : '' }}</p>
                                </div>
                            </div>

                            <!-- Bar breakdown -->
                            <div class="mt-4 space-y-1.5">
                                <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-2">
                                    <span class="w-3 text-right text-xs font-semibold text-gray-500">{{ star }}</span>
                                    <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="#FBBF24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-amber-400 transition-all duration-500" :style="{ width: ratingBarWidth(star) }"></div>
                                    </div>
                                    <span class="w-6 text-right text-xs text-gray-400">{{ reviewStats.counts[star] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="mt-3 text-sm text-gray-400">No reviews yet. Be the first!</p>

                        <!-- Write a review CTA -->
                        <button
                            @click="showReviewForm = !showReviewForm"
                            class="mt-5 w-full rounded-xl border border-orange-200 bg-orange-50 py-2.5 text-sm font-semibold text-orange-700 transition hover:bg-orange-100"
                        >
                            {{ showReviewForm ? 'Cancel' : '✏ Write a Review' }}
                        </button>

                        <!-- Review form -->
                        <form v-if="showReviewForm" @submit.prevent="submitReview" class="mt-4 space-y-4">
                            <!-- Success flash -->
                            <div v-if="reviewSuccess" class="flex items-center gap-2 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-700">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ reviewSuccess }}
                            </div>

                            <!-- Rate-limit error -->
                            <div v-if="rateLimitError" class="flex items-center gap-2 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                {{ rateLimitError }}
                            </div>

                            <!-- Star picker -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Your Rating <span class="text-red-500">*</span></label>
                                <div class="mt-2 flex items-center gap-1">
                                    <button
                                        v-for="s in 5"
                                        :key="s"
                                        type="button"
                                        @click="reviewForm.rating = s"
                                        @mouseenter="hoverRating = s"
                                        @mouseleave="hoverRating = 0"
                                        class="transition-transform hover:scale-110"
                                    >
                                        <svg class="h-8 w-8" viewBox="0 0 24 24" :fill="(hoverRating || reviewForm.rating) >= s ? '#FBBF24' : '#E5E7EB'">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </button>
                                    <span v-if="hoverRating || reviewForm.rating" class="ml-2 text-sm font-medium text-gray-600">
                                        {{ starLabel(hoverRating || reviewForm.rating) }}
                                    </span>
                                </div>
                                <p v-if="reviewForm.errors.rating" class="mt-1 text-xs text-red-500">{{ reviewForm.errors.rating }}</p>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Your Name <span class="text-red-500">*</span></label>
                                    <input v-model="reviewForm.patient_name" type="text" placeholder="Jane Smith" required
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                        :class="{ 'border-red-400': reviewForm.errors.patient_name }" />
                                    <p v-if="reviewForm.errors.patient_name" class="mt-1 text-xs text-red-500">{{ reviewForm.errors.patient_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                    <input v-model="reviewForm.patient_email" type="email" placeholder="you@example.com" required
                                        class="mt-1 w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                                        :class="{ 'border-red-400': reviewForm.errors.patient_email }" />
                                    <p v-if="reviewForm.errors.patient_email" class="mt-1 text-xs text-red-500">{{ reviewForm.errors.patient_email }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Comment <span class="text-gray-400">(Optional)</span></label>
                                <textarea v-model="reviewForm.comment" rows="3" placeholder="Share your experience..."
                                    class="mt-1 w-full resize-none rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100" />
                            </div>

                            <!-- hCaptcha widget (production only) -->
                            <div v-if="IS_PRODUCTION">
                                <VueHcaptcha
                                    ref="hcaptchaRef"
                                    :sitekey="HCAPTCHA_SITEKEY"
                                    theme="light"
                                    @verify="onCaptchaVerified"
                                    @expired="onCaptchaExpired"
                                    @error="onCaptchaExpired"
                                />
                                <p v-if="reviewForm.errors.hcaptcha_token" class="mt-1 text-xs text-red-500">
                                    {{ reviewForm.errors.hcaptcha_token }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="reviewForm.processing || reviewForm.rating === 0 || (IS_PRODUCTION && !reviewForm.hcaptcha_token)"
                                class="w-full rounded-xl bg-orange-600 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                            >
                                {{ reviewForm.processing ? 'Submitting...' : 'Submit Review' }}
                            </button>
                        </form>

                        <!-- Review list -->
                        <div v-if="reviews.length" class="mt-5 space-y-4">
                            <div class="border-t border-gray-100 pt-4 text-xs font-semibold uppercase tracking-wider text-gray-400">Recent Reviews</div>
                            <div v-for="review in reviews" :key="review.id" class="rounded-xl bg-gray-50 p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ review.patient_name }}</p>
                                        <p class="text-xs text-gray-400">
                                            {{ new Date(review.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 gap-0.5">
                                        <template v-for="s in 5" :key="s">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" :fill="review.rating >= s ? '#FBBF24' : '#E5E7EB'">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </template>
                                    </div>
                                </div>
                                <p v-if="review.comment" class="mt-2 text-sm leading-relaxed text-gray-600">{{ review.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </GuestLayout>
</template>
