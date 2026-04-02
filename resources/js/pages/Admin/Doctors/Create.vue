<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { toast } from 'vue-sonner';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    specialization: [] as string[],
    insurance: [] as string[],
    qualification: '',
    bio: '',
    experience_years: 0,
    consultation_fee: 0,
    location: '',
    status: 'approved',
    password: '',
    password_confirmation: '',
    schedules: [] as {
        day_of_week: number;
        start_time: string;
        end_time: string;
        slot_duration_minutes: number;
    }[],
});

const props = defineProps<{
    specializations: string[];
    insurances: string[];
}>();

const insuranceOptions = props.insurances;

const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const selectedDays = ref<number[]>([1, 2, 3, 4, 5]); // Default Mon-Fri

function toggleDay(day: number) {
    if (selectedDays.value.includes(day)) {
        selectedDays.value = selectedDays.value.filter(d => d !== day);
    } else {
        selectedDays.value = [...selectedDays.value, day].sort((a, b) => a - b);
    }
}

const scheduleSettings = ref({ start_time: '09:00', end_time: '17:00', slot_duration_minutes: 30 });

function submit() {
    form.schedules = selectedDays.value.map(day => ({
        day_of_week: day,
        ...scheduleSettings.value,
    }));
    form.post('/admin/doctors', {
        onError: () => {
            toast.error('Could not create doctor', {
                description: 'Please review the highlighted fields and try again.',
                duration: 5000,
            });
        },
    });
}
</script>

