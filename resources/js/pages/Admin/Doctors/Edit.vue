<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Dialog,
    DialogTrigger,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Doctor } from '@/types';
import { toast } from 'vue-sonner';

const props = defineProps<{
    doctor: Doctor;
    specializations: string[];
    insurances: string[];
}>();

const form = useForm({
    name: props.doctor.name,
    email: props.doctor.email,
    phone: props.doctor.phone ?? '',
    specialization: Array.isArray(props.doctor.specialization)
        ? [...props.doctor.specialization]
        : [],
    qualification: props.doctor.qualification ?? '',
    bio: props.doctor.bio ?? '',
    experience_years: props.doctor.experience_years,
    consultation_fee: parseFloat(props.doctor.consultation_fee),
    location: props.doctor.location ?? '',
    status: props.doctor.status,
    schedules:
        props.doctor.schedules?.map((s) => ({
            day_of_week: s.day_of_week,
            start_time: s.start_time,
            end_time: s.end_time,
            slot_duration_minutes: s.slot_duration_minutes,
        })) ?? [],
    insurance: Array.isArray(props.doctor.insurance)
        ? [...props.doctor.insurance]
        : [],
});

const insuranceOptions = props.insurances;

const days = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
];

const selectedDays = ref<number[]>(
    props.doctor.schedules
        ?.filter((s) => s.is_active)
        .map((s) => s.day_of_week) ?? [1, 2, 3, 4, 5],
);

const existingSchedule = props.doctor.schedules?.[0];

function trimTime(t?: string) {
    return t ? t.slice(0, 5) : undefined;
}

const scheduleSettings = ref({
    start_time: trimTime(existingSchedule?.start_time) ?? '09:00',
    end_time: trimTime(existingSchedule?.end_time) ?? '17:00',
    slot_duration_minutes: existingSchedule?.slot_duration_minutes ?? 30,
});

function toggleDay(day: number) {
    if (selectedDays.value.includes(day)) {
        selectedDays.value = selectedDays.value.filter((d) => d !== day);
    } else {
        selectedDays.value = [...selectedDays.value, day].sort((a, b) => a - b);
    }
}

