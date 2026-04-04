<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor, DoctorSchedule } from '@/types/dokhub';
import QRCode from 'qrcode';

const props = defineProps<{ doctor: Doctor }>();

const qrDataUrl = ref<string>('');
const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

const profileUrl = computed(() => `${window.location.origin}/doctors/${props.doctor.slug}`);
const siteHostname = window.location.hostname;

const activeSchedules = computed<DoctorSchedule[]>(() =>
    (props.doctor.schedules ?? [])
        .filter(s => s.is_active)
        .sort((a, b) => a.day_of_week - b.day_of_week)
);

function formatTime(t: string): string {
    const [hStr, mStr] = t.split(':');
    const h = parseInt(hStr, 10);
    const m = mStr ?? '00';
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 === 0 ? 12 : h % 12;
    return `${h12}:${m} ${ampm}`;
}

onMounted(async () => {
    try {
        qrDataUrl.value = await QRCode.toDataURL(profileUrl.value, {
            width: 200,
            margin: 1,
            color: { dark: '#1e1b4b', light: '#ffffff' },
        });
    } catch {
        // silently fail — QR simply won't show
    }
});

function printPoster() {
    window.print();
}
</script>

<template>
    <DoctorLayout>
        <div class="mx-auto max-w-3xl px-4 py-8">
            <!-- Page header (hidden on print) -->
            <div class="no-print mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Advertisement Poster</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Print or save this poster to share with your patients.
                    </p>
                </div>
                <button
                    @click="printPoster"
                    class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 active:scale-95"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Poster
                </button>
            </div>

            <!-- ===== POSTER ===== -->
            <div id="poster" class="poster-card overflow-hidden rounded-2xl shadow-2xl">

                <!-- Header band -->
                <div class="poster-header relative overflow-hidden bg-gradient-to-br from-orange-500 via-orange-600 to-indigo-700 px-8 pb-10 pt-8 text-white">
                    <!-- Decorative circles -->
                    <span class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10"></span>
                    <span class="absolute -bottom-8 left-1/3 h-28 w-28 rounded-full bg-white/10"></span>
                    <span class="absolute bottom-4 right-20 h-12 w-12 rounded-full bg-white/10"></span>

                    <div class="relative flex items-center gap-5">
                        <!-- Avatar -->
                        <div class="shrink-0">
                            <img
                                v-if="doctor.avatar_url"
                                :src="doctor.avatar_url"
                                :alt="doctor.name"
                                class="h-20 w-20 rounded-full object-cover ring-4 ring-white/40"
                            />
                            <div
                                v-else
                                class="flex h-20 w-20 items-center justify-center rounded-full bg-white/20 ring-4 ring-white/40 text-3xl font-bold"
                            >
                                {{ doctor.name.charAt(0).toUpperCase() }}
                            </div>
                        </div>

                        <!-- Name & qualifications -->
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-widest text-orange-200">Medical Professional</p>
                            <h2 class="mt-0.5 text-3xl font-extrabold leading-tight">{{ doctor.name }}</h2>
                            <p v-if="doctor.qualification" class="mt-1 text-base font-medium text-orange-100">
                                {{ doctor.qualification }}
                            </p>
                            <p v-if="doctor.experience_years" class="mt-1 text-sm text-orange-200">
                                {{ doctor.experience_years }} year{{ doctor.experience_years === 1 ? '' : 's' }} of experience
                            </p>
                        </div>
                    </div>

                    <!-- Specializations -->
                    <div v-if="doctor.specialization?.length" class="relative mt-5 flex flex-wrap gap-2">
                        <span
                            v-for="spec in doctor.specialization"
                            :key="spec"
                            class="spec-pill rounded-full border border-white/40 bg-white/20 px-3 py-1 text-xs font-semibold text-white"
                        >
                            {{ spec }}
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="poster-body grid gap-0 bg-white sm:grid-cols-2">

                    <!-- Left: Schedule + Insurance -->
                    <div class="border-r border-gray-100 px-7 py-6">

                        <!-- Schedule -->
                        <div v-if="activeSchedules.length" class="mb-6">
                            <h3 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-orange-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Consultation Hours
                            </h3>
                            <ul class="space-y-1.5">
                                <li
                                    v-for="s in activeSchedules"
                                    :key="s.id"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="w-24 font-semibold text-gray-700">{{ DAY_NAMES[s.day_of_week] }}</span>
                                    <span class="text-gray-500">{{ formatTime(s.start_time) }} – {{ formatTime(s.end_time) }}</span>
                                </li>
                            </ul>
                        </div>
                        <div v-else class="mb-6">
                            <h3 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-orange-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Consultation Hours
                            </h3>
                            <p class="text-sm text-gray-400 italic">Schedule not set yet.</p>
                        </div>

                        <!-- Insurance -->
                        <div v-if="doctor.insurance?.length">
                            <h3 class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-indigo-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Accepted Insurance
                            </h3>
                            <ul class="space-y-1">
                                <li
                                    v-for="ins in doctor.insurance"
                                    :key="ins"
                                    class="flex items-center gap-2 text-sm text-gray-600"
                                >
                                    <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                                    {{ ins }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Right: QR + bio + location -->
                    <div class="flex flex-col items-center justify-between px-7 py-6 text-center">
                        <div class="w-full">
                            <!-- Bio -->
                            <div v-if="doctor.bio" class="mb-6 text-left">
                                <h3 class="mb-2 text-xs font-bold uppercase tracking-widest text-gray-400">About</h3>
                                <p class="text-sm leading-relaxed text-gray-600">{{ doctor.bio }}</p>
                            </div>

                            <!-- Location -->
                            <div v-if="doctor.location" class="mb-6 flex items-start gap-2 text-left">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="text-sm text-gray-600">{{ doctor.location }}</p>
                            </div>

                            <!-- Consultation fee -->
                            <div v-if="doctor.consultation_fee" class="mb-6 rounded-xl bg-orange-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wider text-orange-500">Consultation Fee</p>
                                <p class="mt-0.5 text-2xl font-extrabold text-orange-700">₱ {{ Number(doctor.consultation_fee).toLocaleString() }}</p>
                            </div>
                        </div>

                        <!-- QR Code -->
                        <div class="flex flex-col items-center gap-3">
                            <div class="rounded-2xl bg-indigo-900 p-3 shadow-lg">
                                <img
                                    v-if="qrDataUrl"
                                    :src="qrDataUrl"
                                    alt="QR Code"
                                    class="h-28 w-28"
                                />
                                <div v-else class="flex h-28 w-28 items-center justify-center text-xs text-indigo-300">
                                    Generating…
                                </div>
                            </div>
                            <p class="text-xs font-semibold text-gray-500">Scan to book an appointment</p>
                            <p class="text-[11px] break-all text-indigo-500 font-medium">{{ profileUrl }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer band -->
                <div class="poster-footer bg-gradient-to-r from-indigo-700 to-orange-600 px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img src="/logo.png" alt="DokHub" class="h-6 w-auto brightness-0 invert" />
                            <span class="text-sm font-bold text-white">DokHub</span>
                        </div>
                        <p class="text-xs text-white/70">Book online · Fast · Convenient</p>
                        <p class="text-xs font-semibold text-white">{{ siteHostname }}</p>
                    </div>
                </div>
            </div><!-- end #poster -->
        </div>
    </DoctorLayout>
</template>

<style>
/* ── Print styles ── */
@media print {
    body * {
        visibility: hidden !important;
    }

    #poster,
    #poster * {
        visibility: visible !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    #poster {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    /* Solid pill so it shows regardless of background transparency support */
    .spec-pill {
        background-color: rgba(255, 255, 255, 0.25) !important;
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
        color: #ffffff !important;
    }
}
</style>
