<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';

const props = defineProps<{
    featuredSpecializations: string[];
    stats: {
        doctors: number;
        appointments: number;
        specializations: number;
        avg_rating: string | null;
    };
}>();

function formatCount(n: number): string {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(n % 1_000_000 === 0 ? 0 : 1) + 'M+';
    if (n >= 1_000) return (n / 1_000).toFixed(n % 1_000 === 0 ? 0 : 1) + 'k+';
    return n + (n > 0 ? '+' : '');
}

const EMOJI_MAP: Record<string, string> = {
    'Cardiology': '❤️',
    'Neurology': '🧠',
    'Dermatology': '✨',
    'Orthopedic Surgery': '🦴',
    'Pediatrics': '👶',
    'General Surgery': '🩺',
    'Obstetrics & Gynecology': '🌸',
    'Psychiatry': '🧘',
    'Oncology': '🎗️',
    'Ophthalmology': '👁️',
    'Pulmonology': '🫁',
    'Endocrinology': '⚗️',
    'Gastroenterology': '💊',
    'Nephrology': '🫘',
    'Rheumatology': '🦴',
    'Infectious Disease': '🦠',
};

function emoji(name: string) {
    return EMOJI_MAP[name] ?? '🏥';
}

const statCards = computed(() => [
    { value: formatCount(props.stats.doctors), label: 'Verified Doctors' },
    { value: formatCount(props.stats.appointments), label: 'Appointments Booked' },
    { value: props.stats.specializations + '+', label: 'Specializations' },
    { value: props.stats.avg_rating ? props.stats.avg_rating + '★' : '—', label: 'Average Rating' },
]);

const steps = [
    {
        num: '1',
        title: 'Browse Doctors',
        description: 'Filter by specialization, location, or availability to find the right doctor for your needs.',
        bg: 'bg-orange-100',
        color: 'text-orange-600',
    },
    {
        num: '2',
        title: 'Pick a Time Slot',
        description: "Choose from the doctor's available time slots to fit your schedule perfectly.",
        bg: 'bg-sky-100',
        color: 'text-sky-600',
    },
    {
        num: '3',
        title: 'Get Confirmation',
        description: 'Fill in your details and receive an instant email confirmation with your appointment reference.',
        bg: 'bg-green-100',
        color: 'text-green-600',
    },
];
</script>

