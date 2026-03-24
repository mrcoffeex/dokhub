<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor, DoctorSchedule } from '@/types';

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
    form.patch('/doctor/schedule');
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

        <div class="mx-auto max-w-3xl space-y-6">

            <!-- Summary strip -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm dark:border-violet-900/30 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Working Days</p>
                    <p class="mt-1.5 text-3xl font-bold text-violet-600 dark:text-violet-400">{{ activeDaysCount() }}</p>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-600">days per week</p>
                </div>
                <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm dark:border-sky-900/30 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Avg. Slots/Day</p>
                    <p class="mt-1.5 text-3xl font-bold text-sky-600 dark:text-sky-400">
                        {{ activeDaysCount() ? Math.round(form.schedules.reduce((t, s) => t + slotsPerDay(s), 0) / activeDaysCount()) : 0 }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-600">appointments</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-600">Weekly Capacity</p>
                    <p class="mt-1.5 text-3xl font-bold text-emerald-600 dark:text-emerald-400">
                        {{ form.schedules.reduce((t, s) => t + slotsPerDay(s), 0) }}
                    </p>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-600">total slots</p>
                </div>
            </div>

            <!-- Schedule form -->
            <form @submit.prevent="submit">
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <div>
                            <h2 class="font-semibold text-gray-900 dark:text-white">Weekly Schedule</h2>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Toggle each day on/off and set individual hours.</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <span v-if="form.processing">Saving…</span>
                            <span v-else>Save Schedule</span>
                        </button>
                    </div>

                    <!-- Day rows -->
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div
                            v-for="(schedule, idx) in form.schedules"
                            :key="schedule.day_of_week"
                            class="px-6 py-4 transition-colors"
                            :class="schedule.is_active ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/60 dark:bg-gray-800/30'"
                        >
                            <div class="flex flex-wrap items-center gap-4">

                                <!-- Toggle + day name -->
                                <div class="flex w-32 shrink-0 items-center gap-3">
                                    <button
                                        type="button"
                                        @click="schedule.is_active = !schedule.is_active"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                        :class="schedule.is_active ? 'bg-violet-600' : 'bg-gray-200 dark:bg-gray-700'"
                                        :aria-label="`Toggle ${DAY_NAMES[schedule.day_of_week]}`"
                                    >
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200"
                                            :class="schedule.is_active ? 'translate-x-5' : 'translate-x-0'"
                                        />
                                    </button>
                                    <span class="text-sm font-semibold" :class="schedule.is_active ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600'">
                                        {{ DAY_NAMES[schedule.day_of_week] }}
                                    </span>
                                </div>

                                <!-- Time + slot (disabled when inactive) -->
                                <div class="flex flex-1 flex-wrap items-center gap-3" :class="{ 'pointer-events-none opacity-40': !schedule.is_active }">
                                    <!-- Start time -->
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-gray-500 dark:text-gray-500">From</label>
                                        <input
                                            v-model="schedule.start_time"
                                            type="time"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                        />
                                    </div>
                                    <!-- End time -->
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-gray-500 dark:text-gray-500">To</label>
                                        <input
                                            v-model="schedule.end_time"
                                            type="time"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                        />
                                    </div>
                                    <!-- Slot duration -->
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-gray-500 dark:text-gray-500">Slot</label>
                                        <select
                                            v-model.number="schedule.slot_duration_minutes"
                                            class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-700 outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-violet-500"
                                        >
                                            <option v-for="opt in SLOT_OPTIONS" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Slot count badge + copy button -->
                                <div class="flex items-center gap-3">
                                    <span
                                        v-if="schedule.is_active"
                                        class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-600 dark:bg-violet-900/30 dark:text-violet-400"
                                    >
                                        {{ slotsPerDay(schedule) }} slots
                                    </span>
                                    <span v-else class="text-xs text-gray-300 dark:text-gray-700">Off</span>

                                    <button
                                        v-if="schedule.is_active"
                                        type="button"
                                        @click="applyToAll(schedule)"
                                        class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-500 transition hover:border-violet-300 hover:text-violet-600 dark:border-gray-700 dark:text-gray-500 dark:hover:border-violet-700 dark:hover:text-violet-400"
                                        title="Apply these times to all active days"
                                    >
                                        Apply to all
                                    </button>
                                </div>
                            </div>

                            <!-- Validation errors -->
                            <p v-if="form.errors[`schedules.${idx}.end_time`]" class="mt-2 text-xs text-red-500">
                                {{ form.errors[`schedules.${idx}.end_time`] }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-800/30">
                        <p class="text-xs text-gray-400 dark:text-gray-600">
                            Changes take effect immediately for new bookings.
                        </p>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <span v-if="form.processing">Saving…</span>
                            <span v-else>Save Schedule</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </DoctorLayout>
</template>
