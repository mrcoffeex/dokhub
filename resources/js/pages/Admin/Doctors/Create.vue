<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    specialization: '',
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
    form.post('/admin/doctors');
}
</script>

<template>
    <Head title="Add Doctor" />
    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <a href="/admin/doctors" class="text-sm text-gray-500 hover:text-gray-700">Doctors</a>
                <span class="text-gray-300">/</span>
                <h1 class="text-lg font-semibold text-gray-900">Add Doctor</h1>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Info -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 font-semibold text-gray-900">Doctor Information</h2>

                    <div class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Full Name <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" required placeholder="Dr. Jane Smith"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                    :class="{ 'border-red-400': form.errors.name }" />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" required placeholder="jane@clinic.com"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                    :class="{ 'border-red-400': form.errors.email }" />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Phone</label>
                                <input v-model="form.phone" type="tel" placeholder="+1 555 000 0000"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Location</label>
                                <input v-model="form.location" type="text" placeholder="New York, NY"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Specialization <span class="text-red-500">*</span></label>
                                <input v-model="form.specialization" type="text" required placeholder="Cardiology"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                    :class="{ 'border-red-400': form.errors.specialization }" />
                                <p v-if="form.errors.specialization" class="mt-1 text-xs text-red-500">{{ form.errors.specialization }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Qualification</label>
                                <input v-model="form.qualification" type="text" placeholder="MD, FACC"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Experience (years)</label>
                                <input v-model.number="form.experience_years" type="number" min="0" max="60"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Consultation Fee ($)</label>
                                <input v-model.number="form.consultation_fee" type="number" min="0" step="0.01"
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bio</label>
                            <textarea v-model="form.bio" rows="3" placeholder="Brief description of the doctor's background and expertise..."
                                class="mt-1.5 w-full resize-none rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select v-model="form.status"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Login Credentials -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-1 font-semibold text-gray-900">Login Credentials</h2>
                    <p class="mb-5 text-sm text-gray-500">The doctor will use these to log into their portal.</p>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                            <input v-model="form.password" type="password" required placeholder="Min. 8 characters"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                                :class="{ 'border-red-400': form.errors.password }" />
                            <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm Password <span class="text-red-500">*</span></label>
                            <input v-model="form.password_confirmation" type="password" required placeholder="Repeat password"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                        </div>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-5 font-semibold text-gray-900">Weekly Schedule</h2>

                    <!-- Day picker -->
                    <div class="mb-5">
                        <label class="mb-2 block text-sm font-medium text-gray-700">Working Days</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="(day, idx) in days"
                                :key="idx"
                                type="button"
                                @click="toggleDay(idx)"
                                class="rounded-xl border px-4 py-2 text-sm font-medium transition-all"
                                :class="selectedDays.includes(idx)
                                    ? 'border-violet-600 bg-violet-600 text-white shadow-sm'
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                            >
                                {{ day.slice(0, 3) }}
                            </button>
                        </div>
                    </div>

                    <!-- Time settings -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input v-model="scheduleSettings.start_time" type="time"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">End Time</label>
                            <input v-model="scheduleSettings.end_time" type="time"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Slot Duration</label>
                            <select v-model.number="scheduleSettings.slot_duration_minutes"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                                <option :value="15">15 minutes</option>
                                <option :value="20">20 minutes</option>
                                <option :value="30">30 minutes</option>
                                <option :value="45">45 minutes</option>
                                <option :value="60">1 hour</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3">
                    <a href="/admin/doctors" class="rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-700 disabled:opacity-60"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        Save Doctor
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
