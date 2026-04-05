<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Spinner } from '@/components/ui/spinner';
import VueHcaptcha from '@hcaptcha/vue3-hcaptcha';
import {
    User, Mail, Phone, Stethoscope, GraduationCap, Briefcase,
    MapPin, FileText, Languages, Lock, ChevronLeft, CheckCircle2,
    IdCard, Camera, X, Upload,
} from 'lucide-vue-next';
import { ref, computed, onBeforeUnmount } from 'vue';
import { useAppearance } from '@/composables/useAppearance';

const { resolvedAppearance, updateAppearance } = useAppearance();

const IS_PRODUCTION = import.meta.env.PROD;
const HCAPTCHA_SITEKEY = import.meta.env.VITE_HCAPTCHA_SITEKEY as string;

const props = defineProps<{
    stats: { doctors: number; patients: number; rating: number };
    specializations: string[];
    insurances: string[];
    googlePending?: { google_id: string; name: string; email: string; avatar: string | null } | null;
}>();

const isGoogleSignup = computed(() => !! props.googlePending);

function formatCount(n: number): string {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(n % 1_000_000 === 0 ? 0 : 1) + 'M+';
    if (n >= 1_000) return (n / 1_000).toFixed(n % 1_000 === 0 ? 0 : 1) + 'K+';
    return String(n);
}

const step = ref(1);
const TOTAL_STEPS = 4;

// ── ID Document upload state ─────────────────────────────────────────────────
const idFiles = ref<File[]>([]);
const idPreviews = ref<string[]>([]);

function onIdFilePick(e: Event) {
    const input = e.target as HTMLInputElement;
    const picked = Array.from(input.files ?? []);
    addIdFiles(picked);
    input.value = '';
}

function addIdFiles(files: File[]) {
    for (const file of files) {
        if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) continue;
        if (file.size > 5 * 1024 * 1024) continue;
        if (idFiles.value.length >= 5) break;
        idFiles.value.push(file);
        idPreviews.value.push(URL.createObjectURL(file));
    }
}

function removeIdFile(i: number) {
    URL.revokeObjectURL(idPreviews.value[i]);
    idFiles.value.splice(i, 1);
    idPreviews.value.splice(i, 1);
}

// Camera capture
const showCamera = ref(false);
const cameraStream = ref<MediaStream | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
const cameraError = ref('');

async function openCamera() {
    cameraError.value = '';
    try {
        cameraStream.value = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        showCamera.value = true;
        // nextTick not needed - the v-if will mount the video once showCamera is true
        // We use a watcher-like approach via the setter
    } catch {
        cameraError.value = 'Could not access camera. Please allow camera permission or upload a file instead.';
    }
}

function onVideoMounted(el: HTMLVideoElement | null) {
    if (el && cameraStream.value) {
        el.srcObject = cameraStream.value;
        el.play();
    }
}

function captureFromCamera() {
    if (!videoRef.value) return;
    const canvas = document.createElement('canvas');
    canvas.width = videoRef.value.videoWidth;
    canvas.height = videoRef.value.videoHeight;
    canvas.getContext('2d')!.drawImage(videoRef.value, 0, 0);
    canvas.toBlob((blob) => {
        if (!blob) return;
        const file = new File([blob], `capture-${Date.now()}.jpg`, { type: 'image/jpeg' });
        addIdFiles([file]);
        closeCamera();
    }, 'image/jpeg', 0.9);
}

function closeCamera() {
    cameraStream.value?.getTracks().forEach(t => t.stop());
    cameraStream.value = null;
    showCamera.value = false;
}

onBeforeUnmount(closeCamera);

const form = ref({
    name: props.googlePending?.name ?? '',
    email: props.googlePending?.email ?? '',
    phone: '',
    specialization: [] as string[],
    insurance: [] as string[],
    qualification: '',
    bio: '',
    experience_years: '',
    consultation_fee: '',
    location: '',
    languages: 'English',
    password: '',
    password_confirmation: '',
});

const processing = ref(false);
const errors = ref<Record<string, string>>({});
const regCaptchaRef = ref<InstanceType<typeof VueHcaptcha> | null>(null);
const regCaptchaToken = ref('');

