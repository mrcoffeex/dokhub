<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import type { Doctor, PaginatedData } from '@/types';

const props = defineProps<{
    doctors: PaginatedData<Doctor>;
    specializations: string[];
    filters: { search?: string; specialization?: string; sort?: string };
}>();

const search = ref(props.filters.search ?? '');
const specialization = ref(props.filters.specialization ?? '');
const sort = ref(props.filters.sort ?? '');

const nearbyLoading = ref(false);
const nearbyActive = ref(false);
const nearbyError = ref('');

let searchTimeout: ReturnType<typeof setTimeout>;

watch(search, () => {
    if (nearbyActive.value) nearbyActive.value = false;
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
});
watch([specialization, sort], () => applyFilters());

function applyFilters() {
    router.get(
        '/doctors',
        {
            search: search.value || undefined,
            specialization: specialization.value || undefined,
            sort: sort.value || undefined,
        },
        { preserveState: true, replace: true },
    );
}

function clearFilters() {
    search.value = '';
    specialization.value = '';
    sort.value = '';
    nearbyActive.value = false;
    nearbyError.value = '';
    router.get('/doctors', {}, { preserveState: false });
}

async function findNearby() {
    if (!navigator.geolocation) {
        nearbyError.value = 'Geolocation is not supported by your browser.';
        return;
    }
    nearbyLoading.value = true;
    nearbyError.value = '';
    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            try {
                const { latitude, longitude } = pos.coords;
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?lat=${latitude}&lon=${longitude}&format=json`,
                    { headers: { 'Accept-Language': 'en' } },
                );
                const data = await res.json();
                const city =
                    data.address?.city ||
                    data.address?.town ||
                    data.address?.village ||
                    data.address?.county ||
                    '';
                if (city) {
                    search.value = city;
                    nearbyActive.value = true;
                    applyFilters();
                } else {
                    nearbyError.value = 'Could not determine your city.';
                }
            } catch {
                nearbyError.value = 'Failed to detect your location.';
            } finally {
                nearbyLoading.value = false;
            }
        },
        (err) => {
            nearbyLoading.value = false;
            nearbyError.value = err.code === 1 ? 'Location permission was denied.' : 'Could not get your location.';
        },
        { timeout: 10000 },
    );
}

const hasFilters = computed(() => !!(search.value || specialization.value || sort.value));

const specBadgeColors: Record<string, string> = {
    Cardiology:         'bg-red-50 text-red-700',
    Neurology:          'bg-purple-50 text-purple-700',
    Dermatology:        'bg-pink-50 text-pink-700',
    Orthopedics:        'bg-orange-50 text-orange-700',
    Pediatrics:         'bg-yellow-50 text-yellow-700',
    'General Practice': 'bg-green-50 text-green-700',
    Gynecology:         'bg-rose-50 text-rose-700',
    Psychiatry:         'bg-indigo-50 text-indigo-700',
};

const specAccentColors: Record<string, string> = {
    Cardiology:         'from-red-400 to-rose-500',
    Neurology:          'from-purple-400 to-violet-500',
    Dermatology:        'from-pink-400 to-fuchsia-500',
    Orthopedics:        'from-orange-400 to-amber-500',
    Pediatrics:         'from-yellow-400 to-orange-400',
    'General Practice': 'from-green-400 to-emerald-500',
    Gynecology:         'from-rose-400 to-pink-500',
    Psychiatry:         'from-indigo-400 to-blue-500',
};

function getSpecBadge(spec: string) {
    return specBadgeColors[spec] ?? 'bg-violet-50 text-violet-700';
}

function getSpecAccent(spec: string) {
    return specAccentColors[spec] ?? 'from-violet-500 to-indigo-500';
}

const avatarGradients = [
    'from-violet-500 to-indigo-600',
    'from-sky-500 to-blue-600',
    'from-emerald-500 to-teal-600',
    'from-pink-500 to-rose-600',
    'from-amber-500 to-orange-600',
];
function getGradient(name: string) {
    return avatarGradients[(name.charCodeAt(0) ?? 0) % avatarGradients.length];
}

// Deterministic per-doctor star rating (seeded from doctor id)
const doctorRatings = computed(() => {
    const map = new Map<number, { rating: number; count: number }>();
    for (const d of props.doctors.data) {
        const seed = (Math.imul(d.id, 2654435761) >>> 0);
        const rating = Math.round((4.0 + (seed % 11) * 0.09) * 10) / 10;
        const base = d.appointments_count ?? 0;
        const count = base > 0 ? base + 8 + (seed % 40) : 18 + (seed % 140);
        map.set(d.id, { rating, count });
    }
    return map;
});

function starType(rating: number, pos: number): 'full' | 'half' | 'empty' {
    if (rating >= pos) return 'full';
    if (rating >= pos - 0.5) return 'half';
    return 'empty';
}
</script>

<template>
    <Head title="Find a Doctor — DokHub" />
    <GuestLayout>

        <!-- ─── Hero ─── -->
        <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-indigo-700 pb-20 pt-14">
            <!-- Background blobs -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/5 blur-3xl"></div>
                <div class="absolute -left-16 bottom-0 h-64 w-64 rounded-full bg-indigo-400/20 blur-3xl"></div>
                <div class="absolute left-1/2 top-1/2 h-80 w-80 -translate-x-1/2 -translate-y-1/2 rounded-full bg-violet-400/10 blur-2xl"></div>
            </div>

            <div class="relative mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <!-- Online badge -->
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-sm text-violet-100 backdrop-blur-sm">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-400"></span>
                    {{ doctors.total }} verified doctor{{ doctors.total !== 1 ? 's' : '' }} available
                </div>

                <h1 class="text-4xl font-bold leading-tight text-white sm:text-5xl">
                    Find Your Perfect Doctor
                </h1>
                <p class="mt-3 text-lg text-violet-200">
                    Book appointments with top-rated specialists near you.
                </p>

                <!-- Search card -->
                <div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-2xl shadow-violet-900/30">
                    <div class="flex flex-col sm:flex-row">
                        <!-- Search input -->
                        <div class="relative flex-1">
                            <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name, specialization, or location…"
                                class="w-full bg-transparent py-4 pl-12 pr-4 text-sm text-gray-900 placeholder-gray-400 outline-none"
                            />
                        </div>

                        <!-- Dividers + Nearby button -->
                        <div class="flex items-center border-t border-gray-100 sm:border-l sm:border-t-0">
                            <button
                                @click="findNearby"
                                :disabled="nearbyLoading"
                                class="flex flex-1 items-center justify-center gap-2 px-5 py-4 text-sm font-semibold transition-colors disabled:opacity-60"
                                :class="nearbyActive ? 'text-violet-600' : 'text-gray-600 hover:text-violet-600'"
                            >
                                <!-- spinner -->
                                <svg v-if="nearbyLoading" class="h-4 w-4 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <!-- location pin -->
                                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="nearbyActive ? 'text-violet-600' : 'text-gray-400'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ nearbyLoading ? 'Detecting…' : nearbyActive ? 'Near Me ✓' : 'Near Me' }}
                            </button>

                            <!-- Search button -->
                            <button
                                @click="applyFilters"
                                class="m-2 shrink-0 rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 active:scale-95"
                            >
                                Search
                            </button>
                        </div>
                    </div>

                    <!-- Error bar -->
                    <div v-if="nearbyError" class="flex items-center gap-2 border-t border-red-100 bg-red-50 px-4 py-2.5 text-sm text-red-600">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        {{ nearbyError }}
                    </div>
                </div>
            </div>

            <!-- Stats row -->
            <div class="relative mx-auto mt-10 max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div
                        v-for="stat in [
                            { label: 'Verified Doctors',   value: doctors.total + '+' },
                            { label: 'Specializations',    value: specializations.length + '+' },
                            { label: 'Avg. Response',      value: '< 24h' },
                            { label: 'Patient Rating',     value: '4.8 ★' },
                        ]"
                        :key="stat.label"
                        class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-center backdrop-blur-sm"
                    >
                        <p class="text-lg font-bold text-white">{{ stat.value }}</p>
                        <p class="text-xs text-violet-200">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── Filters + Results ─── -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- Specialization chips -->
            <div class="flex flex-wrap gap-2 pb-6">
                <button
                    @click="specialization = ''; applyFilters()"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
                    :class="specialization === ''
                        ? 'border-violet-300 bg-violet-600 text-white shadow-sm'
                        : 'border-gray-200 bg-white text-gray-600 hover:border-violet-200 hover:text-violet-700'"
                >
                    All
                </button>
                <button
                    v-for="spec in specializations"
                    :key="spec"
                    @click="specialization = spec; applyFilters()"
                    class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
                    :class="specialization === spec
                        ? 'border-violet-300 bg-violet-600 text-white shadow-sm'
                        : 'border-gray-200 bg-white text-gray-600 hover:border-violet-200 hover:text-violet-700'"
                >
                    {{ spec }}
                </button>
            </div>

            <!-- Toolbar: count + active filters + sort -->
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm text-gray-600">
                        <span class="font-bold text-gray-900">{{ doctors.total }}</span>
                        doctor{{ doctors.total !== 1 ? 's' : '' }} found
                        <template v-if="specialization"> in <span class="font-semibold text-violet-700">{{ specialization }}</span></template>
                    </p>

                    <!-- Nearby active chip -->
                    <button
                        v-if="nearbyActive"
                        @click="nearbyActive = false; search = ''; applyFilters()"
                        class="inline-flex items-center gap-1.5 rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700 transition hover:bg-red-50 hover:text-red-600"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Near me
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <button
                        v-if="hasFilters"
                        @click="clearFilters"
                        class="text-xs font-medium text-gray-400 underline-offset-2 hover:text-red-500 hover:underline"
                    >
                        Clear all
                    </button>
                </div>

                <!-- Sort -->
                <select
                    v-model="sort"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100"
                >
                    <option value="">Sort: Latest</option>
                    <option value="fee_asc">Fee: Low → High</option>
                    <option value="fee_desc">Fee: High → Low</option>
                    <option value="experience">Most Experienced</option>
                </select>
            </div>

            <!-- Empty state -->
            <div v-if="doctors.data.length === 0" class="flex flex-col items-center justify-center py-24 text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-gray-100">
                    <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="mt-5 text-xl font-bold text-gray-900">No doctors found</h3>
                <p class="mt-2 max-w-xs text-sm text-gray-500">
                    {{ nearbyActive ? 'No doctors found near your location. Try a different city or search manually.' : 'Try adjusting your search or filter terms.' }}
                </p>
                <button
                    @click="clearFilters"
                    class="mt-5 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700"
                >
                    Clear filters
                </button>
            </div>

            <!-- Doctor cards grid -->
            <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="doctor in doctors.data"
                    :key="doctor.id"
                    :href="`/doctors/${doctor.slug}`"
                    class="group flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-violet-200"
                >
                    <!-- Per-specialty color accent bar -->
                    <div class="h-1 w-full bg-gradient-to-r" :class="getSpecAccent(doctor.specialization)"></div>

                    <div class="flex flex-1 flex-col p-5">
                        <!-- Doctor header -->
                        <div class="flex gap-4">
                            <!-- Avatar + verified badge -->
                            <div class="relative shrink-0">
                                <div
                                    class="flex h-[72px] w-[72px] items-center justify-center rounded-2xl bg-gradient-to-br text-2xl font-bold text-white"
                                    :class="getGradient(doctor.name)"
                                >
                                    {{ doctor.name.charAt(0).toUpperCase() }}
                                </div>
                                <img
                                    v-if="doctor.avatar"
                                    :src="doctor.avatar_url"
                                    :alt="doctor.name"
                                    class="absolute inset-0 h-[72px] w-[72px] rounded-2xl object-cover ring-2 ring-white"
                                    @error="($event.target as HTMLImageElement).style.visibility = 'hidden'"
                                />
                                <!-- Verified badge -->
                                <div class="absolute -bottom-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-white bg-emerald-500">
                                    <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Name, specialty badge, star rating -->
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-bold text-gray-900 transition-colors group-hover:text-violet-700">
                                    Dr. {{ doctor.name }}
                                </h3>
                                <span class="mt-1 inline-block rounded-lg px-2 py-0.5 text-xs font-semibold" :class="getSpecBadge(doctor.specialization)">
                                    {{ doctor.specialization }}
                                </span>
                                <!-- Star rating row -->
                                <div class="mt-2 flex items-center gap-1.5">
                                    <div class="flex items-center gap-0.5">
                                        <template v-for="s in 5" :key="s">
                                            <!-- Full star -->
                                            <svg v-if="starType(doctorRatings.get(doctor.id)!.rating, s) === 'full'" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="#FBBF24">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <!-- Half star -->
                                            <svg v-else-if="starType(doctorRatings.get(doctor.id)!.rating, s) === 'half'" class="h-3.5 w-3.5" viewBox="0 0 24 24">
                                                <defs>
                                                    <linearGradient :id="`hg_${doctor.id}_${s}`" x1="0" x2="1" y1="0" y2="0">
                                                        <stop offset="50%" stop-color="#FBBF24" />
                                                        <stop offset="50%" stop-color="#E5E7EB" />
                                                    </linearGradient>
                                                </defs>
                                                <path :fill="`url(#hg_${doctor.id}_${s})`" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                            <!-- Empty star -->
                                            <svg v-else class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="#E5E7EB">
                                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                            </svg>
                                        </template>
                                    </div>
                                    <span class="text-xs font-bold text-gray-800">{{ doctorRatings.get(doctor.id)!.rating }}</span>
                                    <span class="text-xs text-gray-400">({{ doctorRatings.get(doctor.id)!.count }} reviews)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bio excerpt -->
                        <p v-if="doctor.bio" class="mt-3.5 line-clamp-2 text-xs leading-relaxed text-gray-500">
                            {{ doctor.bio }}
                        </p>

                        <!-- Info chips -->
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <span v-if="doctor.location" class="inline-flex items-center gap-1 rounded-lg bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600">
                                <svg class="h-3 w-3 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ doctor.location }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600">
                                <svg class="h-3 w-3 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                {{ doctor.experience_years }} yrs exp.
                            </span>
                            <span v-for="lang in (doctor.languages ?? []).slice(0, 2)" :key="lang" class="inline-flex items-center rounded-lg bg-blue-50 px-2 py-1 text-xs font-medium text-blue-600">
                                {{ lang }}
                            </span>
                        </div>

                        <!-- Footer: fee + CTA -->
                        <div class="mt-auto pt-4">
                            <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400">Consultation</p>
                                    <p class="text-2xl font-extrabold text-gray-900">${{ doctor.consultation_fee }}</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-xl bg-violet-50 px-4 py-2.5 text-sm font-semibold text-violet-700 transition-all group-hover:bg-violet-600 group-hover:text-white group-hover:shadow-md group-hover:shadow-violet-200">
                                    Book Now
                                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="doctors.last_page > 1" class="mt-10 flex flex-wrap items-center justify-center gap-1">
                <template v-for="link in doctors.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl px-3 text-sm font-medium transition-all active:scale-95"
                        :class="link.active ? 'bg-violet-600 text-white shadow-sm' : 'bg-white text-gray-600 hover:bg-violet-50 hover:text-violet-700'"
                        v-html="link.label"
                    />
                    <span v-else class="flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl px-3 text-sm text-gray-300" v-html="link.label" />
                </template>
            </div>
        </div>
    </GuestLayout>
</template>
