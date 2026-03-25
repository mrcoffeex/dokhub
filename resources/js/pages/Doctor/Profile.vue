<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import ClinicMapPicker from '@/components/ClinicMapPicker.vue';
import type { Doctor } from '@/types';
import { toast } from 'vue-sonner';

const SPECIALIZATIONS = [
    'Allergy & Immunology', 'Anesthesiology', 'Cardiology', 'Colorectal Surgery',
    'Critical Care Medicine', 'Dermatology', 'Emergency Medicine', 'Endocrinology',
    'Family Medicine', 'Gastroenterology', 'General Surgery', 'Geriatrics',
    'Hematology', 'Infectious Disease', 'Internal Medicine', 'Nephrology',
    'Neurology', 'Neurosurgery', 'Obstetrics & Gynecology', 'Oncology',
    'Ophthalmology', 'Orthopedic Surgery', 'Otolaryngology (ENT)', 'Pathology',
    'Pediatrics', 'Physical Medicine & Rehabilitation', 'Plastic Surgery',
    'Psychiatry', 'Pulmonology', 'Radiology', 'Rheumatology', 'Thoracic Surgery',
    'Urology', 'Vascular Surgery',
] as const;

const props = defineProps<{ doctor: Doctor }>();

// ── Avatar upload ─────────────────────────────────────────────
const fileInput = ref<HTMLInputElement | null>(null);
const avatarPreview = ref<string | null>(null);
const avatarForm = useForm({ avatar: null as File | null });
const avatarSuccess = ref('');
const avatarError = ref('');

function onFileChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        avatarError.value = 'Image must be smaller than 2 MB.';
        return;
    }
    avatarError.value = '';
    avatarForm.avatar = file;
    avatarPreview.value = URL.createObjectURL(file);
}

function uploadAvatar() {
    if (!avatarForm.avatar) return;
    avatarForm.post('/doctor/profile/avatar', {
        forceFormData: true,
        onSuccess: () => {
            avatarPreview.value = null;
            avatarForm.reset();
            avatarSuccess.value = 'Photo updated!';
            setTimeout(() => { avatarSuccess.value = ''; }, 3000);
        },
        onError: (errs) => {
            avatarError.value = errs.avatar ?? 'Upload failed.';
        },
    });
}

function removeAvatar() {
    if (!confirm('Remove your profile photo?')) return;
    router.delete('/doctor/profile/avatar', {
        onSuccess: () => {
            avatarPreview.value = null;
            avatarSuccess.value = 'Photo removed.';
            setTimeout(() => { avatarSuccess.value = ''; }, 3000);
        },
    });
}

function cancelPreview() {
    avatarPreview.value = null;
    avatarForm.reset();
    avatarError.value = '';
    if (fileInput.value) fileInput.value.value = '';
}

// ── Profile form ──────────────────────────────────────────────
const form = useForm({
    name:             props.doctor.name ?? '',
    phone:            props.doctor.phone ?? '',
    specialization:   Array.isArray(props.doctor.specialization) ? [...props.doctor.specialization] : [],
    qualification:    props.doctor.qualification ?? '',
    bio:              props.doctor.bio ?? '',
    experience_years: props.doctor.experience_years ?? '',
    consultation_fee: props.doctor.consultation_fee ?? '',
    location:         props.doctor.location ?? '',
    latitude:         props.doctor.latitude ?? null as number | null,
    longitude:        props.doctor.longitude ?? null as number | null,
    languages:        Array.isArray(props.doctor.languages) ? props.doctor.languages.join(', ') : (props.doctor.languages ?? ''),
});

