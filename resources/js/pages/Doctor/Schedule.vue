<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor, DoctorSchedule } from '@/types';
import { toast } from 'vue-sonner';

type ScheduleEntry = {
    day_of_week: number;
    is_active: boolean;
    start_time: string;
    end_time: string;
    slot_duration_minutes: number;
};

const props = defineProps<{
    doctor: Doctor;
    schedules: ScheduleEntry[];
}>();

const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const DAY_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const SLOT_OPTIONS = [
    { value: 15, label: '15 min' },
    { value: 20, label: '20 min' },
    { value: 30, label: '30 min' },
    { value: 45, label: '45 min' },
    { value: 60, label: '1 hour' },
];

const form = useForm({
    schedules: props.schedules.map(s => ({ ...s })),
});

function submit() {
    form.patch('/doctor/schedule', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Schedule saved', {
                description: 'Your working hours have been updated successfully.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not save schedule', {
                description: 'Please review the fields and try again.',
                duration: 5000,
            });
        },
    });
}

function applyToAll(source: ScheduleEntry) {
    form.schedules.forEach(s => {
        s.start_time = source.start_time;
        s.end_time = source.end_time;
        s.slot_duration_minutes = source.slot_duration_minutes;
    });
}

function activeDaysCount() {
    return form.schedules.filter(s => s.is_active).length;
}

function slotsPerDay(s: ScheduleEntry): number {
    if (!s.is_active) return 0;
    const [sh, sm] = s.start_time.split(':').map(Number);
    const [eh, em] = s.end_time.split(':').map(Number);
    const totalMins = (eh * 60 + em) - (sh * 60 + sm);
    return totalMins > 0 ? Math.floor(totalMins / s.slot_duration_minutes) : 0;
}
</script>

