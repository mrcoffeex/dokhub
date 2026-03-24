<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/layouts/GuestLayout.vue';

const stats = [
    { value: '500+', label: 'Verified Doctors' },
    { value: '50k+', label: 'Appointments Booked' },
    { value: '20+', label: 'Specializations' },
    { value: '4.9★', label: 'Average Rating' },
];

const steps = [
    {
        num: '1',
        title: 'Browse Doctors',
        description: 'Filter by specialization, location, or availability to find the right doctor for your needs.',
        bg: 'bg-violet-100',
        color: 'text-violet-600',
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

const specializations = [
    { name: 'Cardiology', emoji: '❤️' },
    { name: 'Neurology', emoji: '🧠' },
    { name: 'Dermatology', emoji: '✨' },
    { name: 'Orthopedics', emoji: '🦴' },
    { name: 'Pediatrics', emoji: '👶' },
    { name: 'General Practice', emoji: '🩺' },
    { name: 'Gynecology', emoji: '🌸' },
    { name: 'Psychiatry', emoji: '🧘' },
];
</script>

<template>
    <Head title="DokHub — Healthcare Made Simple" />
    <GuestLayout>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-white">
            <div class="absolute inset-0 bg-gradient-to-br from-violet-50 via-white to-sky-50" />
            <div class="relative mx-auto max-w-7xl px-4 pb-24 pt-20 sm:px-6 lg:px-8 lg:pb-32 lg:pt-28">
                <div class="mx-auto max-w-3xl text-center">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-4 py-1.5 text-sm font-medium text-violet-700">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-violet-400 opacity-75" />
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-violet-500" />
                        </span>
                        Healthcare appointments made effortless
                    </div>
                    <h1 class="text-5xl font-bold tracking-tight text-gray-900 sm:text-6xl lg:text-7xl">
                        See the right doctor,<br />
                        <span class="bg-gradient-to-r from-violet-600 to-sky-500 bg-clip-text text-transparent">right now.</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 sm:text-xl">
                        Book a specialist appointment in under a minute. No account required, no waiting on hold — just instant access to top healthcare professionals.
                    </p>
                    <div class="mt-10 flex flex-col items-center gap-4 sm:flex-row sm:justify-center">
                        <Link href="/doctors" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-violet-200 transition-all hover:bg-violet-700 hover:shadow-violet-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600">
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
                    <div v-for="stat in stats" :key="stat.label" class="rounded-2xl border border-gray-100 bg-white px-6 py-5 text-center shadow-sm">
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
                        v-for="spec in specializations"
                        :key="spec.name"
                        :href="`/doctors?specialization=${encodeURIComponent(spec.name)}`"
                        class="group flex flex-col items-center gap-3 rounded-2xl border border-gray-100 bg-gray-50 p-6 text-center transition-all hover:border-violet-200 hover:bg-violet-50 hover:shadow-md"
                    >
                        <span class="text-3xl">{{ spec.emoji }}</span>
                        <span class="text-sm font-semibold text-gray-700 group-hover:text-violet-700">{{ spec.name }}</span>
                    </Link>
                </div>
                <div class="mt-10 text-center">
                    <Link href="/doctors" class="inline-flex items-center gap-2 text-sm font-semibold text-violet-600 hover:text-violet-700">
                        View all doctors
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="relative overflow-hidden bg-violet-600 py-24">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-violet-500 via-violet-600 to-purple-700" />
            <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to book your appointment?</h2>
                <p class="mt-4 text-lg text-violet-200">Join thousands of patients who trust DokHub for their healthcare needs.</p>
                <div class="mt-10">
                    <Link href="/doctors" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-violet-700 shadow-lg transition-all hover:bg-violet-50">
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
