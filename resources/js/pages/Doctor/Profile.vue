<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import ClinicMapPicker from '@/components/ClinicMapPicker.vue';
import type { Doctor, EducationEntry, AffiliationEntry, CertificationEntry } from '@/types';
import { toast } from 'vue-sonner';

const props = defineProps<{ doctor: Doctor; specializations: string[]; insurances: string[] }>();

const insuranceOptions = props.insurances;

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
    slug:             props.doctor.slug ?? '',
    phone:            props.doctor.phone ?? '',
    specialization:   Array.isArray(props.doctor.specialization) ? [...props.doctor.specialization] : [],
    qualification:    props.doctor.qualification ?? '',
    bio:              props.doctor.bio ?? '',
    experience_years: props.doctor.experience_years ?? '',
    consultation_fee: props.doctor.consultation_fee ?? '',
    appointment_modes: Array.isArray(props.doctor.appointment_modes) && props.doctor.appointment_modes.length
        ? [...props.doctor.appointment_modes]
        : (['in_person', 'online'] as ('in_person' | 'online')[]),
    location:         props.doctor.location ?? '',
    latitude:         props.doctor.latitude ?? null as number | null,
    longitude:        props.doctor.longitude ?? null as number | null,
    languages:        Array.isArray(props.doctor.languages) ? props.doctor.languages.join(', ') : (props.doctor.languages ?? ''),
    insurance:        Array.isArray(props.doctor.insurance) ? [...props.doctor.insurance] : [],
});

function submit() {
    // Convert comma-separated languages string back to array
    const langs = form.languages
        .split(',')
        .map((l: string) => l.trim())
        .filter(Boolean);

    form.transform((data) => ({ ...data, languages: langs }))
        .patch('/doctor/profile', {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Profile updated', {
                    description: 'Your changes have been saved successfully.',
                    duration: 4000,
                });
            },
            onError: () => {
                toast.error('Could not save changes', {
                    description: 'Please review the highlighted fields and try again.',
                    duration: 5000,
                });
            },
        });
}

const initials = computed(() =>
    props.doctor.name?.split(' ').map((w: string) => w[0]).slice(0, 2).join('').toUpperCase() ?? '?'
);

// ── Credentials form ─────────────────────────────────────────────
const credentialsForm = useForm({
    education:      (props.doctor.education      ?? []).map((e: EducationEntry)      => ({ ...e })) as EducationEntry[],
    affiliations:   (props.doctor.affiliations   ?? []).map((a: AffiliationEntry)   => ({ ...a })) as AffiliationEntry[],
    certifications: (props.doctor.certifications ?? []).map((c: CertificationEntry) => ({ ...c })) as CertificationEntry[],
});

function addEducation()            { credentialsForm.education.push({ degree: '', institution: '', year: '' }); }
function removeEducation(i: number){ credentialsForm.education.splice(i, 1); }

function addAffiliation()            { credentialsForm.affiliations.push({ name: '', role: '' }); }
function removeAffiliation(i: number){ credentialsForm.affiliations.splice(i, 1); }

function addCertification()            { credentialsForm.certifications.push({ name: '', issuer: '', year: '' }); }
function removeCertification(i: number){ credentialsForm.certifications.splice(i, 1); }

