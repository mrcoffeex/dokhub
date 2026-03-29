<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Spinner } from '@/components/ui/spinner';
import {
    User, Mail, Phone, Stethoscope, GraduationCap, Briefcase,
    MapPin, FileText, Languages, Lock, ChevronLeft, CheckCircle2,
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps<{
    stats: { doctors: number; patients: number; rating: number };
}>();

function formatCount(n: number): string {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(n % 1_000_000 === 0 ? 0 : 1) + 'M+';
    if (n >= 1_000) return (n / 1_000).toFixed(n % 1_000 === 0 ? 0 : 1) + 'K+';
    return String(n);
}

const step = ref(1);
const TOTAL_STEPS = 4;

const form = ref({
    name: '',
    email: '',
    phone: '',
    specialization: [] as string[],
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

const STEPS = [
    {
        label: 'Personal',
        subtitle: 'Your name and contact details',
        heading: "Let's start with the basics.",
        sub: 'Tell us who you are so patients and DokHub can reach you.',
        icon: User,
        iconBg: 'bg-violet-100 text-violet-600 dark:bg-violet-900/40 dark:text-violet-400',
        badge: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
        bullets: [
            'Your name appears on your public doctor profile',
            'Contact details help us notify you about bookings',
            'All personal data is encrypted and private',
        ],
    },
    {
        label: 'Credentials',
        subtitle: 'Specialization & qualifications',
        heading: 'What are your specialties?',
        sub: 'Your credentials help patients find the right doctor for their needs.',
        icon: Stethoscope,
        iconBg: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-400',
        badge: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        bullets: [
            'Profiles with verified specializations rank higher',
            'Patients search by specialty to find you',
            'You can update credentials anytime from settings',
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

const current = computed(() => STEPS[step.value - 1]);
const progress = computed(() => Math.round(((step.value - 1) / (TOTAL_STEPS - 1)) * 100));

const specializations = [
    'Allergy & Immunology', 'Anesthesiology', 'Cardiology', 'Colorectal Surgery',
    'Critical Care Medicine', 'Dermatology', 'Emergency Medicine', 'Endocrinology',
    'Family Medicine', 'Gastroenterology', 'General Surgery', 'Geriatrics',
    'Hematology', 'Infectious Disease', 'Internal Medicine', 'Nephrology',
    'Neurology', 'Neurosurgery', 'Obstetrics & Gynecology', 'Oncology',
    'Ophthalmology', 'Orthopedic Surgery', 'Otolaryngology (ENT)', 'Pathology',
    'Pediatrics', 'Physical Medicine & Rehabilitation', 'Plastic Surgery',
    'Psychiatry', 'Pulmonology', 'Radiology', 'Rheumatology', 'Thoracic Surgery',
    'Urology', 'Vascular Surgery',
];

function toggleSpec(spec: string) {
    if (form.value.specialization.includes(spec)) {
        form.value.specialization = form.value.specialization.filter(x => x !== spec);
    } else {
        form.value.specialization.push(spec);
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
    } else if (step.value === 3) {
        if (form.value.experience_years === '') errors.value.experience_years = 'Required';
        if (form.value.consultation_fee === '') errors.value.consultation_fee = 'Required';
        if (!form.value.location.trim()) errors.value.location = 'Location is required';
        if (!form.value.languages.trim()) errors.value.languages = 'At least one language is required';
        if (!form.value.bio.trim()) errors.value.bio = 'Professional bio is required';
    } else if (step.value === 4) {
        if (!form.value.password) errors.value.password = 'Password is required';
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

async function handleSubmit() {
    if (!validateStep()) return;
    processing.value = true;
    errors.value = {};
    try {
        const headers: Record<string, string> = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            headers['X-CSRF-Token'] = csrfToken;
        }
        const res = await fetch('/api/doctor-register', {
            method: 'POST',
            headers,
            body: JSON.stringify(form.value),
        });
        const data = await res.json();
        if (!res.ok) {
            errors.value = data.errors || { form: 'Registration failed' };
            processing.value = false;
            return;
        }
        window.location.href = '/auth/doctor-signup-success';
    } catch (err: any) {
        errors.value.form = err.message || 'An error occurred';
        processing.value = false;
    }
}
</script>

<template>
    <Head title="Doctor Registration — DokHub" />

    <div class="flex h-screen overflow-hidden">

        <!-- ── Left branding panel ─────────────────────────────────── -->
        <div class="relative hidden flex-col overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-indigo-700 lg:flex lg:w-5/12 xl:w-2/5">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
                <div class="absolute bottom-10 -right-16 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <div class="relative flex h-full flex-col justify-between p-10">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm ring-1 ring-white/30">
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-white">DokHub</p>
                        <p class="text-xs text-violet-200">Healthcare Platform</p>
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
                                    <CheckCircle2 v-if="i + 1 < step" class="h-5 w-5 text-violet-600" />
                                    <component v-else :is="s.icon" class="h-4 w-4 transition-colors"
                                        :class="i + 1 === step ? 'text-white' : 'text-white/30'" />
                                </div>
                                <span class="mt-1.5 text-[10px] font-semibold uppercase tracking-wide transition-colors"
                                    :class="i + 1 === step ? 'text-white' : i + 1 < step ? 'text-violet-200' : 'text-white/30'">
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
                            <p class="mt-3 text-sm leading-relaxed text-violet-200">{{ current.sub }}</p>
                            <ul class="mt-6 space-y-2.5">
                                <li v-for="bullet in current.bullets" :key="bullet"
                                    class="flex items-start gap-2.5 text-sm text-violet-100">
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
                            <p class="text-xs text-violet-300">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Back link -->
                <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-violet-200 transition-colors hover:text-white">
                    <ChevronLeft class="h-4 w-4" />
                    Back to home
                </Link>
            </div>
        </div>

        <!-- ── Right form panel ───────────────────────────────────── -->
        <div class="flex flex-1 flex-col overflow-hidden bg-gray-50 dark:bg-gray-950">

            <!-- Mobile top bar -->
            <div class="flex shrink-0 items-center justify-between border-b border-gray-200 bg-white px-5 py-3 dark:border-gray-800 dark:bg-gray-900 lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-600">
                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">DokHub</span>
                </div>
                <Link href="/" class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400">← Home</Link>
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
                                <div class="h-full rounded-full bg-violet-600 transition-all duration-500"
                                    :style="{ width: progress + '%' }"></div>
                            </div>
                            <span class="text-xs font-semibold text-violet-600 dark:text-violet-400">{{ progress }}%</span>
                        </div>
                    </div>

                    <!-- Mobile step bar -->
                    <div class="mb-4 flex shrink-0 gap-1 lg:hidden">
                        <div v-for="(s, i) in STEPS" :key="i"
                            class="flex-1 rounded-full py-0.5 transition-all duration-300"
                            :class="i + 1 <= step ? 'bg-violet-500' : 'bg-gray-200 dark:bg-gray-700'">
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
                                        <div>
                                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                                            <div class="relative">
                                                <User class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="name" v-model="form.name" type="text" autofocus placeholder="John Doe"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.name }" />
                                            </div>
                                            <InputError :message="errors.name" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                                            <div class="relative">
                                                <Mail class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="email" v-model="form.email" type="email" placeholder="doctor@example.com"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.email }" />
                                            </div>
                                            <InputError :message="errors.email" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <label for="phone" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone number</label>
                                            <div class="relative">
                                                <Phone class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                <input id="phone" v-model="form.phone" type="tel" placeholder="0912 345 6789"
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
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
                                                    :class="form.specialization.length ? 'text-violet-600 dark:text-violet-400' : 'text-gray-400'">
                                                    {{ form.specialization.length ? form.specialization.length + ' selected' : 'Select one or more' }}
                                                </span>
                                            </div>
                                            <div class="h-44 overflow-y-auto rounded-xl border bg-white p-3 dark:bg-gray-800"
                                                :class="errors.specialization ? 'border-red-400' : 'border-gray-200 dark:border-gray-700'">
                                                <div class="flex flex-wrap gap-1.5">
                                                    <button v-for="spec in specializations" :key="spec"
                                                        type="button" @click="toggleSpec(spec)"
                                                        class="inline-flex items-center gap-1 rounded-lg border px-2.5 py-1 text-xs font-medium transition-all"
                                                        :class="form.specialization.includes(spec)
                                                            ? 'border-violet-500 bg-violet-600 text-white shadow-sm'
                                                            : 'border-gray-200 text-gray-600 hover:border-violet-300 hover:bg-violet-50 hover:text-violet-600 dark:border-gray-600 dark:text-gray-400 dark:hover:border-violet-500 dark:hover:text-violet-400'">
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
                                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.qualification }" />
                                            </div>
                                            <InputError :message="errors.qualification" class="mt-1 text-xs" />
                                        </div>
                                    </template>

                                    <!-- ── Step 3: Practice ── -->
                                    <template v-else-if="step === 3">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="experience_years" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Years of Experience</label>
                                                <div class="relative">
                                                    <Briefcase class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                                    <input id="experience_years" v-model.number="form.experience_years" type="number" min="0" max="70" placeholder="10"
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                        :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.experience_years }" />
                                                </div>
                                                <InputError :message="errors.experience_years" class="mt-1 text-xs" />
                                            </div>
                                            <div>
                                                <label for="consultation_fee" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Consultation Fee (₱)</label>
                                                <div class="relative">
                                                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-medium text-gray-400">₱</span>
                                                    <input id="consultation_fee" v-model.number="form.consultation_fee" type="number" min="0" step="0.01" placeholder="50.00"
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
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
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
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
                                                        class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
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
                                                    class="w-full resize-none rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                    :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.bio }" />
                                            </div>
                                            <InputError :message="errors.bio" class="mt-1 text-xs" />
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

                                        <div>
                                            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                            <PasswordInput id="password" v-model="form.password" autocomplete="new-password" placeholder="••••••••"
                                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.password }" />
                                            <InputError :message="errors.password" class="mt-1 text-xs" />
                                        </div>
                                        <div>
                                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                            <PasswordInput id="password_confirmation" v-model="form.password_confirmation" autocomplete="new-password" placeholder="••••••••"
                                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                                                :class="{ 'border-red-400 focus:border-red-400 focus:ring-red-100': errors.password_confirmation }" />
                                            <InputError :message="errors.password_confirmation" class="mt-1 text-xs" />
                                        </div>

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

                                        <!-- Legal note -->
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            By registering, you agree to our
                                            <a href="/terms-of-service" target="_blank" rel="noopener noreferrer" class="font-medium text-gray-500 underline underline-offset-2 hover:text-violet-600 dark:text-gray-400 dark:hover:text-violet-400">Terms of Service</a>
                                            and
                                            <a href="/privacy-policy" target="_blank" rel="noopener noreferrer" class="font-medium text-gray-500 underline underline-offset-2 hover:text-violet-600 dark:text-gray-400 dark:hover:text-violet-400">Privacy Policy</a>.
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
                            class="flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-violet-200 transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:shadow-none dark:focus:ring-offset-gray-950">
                            Continue
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <button v-else type="button" @click="handleSubmit" :disabled="processing"
                            class="flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-violet-200 transition hover:bg-violet-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:shadow-none dark:focus:ring-offset-gray-950">
                            <Spinner v-if="processing" class="h-4 w-4" />
                            {{ processing ? 'Creating profile…' : 'Complete Registration' }}
                        </button>
                    </div>

                    <!-- Footer -->
                    <p class="mt-3 shrink-0 text-center text-sm text-gray-500 dark:text-gray-400">
                        Already have an account?
                        <Link href="/login" class="font-semibold text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300">Sign in</Link>
                    </p>

                </div>
            </div>
        </div>
    </div>
</template>
