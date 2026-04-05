<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';

interface TopReview {
    id: number;
    patient_name: string;
    rating: number;
    comment: string;
    created_at: string;
    doctor_name: string | null;
    doctor_slug: string | null;
    doctor_avatar: string | null;
    doctor_spec: string | null;
}

const props = defineProps<{
    featuredSpecializations: string[];
    stats: {
        doctors: number;
        appointments: number;
        specializations: number;
        avg_rating: string | null;
    };
    topReviews: TopReview[];
}>();

function formatCount(n: number): string {
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(n % 1_000_000 === 0 ? 0 : 1) + 'M+';
    if (n >= 1_000) return (n / 1_000).toFixed(n % 1_000 === 0 ? 0 : 1) + 'k+';
    return n + (n > 0 ? '+' : '');
}

const EMOJI_MAP: Record<string, string> = {
    Cardiology: '❤️',
    Neurology: '🧠',
    Dermatology: '✨',
    'Orthopedic Surgery': '🦴',
    Pediatrics: '👶',
    'General Surgery': '🩺',
    'Obstetrics & Gynecology': '🌸',
    Psychiatry: '🧘',
    Oncology: '🎗️',
    Ophthalmology: '👁️',
    Pulmonology: '🫁',
    Endocrinology: '⚗️',
    Gastroenterology: '💊',
    Nephrology: '🫘',
    Rheumatology: '🦴',
    'Infectious Disease': '🦠',
};

function emoji(name: string) {
    return EMOJI_MAP[name] ?? '🏥';
}

const statCards = computed(() => [
    { value: formatCount(props.stats.doctors), label: 'Verified Doctors' },
    { value: formatCount(props.stats.appointments), label: 'Appointments Booked' },
    { value: props.stats.specializations + '+', label: 'Specializations' },
    { value: props.stats.avg_rating ? props.stats.avg_rating + '★' : '—', label: 'Avg. Rating' },
]);

const steps = [
    {
        num: '1',
        title: 'Browse Doctors',
        description: 'Filter by specialization, location, or availability to find the right doctor.',
        iconPath: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        accent: 'orange',
    },
    {
        num: '2',
        title: 'Pick a Time Slot',
        description: "Choose from the doctor's available slots that fit your schedule.",
        iconPath: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        accent: 'sky',
    },
    {
        num: '3',
        title: 'Get Confirmation',
        description: 'Receive instant email confirmation with your appointment reference.',
        iconPath: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        accent: 'green',
    },
];

// Scroll-reveal
onMounted(() => {
    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    (e.target as HTMLElement).setAttribute('data-visible', 'true');
                    io.unobserve(e.target);
                }
            });
        },
        { threshold: 0.12 },
    );
    document.querySelectorAll('[data-reveal]').forEach((el) => io.observe(el));
});

// Interactive mock booking card
const selectedSlot = ref(0);
const mockSlots = ['9:00 AM', '11:00 AM', '2:00 PM'];

// Review helpers
function initials(name: string): string {
    return name
        .split(' ')
        .slice(0, 2)
        .map((w) => w[0]?.toUpperCase() ?? '')
        .join('');
}

const AVATAR_COLORS = [
    'from-orange-500 to-orange-400',
    'from-sky-500 to-sky-400',
    'from-violet-500 to-violet-400',
    'from-emerald-500 to-emerald-400',
    'from-rose-500 to-rose-400',
    'from-amber-500 to-amber-400',
];

function avatarGradient(id: number): string {
    return AVATAR_COLORS[id % AVATAR_COLORS.length];
}
</script>