function submit() {
    // Convert comma-separated languages string back to array
    const langs = form.languages
        .split(',')
        .map((l: string) => l.trim())
        .filter(Boolean);

    form.transform((data) => ({ ...data, languages: langs }))
        .patch('/doctor/profile', {
            onSuccess: () => {
                toast.success('Profile updated successfully.');
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
            <div class="flex flex-col gap-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:gap-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900">

                <!-- Avatar with upload overlay -->
                <div class="relative shrink-0">
                    <!-- Hidden file input -->
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="hidden"
                        @change="onFileChange"
                    />

                    <!-- Avatar circle -->
                    <div
                        class="group relative h-20 w-20 cursor-pointer overflow-hidden rounded-full ring-2 ring-violet-200 dark:ring-violet-800"
                        @click="fileInput?.click()"
                        title="Change photo"
                    >
                        <!-- Photo or initials -->
                        <img
                            v-if="avatarPreview || doctor.avatar"
                            :src="avatarPreview ?? doctor.avatar_url"
                            :alt="doctor.name"
                            class="h-20 w-20 object-cover"
                        />
                        <div
                            v-else
                            class="flex h-20 w-20 items-center justify-center bg-gradient-to-br from-violet-500 to-indigo-600 text-2xl font-bold text-white"
                        >
                            {{ initials }}
                        </div>

                        <!-- Camera overlay on hover -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-0.5 bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="text-[10px] font-semibold text-white">Change</span>
                        </div>
                    </div>

                    <!-- Uploading spinner badge -->
                    <div
                        v-if="avatarForm.processing"
                        class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-violet-600 dark:border-gray-900"
                    >
                        <svg class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                    </div>
                </div>

                <!-- Name / status + upload controls -->
                <div class="min-w-0 flex-1">
                    <p class="text-base font-bold text-gray-900 dark:text-white">Dr. {{ doctor.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ doctor.specialization?.length ? doctor.specialization.join(', ') : 'Doctor' }}</p>
                    <span
                        class="mt-1 inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                        :class="doctor.status === 'approved'
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                    >
                        {{ doctor.status === 'approved' ? 'Approved' : 'Pending Approval' }}
                    </span>

                    <!-- Avatar action buttons -->
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <!-- Preview pending upload -->
                        <template v-if="avatarPreview">
                            <button
                                type="button"
                                :disabled="avatarForm.processing"
                                @click="uploadAvatar"
                                class="flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-violet-700 disabled:opacity-60"
                            >
                                <svg v-if="!avatarForm.processing" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <svg v-else class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                {{ avatarForm.processing ? 'Uploading…' : 'Save Photo' }}
                            </button>
                            <button
                                type="button"
                                @click="cancelPreview"
                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-500 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                            >
                                Cancel
                            </button>
                        </template>

                        <!-- No preview: change / remove buttons -->
                        <template v-else>
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ doctor.avatar ? 'Change Photo' : 'Upload Photo' }}
                            </button>
                            <button
                                v-if="doctor.avatar"
                                type="button"
                                @click="removeAvatar"
                                class="flex items-center gap-1.5 rounded-lg border border-red-100 px-3 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-900/20"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Remove
                            </button>
                        </template>
                    </div>

                    <!-- Feedback messages -->
                    <Transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0" leave-active-class="transition ease-in duration-100" leave-to-class="opacity-0">
                        <p v-if="avatarSuccess" class="mt-2 text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ avatarSuccess }}</p>
                        <p v-else-if="avatarError" class="mt-2 text-xs font-medium text-red-500">{{ avatarError }}</p>
                    </Transition>
                    <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-600">JPG, PNG or WebP · max 2 MB</p>
                </div>
            </div>

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
                        <div class="max-h-48 overflow-y-auto rounded-xl border p-3 transition dark:bg-gray-800"
                            :class="form.errors.specialization ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700'"
                        >
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="s in SPECIALIZATIONS" :key="s"
                                    type="button"
                                    @click="form.specialization.includes(s) ? form.specialization = form.specialization.filter((x: string) => x !== s) : form.specialization.push(s)"
                                    class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                    :class="form.specialization.includes(s)
                                        ? 'border-violet-500 bg-violet-600 text-white dark:border-violet-400'
                                        : 'border-gray-200 text-gray-600 hover:border-violet-300 hover:text-violet-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-violet-500'"
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
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee (₱)</label>
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

                    <!-- Location + Map Pin -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Location / Clinic Address</label>
                        <input
                            v-model="form.location"
                            type="text"
                            placeholder="e.g. 123 Main St, Makati City"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                        />
                        <p v-if="form.errors.location" class="mt-1 text-xs text-red-500">{{ form.errors.location }}</p>

                        <!-- Map pin picker -->
                        <div class="mt-3 rounded-xl border border-dashed border-violet-200 bg-violet-50/50 p-4 dark:border-violet-800 dark:bg-violet-950/20">
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-violet-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                </svg>
                                <span class="text-sm font-semibold text-violet-700 dark:text-violet-300">Pin Clinic on Map</span>
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-600 dark:bg-violet-900 dark:text-violet-300">Optional</span>
                            </div>
                            <ClinicMapPicker
                                :lat="form.latitude"
                                :lng="form.longitude"
                                @update:lat="form.latitude = $event"
                                @update:lng="form.longitude = $event"
                            />
                        </div>
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