function submit() {
    form.schedules = selectedDays.value.map((day) => ({
        day_of_week: day,
        ...scheduleSettings.value,
    }));
    form.put(`/admin/doctors/${props.doctor.slug}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Changes saved successfully.');
        },
        onError: () => {
            toast.error('Failed to save. Please check the fields below.');
        },
    });
}
</script>

<template>
    <Head :title="`Edit Dr. ${doctor.name}`" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <a
                    href="/admin/doctors"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >Doctors</a
                >
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Edit Dr. {{ doctor.name }}
                </h1>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2
                        class="mb-5 font-semibold text-gray-900 dark:text-white"
                    >
                        Doctor Information
                    </h2>

                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Full Name
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                    :class="{
                                        'border-red-400': form.errors.name,
                                    }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Email
                                    <span class="text-red-500">*</span></label
                                >
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                    :class="{
                                        'border-red-400': form.errors.email,
                                    }"
                                />
                                <p
                                    v-if="form.errors.email"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Phone</label
                                >
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Location</label
                                >
                                <input
                                    v-model="form.location"
                                    type="text"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Specialization
                                    <span class="text-red-500">*</span></label
                                >
                                <div
                                    class="mt-1.5 max-h-48 overflow-y-auto rounded-xl border border-gray-200 p-3 dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="s in props.specializations"
                                            :key="s"
                                            type="button"
                                            @click="
                                                form.specialization.includes(s)
                                                    ? (form.specialization =
                                                          form.specialization.filter(
                                                              (x) => x !== s,
                                                          ))
                                                    : form.specialization.push(
                                                          s,
                                                      )
                                            "
                                            class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                            :class="
                                                form.specialization.includes(s)
                                                    ? 'border-orange-500 bg-orange-600 text-white'
                                                    : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:text-orange-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-orange-500 dark:hover:text-orange-400'
                                            "
                                        >
                                            {{ s }}
                                        </button>
                                    </div>
                                </div>
                                <p
                                    class="mt-1 text-xs text-gray-400 dark:text-gray-500"
                                >
                                    {{
                                        form.specialization.length
                                            ? form.specialization.length +
                                              ' selected'
                                            : 'Select one or more'
                                    }}
                                </p>
                                <p
                                    v-if="form.errors.specialization"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.specialization }}
                                </p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Accepted Insurance</label
                                >
                                <div
                                    class="mt-1.5 max-h-48 overflow-y-auto rounded-xl border p-3 dark:border-gray-700 dark:bg-gray-800"
                                    :class="
                                        form.errors.insurance
                                            ? 'border-red-400 dark:border-red-500'
                                            : 'border-gray-200'
                                    "
                                >
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="i in insuranceOptions"
                                            :key="i"
                                            type="button"
                                            @click="
                                                form.insurance.includes(i)
                                                    ? (form.insurance =
                                                          form.insurance.filter(
                                                              (x) => x !== i,
                                                          ))
                                                    : form.insurance.push(i)
                                            "
                                            class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                            :class="
                                                form.insurance.includes(i)
                                                    ? 'border-orange-500 bg-orange-600 text-white'
                                                    : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:text-orange-600 dark:border-gray-600 dark:text-gray-300'
                                            "
                                        >
                                            {{ i }}
                                        </button>
                                    </div>
                                </div>
                                <p
                                    class="mt-1 text-xs text-gray-400 dark:text-gray-500"
                                >
                                    {{
                                        form.insurance.length
                                            ? form.insurance.length +
                                              ' selected'
                                            : 'Select one or more'
                                    }}
                                </p>
                                <p
                                    v-if="form.errors.insurance"
                                    class="mt-1 text-xs text-red-500"
                                >
                                    {{ form.errors.insurance }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Qualification</label
                                >
                                <input
                                    v-model="form.qualification"
                                    type="text"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                    >Experience (years)</label
                                >
                                <input
                                    v-model.number="form.experience_years"
                                    type="number"
                                    min="0"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Bio</label
                            >
                            <textarea
                                v-model="form.bio"
                                rows="3"
                                class="mt-1.5 w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Status</label
                            >
                            <select
                                v-model="form.status"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            >
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                >
                    <h2
                        class="mb-5 font-semibold text-gray-900 dark:text-white"
                    >
                        Weekly Schedule
                    </h2>
                    <div class="mb-5">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Working Days</label
                        >
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="(day, idx) in days"
                                :key="idx"
                                type="button"
                                @click="toggleDay(idx)"
                                class="rounded-xl border px-4 py-2 text-sm font-medium transition-all"
                                :class="
                                    selectedDays.includes(idx)
                                        ? 'border-orange-600 bg-orange-600 text-white shadow-sm'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300 dark:border-gray-700 dark:text-gray-300 dark:hover:border-gray-500'
                                "
                            >
                                {{ day.slice(0, 3) }}
                            </button>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Start Time</label
                            >
                            <input
                                v-model="scheduleSettings.start_time"
                                type="time"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >End Time</label
                            >
                            <input
                                v-model="scheduleSettings.end_time"
                                type="time"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                                >Slot Duration</label
                            >
                            <select
                                v-model.number="
                                    scheduleSettings.slot_duration_minutes
                                "
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 transition outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            >
                                <option :value="15">15 minutes</option>
                                <option :value="20">20 minutes</option>
                                <option :value="30">30 minutes</option>
                                <option :value="45">45 minutes</option>
                                <option :value="60">1 hour</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a
                        href="/admin/doctors"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                        >Cancel</a
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-700 disabled:opacity-60"
                    >
                        <svg
                            v-if="form.processing"
                            class="h-4 w-4 animate-spin"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                            />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>

            <!-- ── ID Documents panel (read-only, for admin review) ── -->
            <div v-if="doctor.id_documents && doctor.id_documents.length"
                class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    <h2 class="font-semibold text-gray-900 dark:text-white">Submitted ID Documents</h2>
                    <span class="ml-auto rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-semibold text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                        {{ doctor.id_documents.length }} file{{ doctor.id_documents.length !== 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <a
                        v-for="(path, i) in doctor.id_documents"
                        :key="i"
                        :href="`/admin/doctors/${doctor.slug}/id-docs/${path.split('/').pop()}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group relative block aspect-[4/3] overflow-hidden rounded-xl border border-gray-200 bg-gray-100 transition hover:border-orange-400 dark:border-gray-700 dark:bg-gray-800"
                        :title="`View ID document ${i + 1}`"
                    >
                        <img
                            :src="`/admin/doctors/${doctor.slug}/id-docs/${path.split('/').pop()}`"
                            :alt="`ID document ${i + 1}`"
                            class="h-full w-full object-cover transition group-hover:scale-105"
                        />
                        <div class="absolute inset-0 flex items-end bg-gradient-to-t from-black/40 to-transparent p-2 opacity-0 transition group-hover:opacity-100">
                            <span class="text-xs font-medium text-white">Open full size ↗</span>
                        </div>
                        <div class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-gray-600 shadow dark:bg-gray-900/90 dark:text-gray-300">
                            ID {{ i + 1 }}
                        </div>
                    </a>
                </div>

                <p v-if="doctor.status === 'pending'" class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800/40 dark:bg-amber-900/20 dark:text-amber-400">
                    ⚠ Review the ID documents above before approving this doctor's account.
                </p>
            </div>

            <div v-else-if="doctor.status === 'pending'"
                class="rounded-2xl border border-red-100 bg-red-50 p-5 shadow-sm dark:border-red-900/40 dark:bg-red-950/20">
                <p class="text-sm font-medium text-red-700 dark:text-red-400">⚠ No ID documents were submitted with this registration.</p>
            </div>

        </div>
    </AdminLayout>
</template>