<template>
    <Head title="DokHub — Healthcare Made Simple" />
    <GuestLayout>

        <!-- ═══════════════════════════════════════════════
             HERO — Asymmetric, product-first layout
             ═══════════════════════════════════════════════ -->
        <section class="relative bg-white dark:bg-gray-950">
            <!-- Background (own overflow-hidden so floating badges aren't clipped) -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <!-- Left warm wash -->
                <div class="absolute inset-y-0 left-0 w-3/5 bg-gradient-to-r from-orange-50/80 via-orange-50/20 to-transparent dark:from-orange-950/20 dark:via-transparent" />
                <!-- Right-side grid texture -->
                <div
                    class="absolute inset-y-0 right-0 w-1/2 opacity-[0.04] dark:opacity-[0.07]"
                    style="background-image: linear-gradient(#94a3b8 1px, transparent 1px), linear-gradient(90deg, #94a3b8 1px, transparent 1px); background-size: 52px 52px;"
                />
                <!-- Single accent blob — not three -->
                <div class="blob-drift absolute -right-72 -top-72 h-[800px] w-[800px] rounded-full bg-orange-200/25 blur-3xl dark:bg-orange-900/15" />
                <!-- Bottom edge rule -->
                <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gray-200/70 to-transparent dark:via-gray-700/50" />
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Two-column split -->
                <div class="flex min-h-[calc(100svh-4rem)] flex-col items-center gap-10 py-20 lg:flex-row lg:items-center lg:gap-20 lg:py-28">

                    <!-- ── Left: Editorial content ── -->
                    <div class="flex-1 space-y-7 text-center lg:text-left">

                        <!-- Trust badge -->
                        <div class="hero-in inline-flex items-center gap-2 rounded-full border border-orange-200/70 bg-white/70 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.1em] text-orange-600 shadow-sm backdrop-blur dark:border-orange-800/40 dark:bg-gray-900/50 dark:text-orange-400">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-orange-400 opacity-75" />
                                <span class="relative h-1.5 w-1.5 rounded-full bg-orange-500" />
                            </span>
                            Now accepting patients
                        </div>

                        <!-- Headline with animated SVG underline -->
                        <h1 class="hero-in hero-d1 text-[clamp(2.75rem,6vw,4.5rem)] font-extrabold leading-[1.07] tracking-tight text-gray-950 dark:text-white">
                            <span class="block">Find the right</span>
                            <span class="relative me-2 inline-block">
                                <span class="bg-gradient-to-br from-orange-600 to-orange-400 bg-clip-text text-transparent">doctor</span>
                                <svg class="absolute -bottom-1.5 left-0 w-full overflow-visible" height="10" viewBox="0 0 200 10" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M3 7 C40 2, 80 9, 120 5 C155 2, 180 8, 197 5" fill="none" stroke="url(#ugrad)" stroke-width="3.5" stroke-linecap="round" class="underline-draw" />
                                    <defs>
                                        <linearGradient id="ugrad" x1="0" y1="0" x2="1" y2="0">
                                            <stop offset="0%" stop-color="#ea580c" />
                                            <stop offset="100%" stop-color="#fb923c" />
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </span>
                            <span class="block">book in minutes.</span>
                        </h1>

                        <!-- Subheading -->
                        <p class="hero-in hero-d2 mx-auto max-w-md text-[1.075rem] leading-relaxed text-gray-500 dark:text-gray-400 lg:mx-0">
                            One platform. Every specialist. No waiting on hold, no account required — just instant, trusted access to top healthcare professionals.
                        </p>

                        <!-- CTAs -->
                        <div class="hero-in hero-d3 flex flex-col items-center gap-3 sm:flex-row lg:justify-start justify-center">
                            <Link
                                href="/doctors"
                                class="group inline-flex items-center gap-2 rounded-xl bg-orange-600 px-7 py-3.5 text-sm font-semibold text-white shadow-xl shadow-orange-200/70 transition-all duration-200 hover:-translate-y-px hover:bg-orange-700 hover:shadow-orange-300/70 dark:shadow-orange-900/30"
                            >
                                Find a Doctor
                                <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </Link>
                            <Link
                                href="/my-appointment"
                                class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white/80 px-7 py-3.5 text-sm font-semibold text-gray-700 backdrop-blur transition-all duration-200 hover:-translate-y-px hover:border-gray-300 hover:bg-white dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-300 dark:hover:bg-gray-900"
                            >
                                Track Appointment
                            </Link>
                        </div>

                        <!-- Micro trust pills -->
                        <div class="hero-in hero-d4 flex flex-wrap items-center gap-3 lg:justify-start justify-center">
                            <span v-for="t in ['No account needed', 'Instant confirmation', 'Free to use']" :key="t" class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-600">
                                <svg class="h-3.5 w-3.5 text-green-500 dark:text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                {{ t }}
                            </span>
                        </div>
                    </div>

                    <!-- ── Right: Interactive booking card mockup ── -->
                    <div class="hero-card-in relative w-full flex-shrink-0 self-center sm:max-w-[360px]">
                        <!-- Primary card -->
                        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-2xl shadow-gray-300/30 dark:border-gray-800 dark:bg-gray-900 dark:shadow-gray-950/80">
                            <!-- Back nav -->
                            <div class="mb-4 flex items-center gap-1 text-xs text-gray-400 dark:text-gray-600">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                                Search results
                            </div>

                            <!-- Doctor profile -->
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 text-sm font-bold text-white shadow-md shadow-orange-200/60 dark:shadow-orange-900/40">
                                    SC
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Dr. Sarah Corpuz</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Cardiologist · PRC ✓</p>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400">Available</span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-1">
                                        <div class="flex">
                                            <svg v-for="i in 5" :key="i" class="h-3 w-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </div>
                                        <span class="text-[11px] font-medium text-gray-700 dark:text-gray-300">4.9</span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-600">· 142 reviews</span>
                                    </div>
                                </div>
                            </div>

                            <div class="my-4 border-t border-gray-100 dark:border-gray-800" />

                            <!-- Slot picker -->
                            <p class="mb-2 text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-600">Available Tomorrow</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button
                                    v-for="(slot, i) in mockSlots"
                                    :key="slot"
                                    type="button"
                                    class="rounded-lg border py-2 text-xs font-medium transition-all duration-150"
                                    :class="selectedSlot === i
                                        ? 'border-orange-500 bg-orange-50 text-orange-700 dark:border-orange-500 dark:bg-orange-950/50 dark:text-orange-400'
                                        : 'border-gray-100 bg-gray-50 text-gray-600 hover:border-gray-200 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-400 dark:hover:border-gray-700'"
                                    @click="selectedSlot = i"
                                >
                                    {{ slot }}
                                </button>
                            </div>

                            <!-- Book CTA -->
                            <button type="button" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-orange-600 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-200/50 transition-all duration-200 hover:bg-orange-700 active:scale-[0.98] dark:shadow-orange-900/30">
                                Book Appointment
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                        </div>

                        <!-- Floating: confirmation toast -->
                        <div class="float-badge absolute -bottom-4 -right-3 z-10 flex min-w-[190px] items-center gap-2.5 rounded-xl border border-emerald-200/70 bg-white px-3.5 py-2.5 shadow-xl shadow-emerald-100/40 dark:border-emerald-900/40 dark:bg-gray-900 dark:shadow-emerald-950/30">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-gray-900 dark:text-white">Confirmed!</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Tomorrow · 9:00 AM</p>
                            </div>
                        </div>

                        <!-- Floating: rating badge -->
                        <div class="float-badge absolute -left-4 top-10 z-10 flex items-center gap-2 rounded-xl border border-amber-200/60 bg-white px-3 py-2 shadow-lg dark:border-amber-900/30 dark:bg-gray-900" style="animation-delay: 0.6s;">
                            <span class="text-sm">⭐</span>
                            <div>
                                <p class="text-[11px] font-bold text-gray-900 dark:text-white">Top Rated</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-600">4.9 / 5.0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats bar -->
                <div class="pb-20 lg:pb-28">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div
                            v-for="(stat, i) in statCards"
                            :key="stat.label"
                            data-reveal
                            class="reveal-stat rounded-xl border border-gray-100 bg-white/80 px-5 py-4 text-center shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/70"
                            :style="`--d: ${i * 0.08}s`"
                        >
                            <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ stat.value }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-500">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════
             HOW IT WORKS
             ═══════════════════════════════════════════════ -->
        <section class="bg-gray-50/90 py-24 dark:bg-[#0d1117]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal class="reveal-up mx-auto max-w-2xl text-center" style="--d: 0s;">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.12em] text-orange-500">Simple process</p>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Book in 3 simple steps</h2>
                    <p class="mt-4 text-base text-gray-500 dark:text-gray-400">No login, no hassle. Just fast, reliable healthcare access.</p>
                </div>
                <div class="mt-14 grid gap-5 sm:grid-cols-3">
                    <div
                        v-for="(step, i) in steps"
                        :key="step.title"
                        data-reveal
                        class="reveal-up group relative rounded-2xl bg-white p-7 ring-1 ring-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:bg-gray-900 dark:ring-gray-800"
                        :style="`--d: ${(i + 1) * 0.1}s`"
                    >
                        <div class="absolute right-5 top-5 flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-400 dark:bg-gray-800 dark:text-gray-600">
                            {{ step.num }}
                        </div>
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl transition-transform duration-300 group-hover:scale-110"
                            :class="{
                                'bg-orange-100 dark:bg-orange-950/60': step.accent === 'orange',
                                'bg-sky-100 dark:bg-sky-950/60': step.accent === 'sky',
                                'bg-green-100 dark:bg-green-950/60': step.accent === 'green',
                            }"
                        >
                            <svg
                                class="h-5 w-5"
                                :class="{
                                    'text-orange-600': step.accent === 'orange',
                                    'text-sky-600': step.accent === 'sky',
                                    'text-green-600': step.accent === 'green',
                                }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                            >
                                <path :d="step.iconPath" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h3 class="mt-5 text-base font-semibold text-gray-900 dark:text-white">{{ step.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════
             SPECIALIZATIONS
             ═══════════════════════════════════════════════ -->
        <section class="bg-white py-24 dark:bg-gray-950">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div data-reveal class="reveal-up mx-auto max-w-2xl text-center" style="--d: 0s;">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.12em] text-orange-500">Our network</p>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Browse by Specialization</h2>
                    <p class="mt-4 text-base text-gray-500 dark:text-gray-400">Expert care across every medical field.</p>
                </div>
                <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <Link
                        v-for="(spec, i) in featuredSpecializations"
                        :key="spec"
                        :href="`/doctors?specialization=${encodeURIComponent(spec)}`"
                        data-reveal
                        class="reveal-up group flex flex-col items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/80 p-5 text-center transition-all duration-200 hover:-translate-y-0.5 hover:border-orange-200 hover:bg-orange-50 hover:shadow-md dark:border-gray-800 dark:bg-gray-900/50 dark:hover:border-orange-800/50 dark:hover:bg-orange-950/20"
                        :style="`--d: ${(i % 4) * 0.06}s`"
                    >
                        <span class="text-2xl">{{ emoji(spec) }}</span>
                        <span class="text-xs font-semibold text-gray-700 transition-colors group-hover:text-orange-600 dark:text-gray-300 dark:group-hover:text-orange-400">{{ spec }}</span>
                    </Link>
                </div>
                <div data-reveal class="reveal-up mt-10 text-center" style="--d: 0.2s;">
                    <Link href="/doctors" class="inline-flex items-center gap-1.5 text-sm font-semibold text-orange-600 transition-colors hover:text-orange-700 dark:text-orange-500 dark:hover:text-orange-400">
                        View all doctors
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════
             REVIEWS
             ═══════════════════════════════════════════════ -->
        <section v-if="topReviews.length" class="bg-gray-50/90 py-24 dark:bg-[#0d1117]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Header -->
                <div data-reveal class="reveal-up mx-auto max-w-2xl text-center" style="--d: 0s;">
                    <p class="mb-2 text-xs font-bold uppercase tracking-[0.12em] text-orange-500">Patient voices</p>
                    <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                        What patients are saying
                    </h2>
                    <p class="mt-4 text-base text-gray-500 dark:text-gray-400">
                        Real reviews from real patients across our top-rated doctors.
                    </p>
                </div>

                <!-- Masonry-style card grid -->
                <div class="mt-14 columns-1 gap-5 space-y-5 sm:columns-2 lg:columns-3">
                    <div
                        v-for="(review, i) in topReviews"
                        :key="review.id"
                        data-reveal
                        class="reveal-up break-inside-avoid rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
                        :style="`--d: ${(i % 3) * 0.08}s`"
                    >
                        <!-- Stars -->
                        <div class="flex items-center gap-0.5">
                            <svg
                                v-for="s in 5"
                                :key="s"
                                class="h-4 w-4 transition-colors"
                                :class="s <= review.rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-700'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>

                        <!-- Comment -->
                        <blockquote class="mt-3 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                            "{{ review.comment }}"
                        </blockquote>

                        <!-- Divider -->
                        <div class="my-4 border-t border-gray-100 dark:border-gray-800" />

                        <!-- Patient + Doctor row -->
                        <div class="flex items-center justify-between gap-3">
                            <!-- Patient avatar + name -->
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-[11px] font-bold text-white"
                                    :class="avatarGradient(review.id)"
                                >
                                    {{ initials(review.patient_name) }}
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ review.patient_name }}</p>
                                    <p class="text-[10px] text-gray-400 dark:text-gray-600">{{ review.created_at }}</p>
                                </div>
                            </div>

                            <!-- Doctor chip -->
                            <Link
                                v-if="review.doctor_name && review.doctor_slug"
                                :href="`/doctors/${review.doctor_slug}`"
                                class="flex min-w-0 items-center gap-1.5 rounded-full border border-gray-100 bg-gray-50 px-2.5 py-1 transition-colors hover:border-orange-200 hover:bg-orange-50 dark:border-gray-800 dark:bg-gray-800/60 dark:hover:border-orange-800/50 dark:hover:bg-orange-950/20"
                            >
                                <img
                                    v-if="review.doctor_avatar"
                                    :src="review.doctor_avatar"
                                    :alt="review.doctor_name"
                                    class="h-5 w-5 shrink-0 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-orange-100 text-[8px] font-bold text-orange-600 dark:bg-orange-950/50 dark:text-orange-400"
                                >
                                    {{ initials(review.doctor_name) }}
                                </div>
                                <span class="max-w-[90px] truncate text-[10px] font-semibold text-gray-600 dark:text-gray-400">
                                    Dr. {{ review.doctor_name }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty state (shouldn't appear due to v-if, but just in case) -->
                <div v-if="!topReviews.length" class="mt-12 text-center text-sm text-gray-400">
                    No reviews yet. Be the first to share your experience!
                </div>

            </div>
        </section>

        <!-- ═══════════════════════════════════════════════
             CTA
             ═══════════════════════════════════════════════ -->
        <section class="relative overflow-hidden py-24">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-600 via-orange-500 to-amber-500 dark:from-orange-900 dark:via-orange-800 dark:to-amber-900" />
            <div
                class="absolute inset-0 opacity-[0.06]"
                style="background-image: linear-gradient(#fff 1px, transparent 1px), linear-gradient(90deg, #fff 1px, transparent 1px); background-size: 44px 44px;"
                aria-hidden="true"
            />
            <div data-reveal class="reveal-up relative mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8" style="--d: 0s;">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to book your appointment?</h2>
                <p class="mt-4 text-base text-orange-100/90">Join thousands of patients who trust DokHub for their healthcare needs.</p>
                <div class="mt-10">
                    <Link
                        href="/doctors"
                        class="group inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-sm font-semibold text-orange-700 shadow-xl transition-all duration-200 hover:-translate-y-px hover:bg-orange-50"
                    >
                        Find a Doctor Now
                        <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

    </GuestLayout>
</template>

<style scoped>
/* ── Hero entrance animations ────────────────────────────── */
@keyframes fade-up-in {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fade-right-in {
    from { opacity: 0; transform: translateX(40px) scale(0.97); }
    to   { opacity: 1; transform: translateX(0) scale(1); }
}

.hero-in {
    animation: fade-up-in 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
    animation-delay: 0.05s;
}
/* Each delay class overrides animation-delay set by the shorthand in .hero-in */
.hero-d1 { animation-delay: 0.12s; }
.hero-d2 { animation-delay: 0.22s; }
.hero-d3 { animation-delay: 0.32s; }
.hero-d4 { animation-delay: 0.42s; }

.hero-card-in {
    animation: fade-right-in 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
    animation-delay: 0.28s;
}

/* ── SVG underline draw-on ──────────────────────────────── */
.underline-draw {
    stroke-dasharray: 240;
    stroke-dashoffset: 240;
    animation: dash-draw 0.85s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    animation-delay: 0.58s;
}
@keyframes dash-draw { to { stroke-dashoffset: 0; } }

/* ── Floating badge bobbing ─────────────────────────────── */
@keyframes float-bob {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-7px); }
}
.float-badge {
    animation: float-bob 3.8s ease-in-out infinite;
}

/* ── Background blob drift ──────────────────────────────── */
@keyframes blob-drift {
    0%, 100% {
        transform: translate(0, 0) scale(1);
        border-radius: 62% 38% 50% 50% / 55% 45% 55% 45%;
    }
    33% {
        transform: translate(30px, -40px) scale(1.05);
        border-radius: 42% 58% 60% 40% / 50% 62% 38% 50%;
    }
    66% {
        transform: translate(-20px, 28px) scale(0.96);
        border-radius: 55% 45% 40% 60% / 45% 55% 60% 40%;
    }
}
.blob-drift { animation: blob-drift 16s ease-in-out infinite; }

/* ── Scroll-reveal: generic fade-up ────────────────────── */
[data-reveal].reveal-up {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.65s ease, transform 0.65s ease;
    transition-delay: var(--d, 0s);
}
[data-reveal].reveal-up[data-visible='true'] {
    opacity: 1;
    transform: translateY(0);
}

/* ── Scroll-reveal: stat cards ──────────────────────────── */
[data-reveal].reveal-stat {
    opacity: 0;
    transform: translateY(16px);
    transition: opacity 0.55s ease, transform 0.55s ease;
    transition-delay: var(--d, 0s);
}
[data-reveal].reveal-stat[data-visible='true'] {
    opacity: 1;
    transform: translateY(0);
}
</style>
