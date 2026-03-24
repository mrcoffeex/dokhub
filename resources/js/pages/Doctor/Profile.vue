<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor } from '@/types';
import { ref, computed } from 'vue';

const props = defineProps<{ doctor: Doctor }>();

const form = useForm({
    name:             props.doctor.name ?? '',
    phone:            props.doctor.phone ?? '',
    specialization:   props.doctor.specialization ?? '',
    qualification:    props.doctor.qualification ?? '',
    bio:              props.doctor.bio ?? '',
    experience_years: props.doctor.experience_years ?? '',
    consultation_fee: props.doctor.consultation_fee ?? '',
    location:         props.doctor.location ?? '',
    languages:        Array.isArray(props.doctor.languages) ? props.doctor.languages.join(', ') : (props.doctor.languages ?? ''),
});

const successMsg = ref('');

function submit() {
    // Convert comma-separated languages string back to array
    const langs = form.languages
        .split(',')
        .map((l: string) => l.trim())
        .filter(Boolean);

    form.transform((data) => ({ ...data, languages: langs }))
        .patch('/doctor/profile', {
            onSuccess: () => {
                successMsg.value = 'Profile updated successfully.';
                setTimeout(() => { successMsg.value = ''; }, 3000);
            },
        });
}

const initials = computed(() =>
    props.doctor.name?.split(' ').map((w: string) => w[0]).slice(0, 2).join('').toUpperCase() ?? '?'
);
</script>

<template>
    <Head title="My Profile" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">My Profile</h1>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">

            <!-- Avatar & name header -->
            <div class="flex items-center gap-5 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-2xl font-bold text-white shadow-md">
                    {{ initials }}
                </div>
                <div>
                    <p class="text-base font-bold text-gray-900 dark:text-white">Dr. {{ doctor.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ doctor.specialization ?? 'Doctor' }}</p>
                    <span
                        class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="doctor.status === 'approved'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                    >
                        {{ doctor.status === 'approved' ? 'Approved' : 'Pending Approval' }}
                    </span>
                </div>
            </div>

            <!-- Success banner -->
            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-1" leave-active-class="transition ease-in duration-150" leave-to-class="opacity-0">
                <div v-if="successMsg" class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ successMsg }}
                </div>
            </Transition>

            <!-- Form -->
            <form @submit.prevent="submit" class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="font-semibold text-gray-900 dark:text-white">Personal Information</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Update your professional details below.</p>
                </div>

                <div class="grid gap-5 p-6 sm:grid-cols-2">

                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Dr. Jane Smith"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                            :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="+1 (555) 000-0000"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                        <input
                            v-model="form.specialization"
                            type="text"
                            placeholder="e.g. Cardiology"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.specialization" class="mt-1 text-xs text-red-500">{{ form.errors.specialization }}</p>
                    </div>

                    <!-- Qualification -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Qualification</label>
                        <input
                            v-model="form.qualification"
                            type="text"
                            placeholder="e.g. MBBS, MD"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.qualification" class="mt-1 text-xs text-red-500">{{ form.errors.qualification }}</p>
                    </div>

                    <!-- Experience years -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Experience (years)</label>
                        <input
                            v-model.number="form.experience_years"
                            type="number"
                            min="0"
                            max="80"
                            placeholder="0"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.experience_years" class="mt-1 text-xs text-red-500">{{ form.errors.experience_years }}</p>
                    </div>

                    <!-- Consultation fee -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee ($)</label>
                        <input
                            v-model.number="form.consultation_fee"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="0.00"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.consultation_fee" class="mt-1 text-xs text-red-500">{{ form.errors.consultation_fee }}</p>
                    </div>

                    <!-- Location -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Location / Clinic Address</label>
                        <input
                            v-model="form.location"
                            type="text"
                            placeholder="e.g. 123 Main St, New York, NY"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.location" class="mt-1 text-xs text-red-500">{{ form.errors.location }}</p>
                    </div>

                    <!-- Languages -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Languages Spoken</label>
                        <input
                            v-model="form.languages"
                            type="text"
                            placeholder="English, Spanish, French"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Comma-separated list</p>
                        <p v-if="form.errors.languages" class="mt-1 text-xs text-red-500">{{ form.errors.languages }}</p>
                    </div>

                    <!-- Bio -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Bio</label>
                        <textarea
                            v-model="form.bio"
                            rows="4"
                            placeholder="Brief professional summary…"
                            class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.bio" class="mt-1 text-xs text-red-500">{{ form.errors.bio }}</p>
                    </div>

                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" leave-active-class="transition ease-in duration-100" leave-to-class="opacity-0">
                        <span v-if="successMsg" class="text-sm text-emerald-600 dark:text-emerald-400">Saved!</span>
                    </Transition>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-violet-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-gray-900"
                    >
                        <span v-if="form.processing">Saving…</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>

        </div>
    </DoctorLayout>
</template>