<template>
    <Head title="Add Doctor" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <a href="/admin/doctors" class="flex items-center gap-1.5 text-gray-400 transition hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Doctors
                </a>
                <span class="text-gray-300 dark:text-gray-600">/</span>
                <span class="font-semibold text-gray-900 dark:text-white">Add Doctor</span>
            </div>
        </template>

        <div class="mx-auto max-w-3xl pb-24">
            <form @submit.prevent="submit" class="space-y-5">

                <!-- ─── Doctor Information ─── -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <!-- Section header -->
                    <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/30">
                            <svg class="h-4 w-4 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Doctor Information</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Personal and professional details</p>
                        </div>
                    </div>

                    <div class="space-y-5 p-6">
                        <!-- Name + Email -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="Jane Smith"
                                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:ring-2 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                    :class="form.errors.name
                                        ? 'border-red-400 focus:border-red-400 focus:ring-red-100 dark:border-red-500 dark:focus:ring-red-900/30'
                                        : 'border-gray-200 focus:border-orange-400 focus:ring-orange-100 dark:border-gray-700 dark:focus:border-orange-500 dark:focus:ring-orange-900/30'"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    placeholder="jane@clinic.com"
                                    class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:ring-2 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                    :class="form.errors.email
                                        ? 'border-red-400 focus:border-red-400 focus:ring-red-100 dark:border-red-500 dark:focus:ring-red-900/30'
                                        : 'border-gray-200 focus:border-orange-400 focus:ring-orange-100 dark:border-gray-700 dark:focus:border-orange-500 dark:focus:ring-orange-900/30'"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <!-- Phone + Location -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="+1 555 000 0000"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                <input
                                    v-model="form.location"
                                    type="text"
                                    placeholder="Manila, Philippines"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                        </div>

                        <!-- Specialization + Qualification -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Specialization <span class="text-red-500">*</span>
                                </label>
                                <div
                                    class="max-h-48 overflow-y-auto rounded-xl border p-3 transition dark:bg-gray-800"
                                    :class="form.errors.specialization
                                        ? 'border-red-400 dark:border-red-500'
                                        : 'border-gray-200 dark:border-gray-700'"
                                >
                                    <div class="flex flex-wrap gap-1.5">
                                        <button
                                            v-for="s in props.specializations" :key="s"
                                            type="button"
                                            @click="form.specialization.includes(s) ? form.specialization = form.specialization.filter(x => x !== s) : form.specialization.push(s)"
                                            class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                            :class="form.specialization.includes(s)
                                                ? 'border-orange-500 bg-orange-600 text-white dark:border-orange-400'
                                                : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:text-orange-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-orange-500'"
                                        >
                                            {{ s }}
                                        </button>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                    {{ form.specialization.length ? form.specialization.length + ' selected' : 'Select one or more' }}
                                </p>
                                <p v-if="form.errors.specialization" class="mt-1 text-xs text-red-500">{{ form.errors.specialization }}</p>
                            </div>
                               <div>
                                   <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Accepted Insurance</label>
                                   <div class="mt-1.5 max-h-48 overflow-y-auto rounded-xl border p-3 dark:border-gray-700 dark:bg-gray-800"
                                       :class="form.errors.insurance ? 'border-red-400 dark:border-red-500' : 'border-gray-200'">
                                       <div class="flex flex-wrap gap-1.5">
                                           <button
                                               v-for="i in insuranceOptions" :key="i"
                                               type="button"
                                               @click="form.insurance.includes(i) ? form.insurance = form.insurance.filter(x => x !== i) : form.insurance.push(i)"
                                               class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                               :class="form.insurance.includes(i)
                                                   ? 'border-orange-500 bg-orange-600 text-white'
                                                   : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:text-orange-600 dark:border-gray-600 dark:text-gray-300'"
                                           >
                                               {{ i }}
                                           </button>
                                       </div>
                                   </div>
                                   <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ form.insurance.length ? form.insurance.length + ' selected' : 'Select one or more' }}</p>
                                   <p v-if="form.errors.insurance" class="mt-1 text-xs text-red-500">{{ form.errors.insurance }}</p>
                               </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Qualification</label>
                                <input
                                    v-model="form.qualification"
                                    type="text"
                                    placeholder="e.g. MD, FACC"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                        </div>

                        <!-- Experience + Fee -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Experience (years)</label>
                                <input
                                    v-model.number="form.experience_years"
                                    type="number"
                                    min="0"
                                    max="60"
                                    placeholder="0"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee (₱)</label>
                                <input
                                    v-model.number="form.consultation_fee"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                                />
                            </div>
                        </div>

                        <!-- Bio -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                            <textarea
                                v-model="form.bio"
                                rows="3"
                                placeholder="Brief description of the doctor's background and expertise…"
                                class="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            />
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Account Status</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="opt in [
                                        { value: 'approved',  label: 'Approved',  active: 'bg-emerald-600 text-white shadow-sm', inactive: 'border border-gray-200 text-gray-600 hover:border-emerald-300 hover:text-emerald-700 dark:border-gray-700 dark:text-gray-400 dark:hover:border-emerald-700 dark:hover:text-emerald-300' },
                                        { value: 'pending',   label: 'Pending',   active: 'bg-amber-500 text-white shadow-sm',   inactive: 'border border-gray-200 text-gray-600 hover:border-amber-300 hover:text-amber-700 dark:border-gray-700 dark:text-gray-400 dark:hover:border-amber-700 dark:hover:text-amber-300' },
                                        { value: 'suspended', label: 'Suspended', active: 'bg-red-500 text-white shadow-sm',     inactive: 'border border-gray-200 text-gray-600 hover:border-red-300 hover:text-red-700 dark:border-gray-700 dark:text-gray-400 dark:hover:border-red-700 dark:hover:text-red-300' },
                                    ]"
                                    :key="opt.value"
                                    type="button"
                                    @click="form.status = opt.value"
                                    class="rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-150"
                                    :class="form.status === opt.value ? opt.active : opt.inactive"
                                >
                                    {{ opt.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Login Credentials ─── -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 dark:bg-sky-900/30">
                            <svg class="h-4 w-4 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Login Credentials</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">The doctor will use these to access their portal</p>
                        </div>
                    </div>

                    <div class="grid gap-4 p-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                placeholder="Min. 8 characters"
                                class="w-full rounded-xl border bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:ring-2 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                :class="form.errors.password
                                    ? 'border-red-400 focus:border-red-400 focus:ring-red-100 dark:border-red-500 dark:focus:ring-red-900/30'
                                    : 'border-gray-200 focus:border-orange-400 focus:ring-orange-100 dark:border-gray-700 dark:focus:border-orange-500 dark:focus:ring-orange-900/30'"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirm Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                placeholder="Repeat password"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/30"
                            />
                        </div>
                    </div>
                </div>

                <!-- ─── Weekly Schedule ─── -->
                <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                            <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Weekly Schedule</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Set working days and available hours</p>
                        </div>
                    </div>

                    <div class="space-y-5 p-6">
                        <!-- Working days -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Working Days</label>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="(day, idx) in days"
                                    :key="idx"
                                    type="button"
                                    @click="toggleDay(idx)"
                                    class="flex h-10 w-10 flex-col items-center justify-center rounded-xl text-xs font-bold transition-all duration-150"
                                    :class="selectedDays.includes(idx)
                                        ? 'bg-orange-600 text-white shadow-sm'
                                        : 'border border-gray-200 text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700 dark:text-gray-500 dark:hover:border-orange-700 dark:hover:text-orange-400'"
                                    :title="day"
                                >
                                    {{ day.charAt(0) }}
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-400 dark:text-gray-600">
                                {{ selectedDays.length === 0 ? 'No days selected' : `${selectedDays.length} day${selectedDays.length > 1 ? 's' : ''} selected: ${selectedDays.map(d => days[d].slice(0,3)).join(', ')}` }}
                            </p>
                        </div>

                        <!-- Time range -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Working Hours</label>
                            <div class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50/80 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800">
                                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <input
                                    v-model="scheduleSettings.start_time"
                                    type="time"
                                    class="w-24 bg-transparent text-sm text-gray-900 outline-none dark:text-gray-100"
                                />
                                <span class="text-gray-300 dark:text-gray-600">→</span>
                                <input
                                    v-model="scheduleSettings.end_time"
                                    type="time"
                                    class="w-24 bg-transparent text-sm text-gray-900 outline-none dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <!-- Slot duration -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Slot Duration</label>
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="opt in [
                                        { value: 15, label: '15 min' },
                                        { value: 20, label: '20 min' },
                                        { value: 30, label: '30 min' },
                                        { value: 45, label: '45 min' },
                                        { value: 60, label: '1 hour' },
                                    ]"
                                    :key="opt.value"
                                    type="button"
                                    @click="scheduleSettings.slot_duration_minutes = opt.value"
                                    class="rounded-lg px-3.5 py-2 text-xs font-semibold transition-all duration-150"
                                    :class="scheduleSettings.slot_duration_minutes === opt.value
                                        ? 'bg-orange-600 text-white shadow-sm'
                                        : 'border border-gray-200 text-gray-500 hover:border-orange-300 hover:text-orange-600 dark:border-gray-700 dark:text-gray-500 dark:hover:border-orange-700 dark:hover:text-orange-400'"
                                >
                                    {{ opt.label }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <!-- Sticky footer actions -->
        <div class="fixed bottom-0 right-0 z-30 border-t border-gray-100 bg-white/90 px-6 py-4 backdrop-blur-sm lg:left-64 dark:border-gray-800 dark:bg-gray-900/90">
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-4">
                <p class="hidden text-xs text-gray-400 dark:text-gray-500 sm:block">
                    Fields marked <span class="text-red-500">*</span> are required
                </p>
                <div class="flex items-center gap-3 ml-auto">
                    <a
                        href="/admin/doctors"
                        class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        @click="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ form.processing ? 'Saving…' : 'Save Doctor' }}
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>