<template>
    <Head title="DokHub — Healthcare Made Simple" />
    <GuestLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-white">
            <!-- Base gradient -->
            <div class="absolute inset-0 bg-gradient-to-br from-orange-50 via-white to-sky-50" />

            <!-- Dot grid -->
            <div
                class="absolute inset-0 opacity-50"
                style="background-image: radial-gradient(circle, #ea580c22 1px, transparent 1px); background-size: 28px 28px;"
            />

            <!-- Morphing blobs -->
            <div class="blob-orange absolute -left-56 -top-56 h-[700px] w-[700px] rounded-full bg-orange-200/50 blur-3xl will-change-transform" />
            <div class="blob-sky absolute -right-56 -top-20 h-[600px] w-[600px] rounded-full bg-sky-200/40 blur-3xl will-change-transform" />
            <div class="blob-yellow absolute -bottom-40 left-1/3 h-[500px] w-[500px] rounded-full bg-yellow-200/35 blur-3xl will-change-transform" />

            <!-- Decorative spinning rings -->
            <div class="ring-cw absolute -left-20 top-1/3 h-52 w-52 rounded-full border border-dashed border-orange-300/50 will-change-transform" />
            <div class="ring-ccw absolute -right-12 bottom-1/4 h-80 w-80 rounded-full border border-dashed border-sky-300/40 will-change-transform" />
            <div class="ring-cw absolute right-1/4 top-8 h-32 w-32 rounded-full border border-orange-200/40 will-change-transform" style="animation-duration: 30s;" />

            <!-- Floating particles -->
            <div class="particle absolute left-[14%] top-[22%] h-3 w-3 rounded-full bg-orange-400/60 will-change-transform" style="animation-delay: 0s;" />
            <div class="particle absolute right-[18%] top-[14%] h-2 w-2 rounded-full bg-sky-400/55 will-change-transform" style="animation-delay: 1.1s;" />
            <div class="particle absolute left-[78%] top-[38%] h-4 w-4 rounded-full bg-yellow-400/45 will-change-transform" style="animation-delay: 2.3s;" />
            <div class="particle absolute left-[8%] bottom-[28%] h-2 w-2 rounded-full bg-orange-300/65 will-change-transform" style="animation-delay: 1.7s;" />
            <div class="particle absolute right-[12%] bottom-[18%] h-3 w-3 rounded-full bg-sky-300/50 will-change-transform" style="animation-delay: 0.6s;" />
            <div class="particle absolute left-[48%] top-[8%] h-2 w-2 rounded-full bg-orange-500/45 will-change-transform" style="animation-delay: 3.1s;" />
            <div class="particle absolute left-[32%] bottom-[12%] h-2 w-2 rounded-full bg-yellow-500/40 will-change-transform" style="animation-delay: 2s;" />
            <div class="particle absolute right-[35%] top-[30%] h-1.5 w-1.5 rounded-full bg-orange-400/50 will-change-transform" style="animation-delay: 0.9s;" />

            <!-- Shimmer top edge -->
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-orange-300/60 to-transparent" />

            <div class="relative mx-auto max-w-7xl px-4 pb-24 pt-20 sm:px-6 lg:px-8 lg:pb-32 lg:pt-28">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-orange-200 bg-orange-50 px-4 py-1.5 text-sm font-medium text-orange-700">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75" />
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-orange-500" />
                        </span>
                        Healthcare appointments made effortless
                    </div>
                    <h1 class="text-5xl font-bold tracking-tight text-gray-900 sm:text-6xl lg:text-7xl">
                        See the right doctor,<br />
                        <span class="bg-gradient-to-r from-orange-600 to-sky-500 bg-clip-text text-transparent">right now.</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 sm:text-xl">
                        Book a specialist appointment in under a minute. No account required, no waiting on hold — just instant access to top healthcare professionals.
                    </p>
                    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <Link href="/doctors" class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-orange-200 transition-all hover:bg-orange-700 hover:shadow-orange-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                            Find a Doctor
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </Link>
                        <Link href="/my-appointment" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-8 py-3.5 text-base font-semibold text-gray-700 shadow-sm transition-all hover:border-gray-300 hover:bg-gray-50">
                            Track Appointment
                        </Link>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="mt-20 grid grid-cols-2 gap-6 sm:grid-cols-4">
                    <div v-for="stat in statCards" :key="stat.label" class="rounded-2xl border border-gray-100 bg-white px-6 py-5 text-center shadow-sm">
                        <p class="text-3xl font-bold text-gray-900">{{ stat.value }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section class="bg-gray-50 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Book in 3 simple steps</h2>
                    <p class="mt-4 text-lg text-gray-600">No login, no hassle. Just fast, reliable healthcare access.</p>
                </div>
                <div class="mt-16 grid gap-8 sm:grid-cols-3">
                    <div v-for="step in steps" :key="step.title" class="relative rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl" :class="step.bg">
                            <svg class="h-6 w-6" :class="step.color" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path v-if="step.num === '1'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                <path v-else-if="step.num === '2'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="absolute right-6 top-6 flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-500">
                            {{ step.num }}
                        </div>
                        <h3 class="mt-6 text-lg font-semibold text-gray-900">{{ step.title }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Specializations -->
        <section class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Browse by Specialization</h2>
                    <p class="mt-4 text-lg text-gray-600">Expert care across every medical field.</p>
                </div>
                <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-4">
                    <Link
                        v-for="spec in featuredSpecializations"
                        :key="spec"
                        :href="`/doctors?specialization=${encodeURIComponent(spec)}`"
                        class="group flex flex-col items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-6 text-center transition-all hover:border-orange-200 hover:bg-orange-50 hover:shadow-md"
                    >
                        <span class="text-3xl">{{ emoji(spec) }}</span>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-orange-700">{{ spec }}</span>
                    </Link>
                </div>
                <div class="mt-10 text-center">
                    <Link href="/doctors" class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600 hover:text-orange-700">
                        View all doctors
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative overflow-hidden bg-orange-600 py-24">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-yellow-400 via-orange-500 to-orange-700" />
            <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to book your appointment?</h2>
                <p class="mt-4 text-lg text-orange-200">Join thousands of patients who trust DokHub for their healthcare needs.</p>
                <div class="mt-10">
                    <Link href="/doctors" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-orange-700 shadow-lg transition-all hover:bg-orange-50">
                        Find a Doctor Now
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
/* ── Blob morph ─────────────────────────────────────────── */
@keyframes blob-morph {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    }
    25% {
        transform: translate(28px, -36px) scale(1.06);
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
    }
    50% {
        transform: translate(-18px, 22px) scale(0.94);
        border-radius: 50% 60% 30% 60% / 30% 60% 70% 40%;
    }
    75% {
        transform: translate(12px, 38px) scale(1.03);
        border-radius: 60% 40% 60% 30% / 70% 30% 60% 40%;
    }
}

.blob-orange {
    animation: blob-morph 12s ease-in-out infinite;
}
.blob-sky {
    animation: blob-morph 15s ease-in-out infinite;
    animation-delay: 2.5s;
}
.blob-yellow {
    animation: blob-morph 10s ease-in-out infinite;
    animation-delay: 5s;
}

/* ── Floating particles ─────────────────────────────────── */
@keyframes float {
    0%,
    100% {
        transform: translateY(0) scale(1);
        opacity: 0.55;
    }
    50% {
        transform: translateY(-18px) scale(1.15);
        opacity: 1;
    }
}

.particle {
    animation: float 4.5s ease-in-out infinite;
}

/* ── Spinning rings ─────────────────────────────────────── */
@keyframes spin-cw {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
@keyframes spin-ccw {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(-360deg);
    }
}

.ring-cw {
    animation: spin-cw 22s linear infinite;
}
.ring-ccw {
    animation: spin-ccw 28s linear infinite;
}
</style>