const STEPS = [
    {
        label: 'Personal',
        subtitle: 'Your name and contact details',
        heading: "Let's start with the basics.",
        sub: 'Tell us who you are so patients and DokHub can reach you.',
        icon: User,
        iconBg: 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-400',
        badge: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
        bullets: [
            'Your name appears on your public doctor profile',
            'Contact details help us notify you about bookings',
            'All personal data is encrypted and private',
        ],
    },
    {
        label: 'Credentials',
        subtitle: 'Specialization, qualifications & ID',
        heading: 'What are your specialties?',
        sub: 'Your credentials help patients find the right doctor. Upload a valid government-issued ID for verification.',
        icon: Stethoscope,
        iconBg: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400',
        badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        bullets: [
            'Profiles with verified specializations rank higher',
            'Patients search by specialty to find you',
            'ID upload is required for account verification',
        ],
    },
    {
        label: 'Practice',
        subtitle: 'Location, fees & professional bio',
        heading: 'Set up your practice.',
        sub: 'Help patients understand what to expect and where to find you.',
        icon: Briefcase,
        iconBg: 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
        badge: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        bullets: [
            'Complete profiles appear first in search results',
            'Transparent fees build patient confidence',
            'Your bio is the first thing new patients read',
        ],
    },
    {
        label: 'Security',
        subtitle: 'Create a password for your account',
        heading: 'Almost there!',
        sub: 'Secure your account and submit your application for review.',
        icon: Lock,
        iconBg: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400',
        badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        bullets: [
            'Use a strong password of 8+ characters',
            'Your profile is reviewed within 24 hours',
            'You can log in immediately after submitting',
        ],
    },
];

const currentStepConfig = computed(() => {
    const base = STEPS[step.value - 1];
    if (step.value === 4 && isGoogleSignup.value) {
        return {
            ...base,
            subtitle: 'Review your Google-connected account',
            sub: 'Your identity is verified via Google. Just submit your application for review.',
            bullets: [
                'Your Google account is securely linked',
                'No password required — sign in with Google',
                'Your profile is reviewed within 24 hours',
            ],
        };
    }
    return base;
});

const current = computed(() => currentStepConfig.value);
const progress = computed(() => Math.round(((step.value - 1) / (TOTAL_STEPS - 1)) * 100));

function toggleSpec(spec: string) {
    if (form.value.specialization.includes(spec)) {
        form.value.specialization = form.value.specialization.filter(x => x !== spec);
    } else {
        form.value.specialization.push(spec);
    }
}

function toggleInsurance(ins: string) {
    if (form.value.insurance.includes(ins)) {
        form.value.insurance = form.value.insurance.filter(x => x !== ins);
    } else {
        form.value.insurance.push(ins);
    }
}

function validateStep(): boolean {
    errors.value = {};
    if (step.value === 1) {
        if (!form.value.name.trim()) errors.value.name = 'Full name is required';
        if (!form.value.email.trim()) errors.value.email = 'Email is required';
        if (!form.value.phone.trim()) errors.value.phone = 'Phone number is required';
    } else if (step.value === 2) {
        if (!form.value.specialization.length) errors.value.specialization = 'Select at least one specialization';
        if (!form.value.qualification.trim()) errors.value.qualification = 'Qualification is required';
        if (!idFiles.value.length) errors.value.id_documents = 'At least one government-issued ID is required';
    } else if (step.value === 3) {
        const yrs = Number(form.value.experience_years);
        const fee = Number(form.value.consultation_fee);
        if (form.value.experience_years === '' || isNaN(yrs)) errors.value.experience_years = 'Required';
        else if (!Number.isInteger(yrs) || yrs < 0 || yrs > 70) errors.value.experience_years = 'Must be a whole number between 0 and 70';
        if (form.value.consultation_fee === '' || isNaN(fee)) errors.value.consultation_fee = 'Required';
        if (!form.value.location.trim()) errors.value.location = 'Location is required';
        if (!form.value.languages.trim()) errors.value.languages = 'At least one language is required';
        if (!form.value.bio.trim()) errors.value.bio = 'Professional bio is required';
    } else if (step.value === 4 && !isGoogleSignup.value) {
        if (!form.value.password) errors.value.password = 'Password is required';
        else if (form.value.password.length < 8) errors.value.password = 'Password must be at least 8 characters';
        if (!form.value.password_confirmation) errors.value.password_confirmation = 'Please confirm your password';
        if (form.value.password && form.value.password_confirmation && form.value.password !== form.value.password_confirmation) {
            errors.value.password_confirmation = 'Passwords do not match';
        }
    }
    return Object.keys(errors.value).length === 0;
}