<template>
    <Head title="My Schedule" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">My Schedule</h1>
        </template>

        <div class="mx-auto max-w-4xl space-y-5">

            <!-- Summary strip -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 sm:gap-4">
                <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm dark:border-orange-900/30 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Working Days</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-orange-600 dark:text-orange-400">{{ activeDaysCount() }}</p>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-600">days per week</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm dark:border-sky-900/30 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Avg. Slots/Day</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-50 dark:bg-sky-900/30">
                            <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-sky-600 dark:text-sky-400">
                        {{ activeDaysCount() ? Math.round(form.schedules.reduce((t, s) => t + slotsPerDay(s), 0) / activeDaysCount()) : 0 }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-600">appointments</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-600">Weekly Capacity</p>
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                            <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                    </div>
                    <p class="mt-3 text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ form.schedules.reduce((t, s) => t + slotsPerDay(s), 0) }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-600">total slots</p>
                </div>
            </div>

            <!-- Schedule form -->
            <form @submit.prevent="submit">
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

                    <!-- Header -->
                    <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Weekly Schedule</h2>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Toggle days on/off and configure hours per day.</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <svg v-if="!form.processing" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span>{{ form.processing ? 'Saving…' : 'Save Schedule' }}</span>
                        </button>
                    </div>

                    <!-- Week strip -->
                    <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 bg-gray-50/60 px-5 py-3 sm:px-6 dark:border-gray-800 dark:bg-gray-800/30">
                        <p class="mr-2 text-xs font-medium text-gray-400 dark:text-gray-600">Week</p>
                        <button
                            v-for="s in form.schedules"
                            :key="s.day_of_week"
                            type="button"
                            @click="s.is_active = !s.is_active"
                            class="flex h-9 w-9 flex-col items-center justify-center rounded-xl text-xs font-bold transition-all duration-150"
                            :class="s.is_active
                                ? 'bg-orange-600 text-white shadow-sm'
                                : 'bg-white text-gray-400 border border-gray-200 hover:border-orange-300 hover:text-orange-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-600 dark:hover:text-orange-400'"
                            :title="DAY_NAMES[s.day_of_week]"
                        >
                            {{ DAY_SHORT[s.day_of_week].charAt(0) }}
                        </button>
                        <p class="ml-auto text-xs text-gray-400 dark:text-gray-600">Click to toggle</p>
                    </div>

                    <!-- Day rows -->
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div
                            v-for="(schedule, idx) in form.schedules"
                            :key="schedule.day_of_week"
                            class="relative flex flex-col gap-3 px-5 py-4 transition-colors sm:flex-row sm:flex-wrap sm:items-start sm:gap-4 sm:px-6"
                            :class="schedule.is_active
                                ? 'bg-white dark:bg-gray-900'
                                : 'bg-gray-50/40 dark:bg-gray-800/20'"
                        >
                            <!-- Active indicator bar -->
                            <div
                                class="absolute inset-y-0 left-0 w-1 rounded-r-sm transition-colors duration-200"
                                :class="schedule.is_active ? 'bg-orange-500' : 'bg-transparent'"
                            ></div>

                            <!-- Toggle + day name -->
                            <div class="flex w-full shrink-0 items-center gap-3 pl-2 sm:w-36">
                                <button
                                    type="button"
                                    @click="schedule.is_active = !schedule.is_active"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                    :class="schedule.is_active ? 'bg-orange-600' : 'bg-gray-200 dark:bg-gray-700'"
                                    :aria-label="`Toggle ${DAY_NAMES[schedule.day_of_week]}`"
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                        :class="schedule.is_active ? 'translate-x-5' : 'translate-x-0'"
                                    />
                                </button>
                                <div>
                                    <p class="text-sm font-semibold leading-none" :class="schedule.is_active ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600'">
                                        {{ DAY_NAMES[schedule.day_of_week] }}
                                    </p>
                                    <p v-if="schedule.is_active" class="mt-0.5 text-[11px] text-orange-500 dark:text-orange-400">
                                        {{ slotsPerDay(schedule) }} slots
                                    </p>
                                    <p v-else class="mt-0.5 text-[11px] text-gray-300 dark:text-gray-700">Day off</p>
                                </div>
                            </div>

                            <!-- Time range + slot duration -->
                            <div
                                class="flex flex-1 flex-col gap-3 transition-opacity duration-200 sm:flex-row sm:flex-wrap sm:items-center"
                                :class="{ 'pointer-events-none opacity-30': !schedule.is_active }"
                            >
                                <!-- Time range -->
                                <div class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-gray-50/80 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <input
                                        v-model="schedule.start_time"
                                        type="time"
                                        class="w-24 bg-transparent text-sm text-gray-900 outline-none dark:text-gray-100"
                                    />
                                    <span class="text-gray-300 dark:text-gray-600">→</span>
                                    <input
                                        v-model="schedule.end_time"
                                        type="time"
                                        class="w-24 bg-transparent text-sm text-gray-900 outline-none dark:text-gray-100"
                                    />
                                </div>

                                <!-- Slot duration pill buttons -->
                                <div class="flex flex-wrap items-center gap-1">
                                    <span class="mr-1 text-xs text-gray-400 dark:text-gray-600">Slot</span>
                                    <button
                                        v-for="opt in SLOT_OPTIONS"
                                        :key="opt.value"
                                        type="button"
                                        @click="schedule.slot_duration_minutes = opt.value"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition-all duration-150"
                                        :class="schedule.slot_duration_minutes === opt.value
                                            ? 'bg-orange-600 text-white shadow-sm'
                                            : 'border border-gray-200 text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700 dark:text-gray-500 dark:hover:text-orange-400'"
                                    >
                                        {{ opt.label }}
                                    </button>
                                </div>
                            </div>

                            <!-- Apply to all -->
                            <div class="flex shrink-0 items-center">
                                <button
                                    v-if="schedule.is_active"
                                    type="button"
                                    @click="applyToAll(schedule)"
                                    class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-2.5 py-1.5 text-xs font-medium text-gray-500 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 dark:border-gray-700 dark:text-gray-500 dark:hover:border-orange-700 dark:hover:bg-orange-900/20 dark:hover:text-orange-400"
                                    title="Copy these hours to all active days"
                                >
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Apply to all
                                </button>
                            </div>

                            <!-- Validation errors -->
                            <p v-if="form.errors[`schedules.${idx}.end_time`]" class="w-full pl-2 text-xs text-red-500">
                                {{ form.errors[`schedules.${idx}.end_time`] }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex flex-col gap-3 border-t border-gray-100 bg-gray-50/50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-gray-800 dark:bg-gray-800/30">
                        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-600">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Changes take effect immediately for new bookings.
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <svg v-if="!form.processing" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <span>{{ form.processing ? 'Saving…' : 'Save Schedule' }}</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </DoctorLayout>
</template>