function saveCredentials() {
    credentialsForm.patch('/doctor/profile/credentials', {
        preserveScroll: true,
        onSuccess: () => toast.success('Credentials updated', { description: 'Your changes have been saved.', duration: 4000 }),
        onError:   () => toast.error('Could not save credentials', { description: 'Please review the highlighted fields.', duration: 5000 }),
    });
}
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
                        class="group relative h-20 w-20 cursor-pointer overflow-hidden rounded-full ring-2 ring-orange-200 dark:ring-orange-800"
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
                            class="flex h-20 w-20 items-center justify-center bg-gradient-to-br from-orange-500 to-indigo-600 text-2xl font-bold text-white"
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
                        class="absolute -bottom-1 -right-1 flex h-6 w-6 items-center justify-center rounded-full border-2 border-white bg-orange-600 dark:border-gray-900"
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
                                class="flex items-center gap-1.5 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-orange-700 disabled:opacity-60"
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

                    <!-- Public Profile Link -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Public Profile Link</label>
                        <div class="flex rounded-xl border border-gray-200 bg-gray-50 focus-within:border-orange-400 focus-within:ring-2 focus-within:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:focus-within:border-orange-500 dark:focus-within:ring-orange-900/40"
                            :class="{ 'border-red-400 focus-within:border-red-400 focus-within:ring-red-100': form.errors.slug }"
                        >
                            <span class="flex items-center rounded-l-xl border-r border-gray-200 bg-gray-100 px-3 text-sm text-gray-500 select-none dark:border-gray-700 dark:bg-gray-700 dark:text-gray-400">
                                /doctors/
                            </span>
                            <input
                                :value="form.slug"
                                @input="form.slug = ($event.target as HTMLInputElement).value.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '').slice(0, 50)"
                                type="text"
                                maxlength="50"
                                placeholder="your-slug"
                                class="min-w-0 flex-1 rounded-r-xl bg-transparent px-4 py-2.5 text-sm text-gray-900 outline-none dark:text-gray-100 dark:placeholder-gray-500"
                                :class="{ 'text-red-500': form.errors.slug }"
                            />
                            <a
                                :href="`/doctors/${form.slug}`"
                                target="_blank"
                                class="flex items-center gap-1 rounded-r-xl px-3 text-sm text-orange-600 transition hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300"
                                title="Open public profile"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        </div>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-600">Lowercase letters, numbers, and hyphens only</p>
                        <p v-if="form.errors.slug" class="mt-1 text-xs text-red-500">{{ form.errors.slug }}</p>
                    </div>

                    <!-- Name -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Dr. Jane Smith"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
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
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                    </div>

                    <!-- Qualification -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Qualification</label>
                        <input
                            v-model="form.qualification"
                            type="text"
                            placeholder="e.g. MBBS, MD"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                        />
                        <p v-if="form.errors.qualification" class="mt-1 text-xs text-red-500">{{ form.errors.qualification }}</p>
                    </div>

                    <!-- Specialization -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                        <div class="max-h-48 overflow-y-auto rounded-xl border p-3 transition dark:bg-gray-800"
                            :class="form.errors.specialization ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700'"
                        >
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="s in props.specializations" :key="s"
                                    type="button"
                                    @click="form.specialization.includes(s) ? form.specialization = form.specialization.filter((x: string) => x !== s) : form.specialization.push(s)"
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

                    <!-- Insurance -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Accepted Insurance</label>
                        <div class="max-h-48 overflow-y-auto rounded-xl border p-3 transition dark:bg-gray-800"
                            :class="form.errors.insurance ? 'border-red-400 dark:border-red-500' : 'border-gray-200 dark:border-gray-700'">
                            <div class="flex flex-wrap gap-1.5">
                                <button
                                    v-for="s in insuranceOptions" :key="s"
                                    type="button"
                                    @click="form.insurance.includes(s) ? form.insurance = form.insurance.filter(x => x !== s) : form.insurance.push(s)"
                                    class="rounded-lg border px-2.5 py-1 text-xs font-medium transition"
                                    :class="form.insurance.includes(s)
                                        ? 'border-orange-500 bg-orange-600 text-white dark:border-orange-400'
                                        : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:text-orange-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-orange-500'"
                                >
                                    {{ s }}
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                            {{ form.insurance.length ? form.insurance.length + ' selected' : 'Select one or more' }}
                        </p>
                        <p v-if="form.errors.insurance" class="mt-1 text-xs text-red-500">{{ form.errors.insurance }}</p>
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
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
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
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                        />
                        <p v-if="form.errors.consultation_fee" class="mt-1 text-xs text-red-500">{{ form.errors.consultation_fee }}</p>
                    </div>

                    <!-- Consultation modes -->
                    <div class="sm:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Modes</label>
                        <p class="mb-3 text-xs text-gray-400 dark:text-gray-500">Select the types of consultation you offer to patients.</p>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Clinic Visit toggle -->
                            <button
                                type="button"
                                @click="() => {
                                    const i = form.appointment_modes.indexOf('in_person');
                                    if (i === -1) form.appointment_modes.push('in_person');
                                    else if (form.appointment_modes.length > 1) form.appointment_modes.splice(i, 1);
                                }"
                                class="group relative flex items-center gap-3 rounded-xl border-2 px-4 py-3.5 text-left transition-all duration-150"
                                :class="form.appointment_modes.includes('in_person')
                                    ? 'border-orange-500 bg-orange-50 dark:border-orange-500 dark:bg-orange-950/30'
                                    : 'border-gray-200 bg-white hover:border-orange-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-orange-700'"
                            >
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors"
                                    :class="form.appointment_modes.includes('in_person') ? 'bg-orange-500 text-white' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" :class="form.appointment_modes.includes('in_person') ? 'text-orange-700 dark:text-orange-300' : 'text-gray-800 dark:text-gray-200'">Clinic Visit</p>
                                    <p class="text-xs" :class="form.appointment_modes.includes('in_person') ? 'text-orange-500 dark:text-orange-400' : 'text-gray-400 dark:text-gray-500'">In-person appointment</p>
                                </div>
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full transition-colors"
                                    :class="form.appointment_modes.includes('in_person') ? 'bg-orange-500 text-white' : 'border-2 border-gray-300 dark:border-gray-600'"
                                >
                                    <svg v-if="form.appointment_modes.includes('in_person')" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </button>

                            <!-- Online consultation toggle -->
                            <button
                                type="button"
                                @click="() => {
                                    const i = form.appointment_modes.indexOf('online');
                                    if (i === -1) form.appointment_modes.push('online');
                                    else if (form.appointment_modes.length > 1) form.appointment_modes.splice(i, 1);
                                }"
                                class="group relative flex items-center gap-3 rounded-xl border-2 px-4 py-3.5 text-left transition-all duration-150"
                                :class="form.appointment_modes.includes('online')
                                    ? 'border-indigo-500 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-950/30'
                                    : 'border-gray-200 bg-white hover:border-indigo-200 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-indigo-700'"
                            >
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors"
                                    :class="form.appointment_modes.includes('online') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold" :class="form.appointment_modes.includes('online') ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-800 dark:text-gray-200'">Online</p>
                                    <p class="text-xs" :class="form.appointment_modes.includes('online') ? 'text-indigo-500 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'">Video or phone call</p>
                                </div>
                                <span
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full transition-colors"
                                    :class="form.appointment_modes.includes('online') ? 'bg-indigo-600 text-white' : 'border-2 border-gray-300 dark:border-gray-600'"
                                >
                                    <svg v-if="form.appointment_modes.includes('online')" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">At least one mode must be selected.</p>
                        <p v-if="form.errors.appointment_modes" class="mt-1 text-xs text-red-500">{{ form.errors.appointment_modes }}</p>
                    </div>

                    <!-- Location + Map Pin -->
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Location / Clinic Address</label>
                        <input
                            v-model="form.location"
                            type="text"
                            placeholder="e.g. 123 Main St, Makati City"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                        />
                        <p v-if="form.errors.location" class="mt-1 text-xs text-red-500">{{ form.errors.location }}</p>

                        <!-- Map pin picker -->
                        <div class="mt-3 rounded-xl border border-dashed border-orange-200 bg-orange-50/50 p-4 dark:border-orange-800 dark:bg-orange-950/20">
                            <div class="mb-3 flex items-center gap-2">
                                <svg class="h-4 w-4 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                </svg>
                                <span class="text-sm font-semibold text-orange-700 dark:text-orange-300">Pin Clinic on Map</span>
                                <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-900 dark:text-orange-300">Optional</span>
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
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
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
                            class="w-full resize-y rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                        />
                        <p v-if="form.errors.bio" class="mt-1 text-xs text-red-500">{{ form.errors.bio }}</p>
                    </div>

                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-gray-900"
                    >
                        <span v-if="form.processing">Saving…</span>
                        <span v-else>Save Changes</span>
                    </button>
                </div>
            </form>

            <!-- ── Education ─────────────────────────────────────────────── -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="font-semibold text-gray-900 dark:text-white">Education</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Degrees and academic qualifications.</p>
                    </div>
                    <button type="button" @click="addEducation"
                        class="flex items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-950/30 dark:text-orange-400 dark:hover:bg-orange-950/60">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add
                    </button>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-if="credentialsForm.education.length === 0" class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-600">No education entries yet. Click "Add" to get started.</p>
                    </div>
                    <div v-for="(entry, idx) in credentialsForm.education" :key="idx" class="grid gap-4 p-6 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Degree <span class="text-red-500">*</span></label>
                            <input v-model="entry.degree" type="text" placeholder="e.g. MD, MBBS" required
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400': credentialsForm.errors['education.' + idx + '.degree'] }" />
                            <p v-if="credentialsForm.errors['education.' + idx + '.degree']" class="mt-1 text-xs text-red-500">{{ credentialsForm.errors['education.' + idx + '.degree'] }}</p>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Institution <span class="text-red-500">*</span></label>
                            <input v-model="entry.institution" type="text" placeholder="University / School" required
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400': credentialsForm.errors['education.' + idx + '.institution'] }" />
                            <p v-if="credentialsForm.errors['education.' + idx + '.institution']" class="mt-1 text-xs text-red-500">{{ credentialsForm.errors['education.' + idx + '.institution'] }}</p>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Year</label>
                                <input v-model.number="entry.year" type="number" :min="1900" :max="new Date().getFullYear() + 1" placeholder="2020"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                    :class="{ 'border-red-400': credentialsForm.errors['education.' + idx + '.year'] }" />
                            </div>
                            <button type="button" @click="removeEducation(idx)" title="Remove"
                                class="mt-[1.625rem] flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-100 text-red-400 transition hover:bg-red-50 dark:border-red-900/40 dark:hover:bg-red-900/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    <button type="button" :disabled="credentialsForm.processing" @click="saveCredentials"
                        class="rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-gray-900">
                        <span v-if="credentialsForm.processing">Saving…</span>
                        <span v-else>Save Education</span>
                    </button>
                </div>
            </div>

            <!-- ── Affiliations ──────────────────────────────────────────────── -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="font-semibold text-gray-900 dark:text-white">Affiliations</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Hospitals, clinics, or professional bodies.</p>
                    </div>
                    <button type="button" @click="addAffiliation"
                        class="flex items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-950/30 dark:text-orange-400 dark:hover:bg-orange-950/60">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add
                    </button>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-if="credentialsForm.affiliations.length === 0" class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-600">No affiliations yet. Click "Add" to get started.</p>
                    </div>
                    <div v-for="(entry, idx) in credentialsForm.affiliations" :key="idx" class="grid gap-4 p-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Organization / Hospital <span class="text-red-500">*</span></label>
                            <input v-model="entry.name" type="text" placeholder="e.g. St. Luke's Medical Center" required
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400': credentialsForm.errors['affiliations.' + idx + '.name'] }" />
                            <p v-if="credentialsForm.errors['affiliations.' + idx + '.name']" class="mt-1 text-xs text-red-500">{{ credentialsForm.errors['affiliations.' + idx + '.name'] }}</p>
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Role / Position</label>
                                <input v-model="entry.role" type="text" placeholder="e.g. Attending Physician"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40" />
                            </div>
                            <button type="button" @click="removeAffiliation(idx)" title="Remove"
                                class="mt-[1.625rem] flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-100 text-red-400 transition hover:bg-red-50 dark:border-red-900/40 dark:hover:bg-red-900/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    <button type="button" :disabled="credentialsForm.processing" @click="saveCredentials"
                        class="rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-gray-900">
                        <span v-if="credentialsForm.processing">Saving…</span>
                        <span v-else>Save Affiliations</span>
                    </button>
                </div>
            </div>

            <!-- ── Certifications ────────────────────────────────────────────── -->
            <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="font-semibold text-gray-900 dark:text-white">Certifications</h2>
                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Board certifications and professional credentials.</p>
                    </div>
                    <button type="button" @click="addCertification"
                        class="flex items-center gap-1.5 rounded-lg border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 transition hover:bg-orange-100 dark:border-orange-800 dark:bg-orange-950/30 dark:text-orange-400 dark:hover:bg-orange-950/60">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add
                    </button>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-if="credentialsForm.certifications.length === 0" class="px-6 py-8 text-center">
                        <p class="text-sm text-gray-400 dark:text-gray-600">No certifications yet. Click "Add" to get started.</p>
                    </div>
                    <div v-for="(entry, idx) in credentialsForm.certifications" :key="idx" class="grid gap-4 p-6 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Certification Name <span class="text-red-500">*</span></label>
                            <input v-model="entry.name" type="text" placeholder="e.g. Board Certified Cardiologist" required
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400': credentialsForm.errors['certifications.' + idx + '.name'] }" />
                            <p v-if="credentialsForm.errors['certifications.' + idx + '.name']" class="mt-1 text-xs text-red-500">{{ credentialsForm.errors['certifications.' + idx + '.name'] }}</p>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Issuing Organization</label>
                            <input v-model="entry.issuer" type="text" placeholder="e.g. PRC, AMA"
                                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40" />
                        </div>
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-400">Year</label>
                                <input v-model.number="entry.year" type="number" :min="1900" :max="new Date().getFullYear() + 1" placeholder="2022"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                    :class="{ 'border-red-400': credentialsForm.errors['certifications.' + idx + '.year'] }" />
                            </div>
                            <button type="button" @click="removeCertification(idx)" title="Remove"
                                class="mt-[1.625rem] flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-red-100 text-red-400 transition hover:bg-red-50 dark:border-red-900/40 dark:hover:bg-red-900/20">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-100 px-6 py-4 dark:border-gray-800">
                    <button type="button" :disabled="credentialsForm.processing" @click="saveCredentials"
                        class="rounded-xl bg-orange-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 disabled:opacity-60 dark:focus:ring-offset-gray-900">
                        <span v-if="credentialsForm.processing">Saving…</span>
                        <span v-else>Save Certifications</span>
                    </button>
                </div>
            </div>

        </div>
    </DoctorLayout>
</template>