function nextStep() {
    if (validateStep()) step.value++;
}

function prevStep() {
    errors.value = {};
    step.value--;
}

const STEP_FIELDS: Record<number, string[]> = {
    1: ['name', 'email', 'phone'],
    2: ['specialization', 'qualification', 'id_documents'],
    3: ['experience_years', 'consultation_fee', 'location', 'languages', 'bio', 'insurance'],
    4: ['password', 'password_confirmation'],
};

function stepForError(serverErrors: Record<string, string[]>): number {
    for (let s = 1; s <= TOTAL_STEPS; s++) {
        if (STEP_FIELDS[s].some((f) => serverErrors[f])) return s;
    }
    return TOTAL_STEPS;
}

async function handleSubmit() {
    if (!validateStep()) return;
    if (IS_PRODUCTION && HCAPTCHA_SITEKEY && !regCaptchaToken.value) {
        regCaptchaRef.value?.execute();
        return;
    }
    await doSubmit();
}

async function doSubmit() {
    processing.value = true;
    errors.value = {};
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const headers: Record<string, string> = {
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (csrfToken) headers['X-CSRF-Token'] = csrfToken;

        const fd = new FormData();
        // Scalar fields
        fd.append('name', form.value.name);
        fd.append('email', form.value.email);
        fd.append('phone', form.value.phone);
        fd.append('qualification', form.value.qualification);
        fd.append('bio', form.value.bio);
        fd.append('location', form.value.location);
        fd.append('languages', form.value.languages);
        const expYrs = (form.value.experience_years !== '' && !isNaN(Number(form.value.experience_years)))
            ? Math.floor(Number(form.value.experience_years)) : form.value.experience_years;
        const fee = (form.value.consultation_fee !== '' && !isNaN(Number(form.value.consultation_fee)))
            ? Number(form.value.consultation_fee) : form.value.consultation_fee;
        fd.append('experience_years', String(expYrs));
        fd.append('consultation_fee', String(fee));
        // Arrays
        form.value.specialization.forEach(s => fd.append('specialization[]', s));
        form.value.insurance.forEach(i => fd.append('insurance[]', i));
        // Files
        idFiles.value.forEach(f => fd.append('id_documents[]', f));
        // Auth fields
        if (!isGoogleSignup.value) {
            fd.append('password', form.value.password);
            fd.append('password_confirmation', form.value.password_confirmation);
        }
        if (regCaptchaToken.value) fd.append('hcaptcha_token', regCaptchaToken.value);

        const res = await fetch('/auth/signup/doctor', {
            method: 'POST',
            headers,
            body: fd,
        });
        const data = await res.json();
        if (!res.ok) {
            // Laravel returns errors as { field: [message, ...] } — flatten to { field: message }
            const serverErrors: Record<string, string[]> = data.errors ?? {};
            errors.value = Object.fromEntries(
                Object.entries(serverErrors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
            );
            if (!Object.keys(errors.value).length) {
                errors.value.form = data.message || 'Registration failed. Please try again.';
            }
            // Navigate to the first step that contains an errored field
            step.value = stepForError(serverErrors);
            regCaptchaRef.value?.reset();
            regCaptchaToken.value = '';
            processing.value = false;
            return;
        }
        window.location.href = '/auth/doctor-signup-success';
    } catch (err: any) {
        errors.value.form = err.message || 'An error occurred';
        regCaptchaRef.value?.reset();
        regCaptchaToken.value = '';
        processing.value = false;
    }
}

function onRegCaptchaVerified(token: string) {
    regCaptchaToken.value = token;
    doSubmit();
}

function onRegCaptchaExpired() {
    regCaptchaToken.value = '';
}
</script>

<template>
    <Head title="Doctor Registration — DokHub" />

    <div class="flex h-screen overflow-hidden">

        <!-- ── Left branding panel ─────────────────────────────────── -->
        <div class="relative hidden flex-col overflow-hidden bg-gradient-to-br from-orange-700 via-orange-600 to-indigo-700 lg:flex lg:w-5/12 xl:w-2/5">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
                <div class="absolute bottom-10 -right-16 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <div class="relative flex h-full flex-col justify-between p-10">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img src="/logo.png" alt="DokHub" class="h-10 w-auto" />
                    <div>
                        <p class="text-lg font-bold text-white">DokHub</p>
                        <p class="text-xs text-orange-200">Healthcare Platform</p>
                    </div>
                </div>

                <!-- Step tracker + dynamic content -->
                <div>
                    <!-- Step dots with connecting lines -->
                    <div class="mb-8 flex items-center">
                        <template v-for="(s, i) in STEPS" :key="i">
                            <div class="flex flex-col items-center">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full border-2 transition-all duration-300"
                                    :class="i + 1 < step
                                        ? 'border-white bg-white'
                                        : i + 1 === step
                                            ? 'border-white bg-white/20'
                                            : 'border-white/30 bg-transparent'">
                                    <CheckCircle2 v-if="i + 1 < step" class="h-5 w-5 text-orange-600" />
                                    <component v-else :is="s.icon" class="h-4 w-4 transition-colors"
                                        :class="i + 1 === step ? 'text-white' : 'text-white/30'" />
                                </div>
                                <span class="mt-1.5 text-[10px] font-semibold uppercase tracking-wide transition-colors"
                                    :class="i + 1 === step ? 'text-white' : i + 1 < step ? 'text-orange-200' : 'text-white/30'">
                                    {{ s.label }}
                                </span>
                            </div>
                            <div v-if="i < STEPS.length - 1" class="mb-5 h-px flex-1 mx-2 transition-all duration-500"
                                :class="i + 1 < step ? 'bg-white/70' : 'bg-white/20'"></div>
                        </template>
                    </div>

                    <!-- Dynamic heading + bullets per step -->
                    <Transition mode="out-in"
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 translate-y-3"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-3">
                        <div :key="step">
                            <h1 class="text-3xl font-bold leading-tight text-white">{{ current.heading }}</h1>
                            <p class="mt-3 text-sm leading-relaxed text-orange-200">{{ current.sub }}</p>
                            <ul class="mt-6 space-y-2.5">
                                <li v-for="bullet in current.bullets" :key="bullet"
                                    class="flex items-start gap-2.5 text-sm text-orange-100">
                                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-white/20">
                                        <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                    {{ bullet }}
                                </li>
                            </ul>
                        </div>
                    </Transition>

                    <!-- Stats -->
                    <div class="mt-8 grid grid-cols-3 gap-3">
                        <div v-for="stat in [
                            { value: formatCount(props.stats.doctors), label: 'Doctors' },
                            { value: formatCount(props.stats.patients), label: 'Patients' },
                            { value: props.stats.rating > 0 ? props.stats.rating.toFixed(1) + '★' : 'New★', label: 'Rating' },
                        ]" :key="stat.label" class="rounded-xl bg-white/10 px-2 py-3 text-center ring-1 ring-white/10">
                            <p class="text-base font-bold text-white">{{ stat.value }}</p>
                            <p class="text-xs text-orange-300">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Back link -->
                <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-orange-200 transition-colors hover:text-white">
                    <ChevronLeft class="h-4 w-4" />
                    Back to home
                </Link>
            </div>
        </div>

        <!-- ── Right form panel ───────────────────────────────────── -->
        <div class="relative flex flex-1 flex-col overflow-hidden bg-gray-50 dark:bg-gray-950">

            <!-- Desktop theme toggle (top-right corner) -->
            <button
                type="button"
                class="absolute right-4 top-4 z-10 hidden rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300 lg:block"
                :aria-label="resolvedAppearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                @click="updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark')"
            >
                <svg v-if="resolvedAppearance === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>

            <!-- Mobile top bar -->
            <div class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-800 dark:bg-gray-900 lg:hidden">
                <div class="flex items-center gap-2">
                    <img src="/logo.png" alt="DokHub" class="h-7 w-auto" />
                    <span class="text-sm font-bold text-gray-900 dark:text-white">DokHub</span>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        :aria-label="resolvedAppearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                        @click="updateAppearance(resolvedAppearance === 'dark' ? 'light' : 'dark')"
                    >
                        <svg v-if="resolvedAppearance === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <Link href="/" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">← Home</Link>
                </div>
            </div>

            <!-- Centered layout container -->
            <div class="flex flex-1 flex-col overflow-hidden px-5 py-5 sm:px-8 sm:py-6">
                <div class="mx-auto flex h-full w-full max-w-lg flex-col">

                    <!-- Top row: title + progress -->
                    <div class="mb-4 shrink-0 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Create your doctor profile</h2>
                            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">
                                Step {{ step }} of {{ TOTAL_STEPS }} — {{ current.label }}
                            </p>
                        </div>
                        <!-- Progress pill -->
                        <div class="shrink-0 flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="h-1.5 w-20 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-full rounded-full bg-orange-600 transition-all duration-500"
                                    :style="{ width: progress + '%' }"></div>
                            </div>
                            <span class="text-xs font-semibold text-orange-600 dark:text-orange-400">{{ progress }}%</span>
                        </div>
                    </div>

                    <!-- Mobile step bar -->
                    <div class="mb-4 flex shrink-0 gap-1 lg:hidden">
                        <div v-for="(s, i) in STEPS" :key="i"
                            class="flex-1 rounded-full py-0.5 transition-all duration-300"
                            :class="i + 1 <= step ? 'bg-orange-500' : 'bg-gray-200 dark:bg-gray-700'">
                        </div>
                    </div>

                    <!-- Step card (fills remaining height, content scrollable inside) -->
                    <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

                        <!-- Card header (animated) -->
                        <div class="flex shrink-0 items-center gap-3 border-b border-gray-100 bg-gray-50/80 px-5 py-3.5 dark:border-gray-800 dark:bg-gray-800/50">
                            <Transition mode="out-in"
                                enter-active-class="transition-all duration-200"
                                enter-from-class="opacity-0 scale-75"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition-all duration-150"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-75">
                                <div :key="step" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="current.iconBg">
                                    <component :is="current.icon" class="h-4 w-4" />
                                </div>
                            </Transition>
                            <Transition mode="out-in"
                                enter-active-class="transition-all duration-200"
                                enter-from-class="opacity-0 translate-x-2"
                                enter-to-class="opacity-100 translate-x-0"
                                leave-active-class="transition-all duration-150"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0 -translate-x-2">
                                <div :key="step">
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ current.label }}</p>
                                    <p class="text-xs text-gray-400">{{ current.subtitle }}</p>
                                </div>
                            </Transition>
                            <span class="ml-auto shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold" :class="current.badge">
                                {{ step }} / {{ TOTAL_STEPS }}
                            </span>
                        </div>

                        <!-- Card body (the only scrollable area) -->
                        <div class="flex-1 overflow-y-auto p-5">
                            <Transition mode="out-in"
                                enter-active-class="transition-all duration-300 ease-out"
                                enter-from-class="opacity-0 translate-x-4"
                                enter-to-class="opacity-100 translate-x-0"
                                leave-active-class="transition-all duration-200 ease-in"
                                leave-from-class="opacity-100 translate-x-0"
                                leave-to-class="opacity-0 -translate-x-4">
                                <div :key="step" class="space-y-4">

                                    <!-- ── Step 1: Personal ── -->
                                    <template v-if="step === 1">

                                        <!-- Google-connected banner -->
                                        <div v-if="isGoogleSignup" class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 dark:border-emerald-800/40 dark:bg-emerald-900/20">
                                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Signed in with Google</p>
                                                <p class="truncate text-xs text-emerald-600 dark:text-emerald-400">{{ googlePending?.email }}</p>
                                            </div>
                                        </div>

                                        <!-- Google sign-up button (only shown when NOT coming from Google) -->
                                        <template v-else>
                                            <a
                                                href="/auth/google/doctor"
                                                class="flex w-full items-center justify-center gap-3 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                            >
                                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                                </svg>
                                                Continue with Google
                                            </a>
                                            <div class="relative flex items-center gap-3">
                                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                                                <span class="text-xs font-medium text-gray-400 dark:text-gray-500">or fill in manually</span>
                                                <div class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></div>
                                            </div>
                                        </template>

                                        <div>
                                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                                            <div class="relative">
                                                <User class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="name" v-model="form.name" type="text" autofocus placeholder="John Doe"
                                                    :readonly="isGoogleSignup"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="[
                                                        { 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.name },
                                                        { 'cursor-not-allowed bg-gray-50 text-gray-500 dark:bg-gray-800/60 dark:text-gray-400': isGoogleSignup }
                                                    ]" />
                                            </div>
                                            <InputError :message="errors.name" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                                            <div class="relative">
                                                <Mail class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="email" v-model="form.email" type="email" placeholder="doctor@example.com"
                                                    :readonly="isGoogleSignup"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="[
                                                        { 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.email },
                                                        { 'cursor-not-allowed bg-gray-50 text-gray-500 dark:bg-gray-800/60 dark:text-gray-400': isGoogleSignup }
                                                    ]" />
                                            </div>
                                            <InputError :message="errors.email" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone number</label>
                                            <div class="relative">
                                                <Phone class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="phone" v-model="form.phone" type="tel" placeholder="0912 345 6789"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.phone }" />
                                            </div>
                                            <InputError :message="errors.phone" class="mt-1 text-xs" />
                                        </div>
                                    </template>

                                    <!-- ── Step 2: Credentials ── -->
                                    <template v-else-if="step === 2">
                                        <div>
                                            <div class="mb-1.5 flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Specialization</label>
                                                <span class="text-xs font-medium"
                                                    :class="form.specialization.length ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400'">
                                                    {{ form.specialization.length ? form.specialization.length + ' selected' : 'Select one or more' }}
                                                </span>
                                            </div>
                                            <div class="h-44 overflow-y-auto rounded-xl border bg-white p-3 dark:bg-gray-800"
                                                :class="errors.specialization ? 'border-red-400' : 'border-gray-200 dark:border-gray-700'">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <button v-for="spec in props.specializations" :key="spec"
                                                        type="button" @click="toggleSpec(spec)"
                                                        class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition-all"
                                                        :class="form.specialization.includes(spec)
                                                            ? 'border-orange-500 bg-orange-600 text-white shadow-sm'
                                                            : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 dark:border-gray-600 dark:text-gray-400 dark:hover:border-orange-500 dark:hover:text-orange-400'">
                                                        <CheckCircle2 v-if="form.specialization.includes(spec)" class="h-3 w-3" />
                                                        {{ spec }}
                                                    </button>
                                                </div>
                                            </div>
                                            <InputError :message="errors.specialization" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <label for="qualification" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Qualification</label>
                                            <div class="relative">
                                                <GraduationCap class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="qualification" v-model="form.qualification" type="text" placeholder="e.g., MBBS, MD, Board Certified"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.qualification }" />
                                            </div>
                                            <InputError :message="errors.qualification" class="mt-1 text-xs" />
                                        </div>

                                        <!-- ── ID Document Upload ── -->
                                        <div>
                                            <div class="mb-1.5 flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Government-issued ID
                                                    <span class="text-red-500">*</span>
                                                </label>
                                                <span class="text-xs text-gray-400">JPEG / PNG · max 5 MB each · up to 5 files</span>
                                            </div>

                                            <!-- Previews -->
                                            <div v-if="idPreviews.length" class="mb-3 grid grid-cols-3 gap-2">
                                                <div v-for="(src, i) in idPreviews" :key="i" class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800">
                                                    <img :src="src" class="h-full w-full object-cover" />
                                                    <button type="button" @click="removeIdFile(i)"
                                                        class="absolute right-1 top-1 flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white opacity-0 transition group-hover:opacity-100 hover:bg-red-600">
                                                        <X class="h-3 w-3" />
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Upload / camera area -->
                                            <div class="flex gap-2" :class="{ 'opacity-50 pointer-events-none': idFiles.length >= 5 }">
                                                <label class="flex flex-1 cursor-pointer flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed bg-gray-50 py-4 transition hover:border-orange-400 hover:bg-orange-50/50 dark:bg-gray-800 dark:hover:border-orange-500 dark:hover:bg-orange-950/20"
                                                    :class="errors.id_documents ? 'border-red-400' : 'border-gray-200 dark:border-gray-700'">
                                                    <Upload class="h-5 w-5 text-gray-400" />
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Upload file</span>
                                                    <input type="file" accept="image/jpeg,image/png" multiple class="hidden" @change="onIdFilePick" />
                                                </label>
                                                <button type="button" @click="openCamera"
                                                    class="flex flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 px-5 py-4 transition hover:border-orange-400 hover:bg-orange-50/50 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-orange-500 dark:hover:bg-orange-950/20">
                                                    <Camera class="h-5 w-5 text-gray-400" />
                                                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Camera</span>
                                                </button>
                                            </div>
                                            <p v-if="cameraError" class="mt-1 text-xs text-red-500">{{ cameraError }}</p>
                                            <InputError :message="errors.id_documents" class="mt-1 text-xs" />

                                            <!-- Camera modal -->
                                            <Transition
                                                enter-active-class="transition duration-200 ease-out"
                                                enter-from-class="opacity-0 scale-95"
                                                enter-to-class="opacity-100 scale-100"
                                                leave-active-class="transition duration-150 ease-in"
                                                leave-from-class="opacity-100 scale-100"
                                                leave-to-class="opacity-0 scale-95">
                                                <div v-if="showCamera" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="closeCamera">
                                                    <div class="w-full max-w-sm rounded-2xl bg-white shadow-xl dark:bg-gray-900">
                                                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Capture ID</p>
                                                            <button type="button" @click="closeCamera" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                                                                <X class="h-4 w-4" />
                                                            </button>
                                                        </div>
                                                        <div class="p-4">
                                                            <video :ref="(el) => { videoRef = el as HTMLVideoElement; onVideoMounted(el as HTMLVideoElement); }"
                                                                autoplay playsinline muted
                                                                class="w-full rounded-xl bg-black" />
                                                        </div>
                                                        <div class="border-t border-gray-100 px-4 pb-4 dark:border-gray-800">
                                                            <button type="button" @click="captureFromCamera"
                                                                class="w-full rounded-xl bg-orange-600 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700">
                                                                Capture Photo
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </Transition>
                                        </div>
                                    </template>

                                    <!-- ── Step 3: Practice ── -->
                                    <template v-else-if="step === 3">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="experience_years" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Years of Experience</label>
                                                <div class="relative">
                                                    <Briefcase class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                    <input id="experience_years" v-model.number="form.experience_years" type="number" min="0" max="70" step="1" placeholder="10"
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.experience_years }" />
                                                </div>
                                                <InputError :message="errors.experience_years" class="mt-1 text-xs" />
                                            </div>
                                            <div>
                                                <label for="consultation_fee" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee (₱)</label>
                                                <div class="relative">
                                                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-400">₱</span>
                                                    <input id="consultation_fee" v-model.number="form.consultation_fee" type="number" min="0" step="0.01" placeholder="50.00"
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.consultation_fee }" />
                                                </div>
                                                <InputError :message="errors.consultation_fee" class="mt-1 text-xs" />
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="location" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                                <div class="relative">
                                                    <MapPin class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                    <input id="location" v-model="form.location" type="text" placeholder="City, State"
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.location }" />
                                                </div>
                                                <InputError :message="errors.location" class="mt-1 text-xs" />
                                            </div>
                                            <div>
                                                <label for="languages" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Languages <span class="text-xs font-normal text-gray-400">comma-sep.</span>
                                                </label>
                                                <div class="relative">
                                                    <Languages class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                    <input id="languages" v-model="form.languages" type="text" placeholder="English, Spanish"
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.languages }" />
                                                </div>
                                                <InputError :message="errors.languages" class="mt-1 text-xs" />
                                            </div>
                                        </div>
                                        <div>
                                            <label for="bio" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Professional Bio</label>
                                            <div class="relative">
                                                <FileText class="pointer-events-none absolute left-3.5 top-3.5 h-4 w-4 text-gray-400" />
                                                <textarea id="bio" v-model="form.bio" rows="3" placeholder="Tell patients about your experience, approach, and specialties..."
                                                    class="w-full resize-none rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.bio }" />
                                            </div>
                                            <InputError :message="errors.bio" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <div class="mb-1.5 flex items-center justify-between">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Accepted Insurance
                                                    <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                                                </label>
                                                <span class="text-xs font-medium"
                                                    :class="form.insurance.length ? 'text-orange-600 dark:text-orange-400' : 'text-gray-400'">
                                                    {{ form.insurance.length ? form.insurance.length + ' selected' : 'None selected' }}
                                                </span>
                                            </div>
                                            <div class="h-32 overflow-y-auto rounded-xl border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <button v-for="ins in props.insurances" :key="ins"
                                                        type="button" @click="toggleInsurance(ins)"
                                                        class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition-all"
                                                        :class="form.insurance.includes(ins)
                                                            ? 'border-orange-500 bg-orange-600 text-white shadow-sm'
                                                            : 'border-gray-200 text-gray-600 hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600 dark:border-gray-600 dark:text-gray-400 dark:hover:border-orange-500 dark:hover:text-orange-400'">
                                                        <CheckCircle2 v-if="form.insurance.includes(ins)" class="h-3 w-3" />
                                                        {{ ins }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- ── Step 4: Security ── -->
                                    <template v-else-if="step === 4">
                                        <!-- Global error -->
                                        <div v-if="errors.form" class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-800/40 dark:bg-red-900/20">
                                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="text-sm font-medium text-red-700 dark:text-red-400">{{ errors.form }}</p>
                                        </div>

                                        <!-- Google sign-up: no password needed -->
                                        <template v-if="isGoogleSignup">
                                            <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 dark:border-emerald-800/40 dark:bg-emerald-900/20">
                                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
                                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                                </svg>
                                                <div>
                                                    <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Google account connected</p>
                                                    <p class="text-xs text-emerald-600 dark:text-emerald-400">{{ googlePending?.email }} · No password required</p>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- Standard sign-up: password fields -->
                                        <template v-else>
                                            <div>
                                                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                                <PasswordInput id="password" v-model="form.password" autocomplete="new-password" placeholder="••••••••"
                                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.password }" />
                                                <InputError :message="errors.password" class="mt-1 text-xs" />
                                            </div>
                                            <div>
                                                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                                <PasswordInput id="password_confirmation" v-model="form.password_confirmation" autocomplete="new-password" placeholder="••••••••"
                                                    class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.password_confirmation }" />
                                                <InputError :message="errors.password_confirmation" class="mt-1 text-xs" />
                                            </div>
                                        </template>

                                        <!-- Profile summary -->
                                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/60">
                                            <p class="mb-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Profile Summary</p>
                                            <div class="space-y-1.5 text-sm text-gray-700 dark:text-gray-300">
                                                <div class="flex items-center gap-2">
                                                    <User class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                                    <span class="truncate">{{ form.name || '—' }}</span>
                                                </div>
                                                <div class="flex items-start gap-2">
                                                    <Stethoscope class="mt-0.5 h-3.5 w-3.5 shrink-0 text-gray-400" />
                                                    <span class="line-clamp-1">{{ form.specialization.length ? form.specialization.join(', ') : '—' }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <MapPin class="h-3.5 w-3.5 shrink-0 text-gray-400" />
                                                    <span>{{ form.location || '—' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- hCaptcha (production only, invisible — registration) -->
                                        <div v-if="IS_PRODUCTION && HCAPTCHA_SITEKEY" class="mt-4">
                                            <VueHcaptcha
                                                ref="regCaptchaRef"
                                                :sitekey="HCAPTCHA_SITEKEY"
                                                size="invisible"
                                                @verify="onRegCaptchaVerified"
                                                @expired="onRegCaptchaExpired"
                                                @error="onRegCaptchaExpired"
                                            />
                                            <p v-if="errors.hcaptcha_token" class="mt-1 text-xs text-red-500">{{ errors.hcaptcha_token }}</p>
                                        </div>

                                        <!-- Legal note -->
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            By registering, you agree to our
                                            <a href="/terms-of-service" target="_blank" rel="noopener noreferrer" class="font-medium text-gray-500 underline underline-offset-2 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400">Terms of Service</a>
                                            and
                                            <a href="/privacy-policy" target="_blank" rel="noopener noreferrer" class="font-medium text-gray-500 underline underline-offset-2 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400">Privacy Policy</a>.
                                            Your profile will be reviewed before activation.
                                        </p>
                                    </template>

                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Navigation buttons -->
                    <div class="mt-4 flex shrink-0 items-center justify-between gap-3">
                        <button v-if="step > 1" type="button" @click="prevStep"
                            class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <ChevronLeft class="h-4 w-4" />
                            Back
                        </button>
                        <div v-else></div>

                        <button v-if="step < TOTAL_STEPS" type="button" @click="nextStep"
                            class="flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-200 transition hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 dark:shadow-none dark:focus:ring-offset-gray-950">
                            Continue
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <button v-else type="button" @click="handleSubmit" :disabled="processing"
                            class="flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-200 transition hover:bg-orange-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 dark:shadow-none dark:focus:ring-offset-gray-950">
                            <Spinner v-if="processing" class="h-4 w-4" />
                            {{ processing ? 'Creating profile…' : 'Complete Registration' }}
                        </button>
                    </div>

                    <!-- Footer -->
                    <p class="mt-3 shrink-0 text-center text-sm text-gray-500 dark:text-gray-400">
                        Already have an account?
                        <Link href="/login" class="font-semibold text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">Sign in</Link>
                    </p>

                </div>
            </div>
        </div>
    </div>
</template>
