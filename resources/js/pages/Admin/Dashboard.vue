<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Appointment, Doctor, PageProps } from '@/types';

const props = defineProps<{
    stats: {
        total_doctors: number;
        approved_doctors: number;
        pending_doctors: number;
        suspended_doctors: number;
        total_appointments: number;
        today_appointments: number;
        today_confirmed: number;
        pending_appointments: number;
        confirmed_appointments: number;
        completed_appointments: number;
        cancelled_appointments: number;
        total_revenue: number;
    };
    week_trend: { date: string; label: string; count: number }[];
    top_doctors: (Doctor & { appointments_count: number })[];
    recent_appointments: Appointment[];
}>();

const page = usePage<PageProps>();
const adminName = computed(() => (page.props.auth as any)?.user?.name?.split(' ')[0] ?? 'Admin');
const hour = new Date().getHours();
const greeting = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
const today = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

const revenueFormatted = computed(() => {
    const n = props.stats.total_revenue;
    if (n >= 1_000_000) return `₱${(n / 1_000_000).toFixed(1)}M`;
    if (n >= 1000) return `₱${(n / 1000).toFixed(1)}k`;
    return `₱${n.toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
});

const completionRate = computed(() => {
    const total = props.stats.total_appointments;
    if (!total) return 0;
    return Math.round((props.stats.completed_appointments / total) * 100);
});

const statusDist = computed(() => {
    const total = props.stats.total_appointments || 1;
    return {
        completed: { count: props.stats.completed_appointments, pct: Math.round((props.stats.completed_appointments / total) * 100) },
        confirmed: { count: props.stats.confirmed_appointments, pct: Math.round((props.stats.confirmed_appointments / total) * 100) },
        pending:   { count: props.stats.pending_appointments,   pct: Math.round((props.stats.pending_appointments   / total) * 100) },
        cancelled: { count: props.stats.cancelled_appointments, pct: Math.round((props.stats.cancelled_appointments / total) * 100) },
    };
});

const sparkline = computed(() => {
    const data = props.week_trend.map(d => d.count);
    const max  = Math.max(...data, 1);
    const W = 280, H = 72, padY = 10;
    const steps = data.length > 1 ? data.length - 1 : 1;
    const pts = data.map((v, i) => ({
        x: (i / steps) * W,
        y: H - padY - ((v / max) * (H - padY * 2)),
    }));
    const polyline = pts.map(p => `${p.x},${p.y}`).join(' ');
    const area = `M${pts[0].x},${pts[0].y} ${pts.slice(1).map(p => `L${p.x},${p.y}`).join(' ')} L${pts[pts.length - 1].x},${H} L${pts[0].x},${H} Z`;
    return { polyline, area, pts, W, H };
});

function formatDate(dateStr: string) {
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function formatTime(timeStr: string) {
    const [h, m] = timeStr.split(':').map(Number);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

const statusConfig: Record<string, { label: string; bg: string; text: string; dot: string }> = {
    pending:   { label: 'Pending',   bg: 'bg-amber-50 dark:bg-amber-900/20',    text: 'text-amber-700 dark:text-amber-400',    dot: 'bg-amber-400'   },
    confirmed: { label: 'Confirmed', bg: 'bg-emerald-50 dark:bg-emerald-900/20', text: 'text-emerald-700 dark:text-emerald-400', dot: 'bg-emerald-500' },
    cancelled: { label: 'Cancelled', bg: 'bg-red-50 dark:bg-red-900/20',        text: 'text-red-600 dark:text-red-400',         dot: 'bg-red-400'     },
    completed: { label: 'Completed', bg: 'bg-sky-50 dark:bg-sky-900/20',        text: 'text-sky-600 dark:text-sky-400',         dot: 'bg-sky-500'     },
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <template #header>
            <div class="flex w-full items-center justify-between gap-4">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white">{{ greeting }}, {{ adminName }} 👋</h1>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ today }}</p>
                </div>
                <Link
                    href="/admin/doctors/create"
                    class="hidden shrink-0 items-center gap-2 rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 active:scale-95 sm:inline-flex"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Doctor
                </Link>
            </div>
        </template>

        <!-- Pending doctors alert -->
        <div
            v-if="stats.pending_doctors > 0"
            class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50/60 px-5 py-4 dark:border-amber-800/40 dark:from-amber-900/10 dark:to-orange-900/5"
        >
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                        {{ stats.pending_doctors }} doctor{{ stats.pending_doctors > 1 ? 's' : '' }} awaiting approval
                    </p>
                    <p class="text-xs text-amber-600 dark:text-amber-500">Review and approve their applications to get them listed on the platform.</p>
                </div>
            </div>
            <Link
                href="/admin/doctors?status=pending"
                class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-600 active:scale-95"
            >
                Review now →
            </Link>
        </div>

        <!-- ── KPI Cards (4 cols) ── -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <!-- Total Doctors -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-50/60 to-transparent dark:from-orange-900/10 dark:to-transparent"></div>
                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100 dark:bg-orange-900/50">
                            <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-orange-50 px-2 py-0.5 text-xs font-medium text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">All time</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold tabular-nums text-gray-900 dark:text-white">{{ stats.total_doctors }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-500 dark:text-gray-400">Total Doctors</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>{{ stats.approved_doctors }} active
                        </span>
                        <span v-if="stats.pending_doctors > 0" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>{{ stats.pending_doctors }} pending
                        </span>
                        <span v-if="stats.suspended_doctors > 0" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600 dark:bg-red-900/20 dark:text-red-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>{{ stats.suspended_doctors }} suspended
                        </span>
                    </div>
                </div>
                <Link href="/admin/doctors" class="absolute inset-0" aria-label="View doctors" />
            </div>

            <!-- Total Appointments -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-sky-50/60 to-transparent dark:from-sky-900/10 dark:to-transparent"></div>
                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/50">
                            <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-600 dark:bg-sky-900/20 dark:text-sky-400">All time</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold tabular-nums text-gray-900 dark:text-white">{{ stats.total_appointments }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-500 dark:text-gray-400">Total Appointments</p>
                    <div class="mt-3 flex items-center gap-2">
                        <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-full rounded-full bg-sky-500 transition-all duration-700" :style="{ width: completionRate + '%' }"></div>
                        </div>
                        <span class="shrink-0 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ completionRate }}% done</span>
                    </div>
                </div>
                <Link href="/admin/appointments" class="absolute inset-0" aria-label="View appointments" />
            </div>

            <!-- Today's Appointments -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-emerald-50/60 to-transparent dark:from-emerald-900/10 dark:to-transparent"></div>
                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/50">
                            <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">Today</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold tabular-nums text-gray-900 dark:text-white">{{ stats.today_appointments }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-500 dark:text-gray-400">Today's Appointments</p>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ stats.today_confirmed }}</span> confirmed for today
                    </p>
                </div>
                <Link :href="`/admin/appointments?date=${new Date().toISOString().slice(0, 10)}`" class="absolute inset-0" aria-label="Today's appointments" />
            </div>

            <!-- Revenue -->
            <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-teal-50/60 to-transparent dark:from-teal-900/10 dark:to-transparent"></div>
                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 dark:bg-teal-900/50">
                            <svg class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs font-medium text-teal-600 dark:bg-teal-900/20 dark:text-teal-400">Completed</span>
                    </div>
                    <p class="mt-4 text-3xl font-bold tabular-nums text-gray-900 dark:text-white">{{ revenueFormatted }}</p>
                    <p class="mt-0.5 text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                        From <span class="font-semibold text-gray-600 dark:text-gray-300">{{ stats.completed_appointments }}</span> completed appts
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Analytics: Status Distribution + 7-day Trend ── -->
        <div class="mt-5 grid gap-5 lg:grid-cols-2">

            <!-- Appointment Status Breakdown -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Appointment Breakdown</h2>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Status distribution across all appointments</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">{{ stats.total_appointments }} total</span>
                </div>
                <!-- Segmented progress bar -->
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-full bg-emerald-500 transition-all duration-700" :style="{ width: statusDist.completed.pct + '%' }"></div>
                    <div class="h-full bg-sky-400 transition-all duration-700"     :style="{ width: statusDist.confirmed.pct + '%' }"></div>
                    <div class="h-full bg-amber-400 transition-all duration-700"   :style="{ width: statusDist.pending.pct   + '%' }"></div>
                    <div class="h-full bg-red-400 transition-all duration-700"     :style="{ width: statusDist.cancelled.pct + '%' }"></div>
                </div>
                <!-- Legend grid -->
                <div class="mt-4 grid grid-cols-2 gap-2.5">
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Completed</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ statusDist.completed.count }}</span>
                            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">({{ statusDist.completed.pct }}%)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-sky-400"></span>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Confirmed</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ statusDist.confirmed.count }}</span>
                            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">({{ statusDist.confirmed.pct }}%)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></span>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Pending</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ statusDist.pending.count }}</span>
                            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">({{ statusDist.pending.pct }}%)</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-red-400"></span>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Cancelled</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-100">{{ statusDist.cancelled.count }}</span>
                            <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">({{ statusDist.cancelled.pct }}%)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 7-day Trend Sparkline -->
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Appointment Trend</h2>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Appointments scheduled over the last 7 days</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2.5 py-1 text-xs font-semibold text-orange-600 dark:bg-orange-900/20 dark:text-orange-400">
                        7-day window
                    </span>
                </div>
                <!-- SVG chart -->
                <svg
                    :viewBox="`0 0 ${sparkline.W} ${sparkline.H}`"
                    class="w-full"
                    style="height: 90px"
                    preserveAspectRatio="none"
                    overflow="visible"
                >
                    <defs>
                        <linearGradient id="sparkGrad" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#ea580c" stop-opacity="0.22" />
                            <stop offset="100%" stop-color="#ea580c" stop-opacity="0" />
                        </linearGradient>
                    </defs>
                    <!-- Horizontal grid lines -->
                    <line :x1="0" :y1="sparkline.H * 0.25" :x2="sparkline.W" :y2="sparkline.H * 0.25" stroke="#e5e7eb" stroke-width="0.8" class="dark:stroke-gray-800" />
                    <line :x1="0" :y1="sparkline.H * 0.5"  :x2="sparkline.W" :y2="sparkline.H * 0.5"  stroke="#e5e7eb" stroke-width="0.8" class="dark:stroke-gray-800" />
                    <line :x1="0" :y1="sparkline.H * 0.75" :x2="sparkline.W" :y2="sparkline.H * 0.75" stroke="#e5e7eb" stroke-width="0.8" class="dark:stroke-gray-800" />
                    <!-- Area fill -->
                    <path :d="sparkline.area" fill="url(#sparkGrad)" />
                    <!-- Line -->
                    <polyline :points="sparkline.polyline" fill="none" stroke="#ea580c" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" />
                    <!-- Data point dots -->
                    <circle
                        v-for="(pt, i) in sparkline.pts"
                        :key="i"
                        :cx="pt.x"
                        :cy="pt.y"
                        r="3.5"
                        fill="#ea580c"
                        stroke="white"
                        stroke-width="2"
                        class="dark:stroke-gray-900"
                    />
                </svg>

                <!-- X-axis: day labels -->
                <div class="mt-2 flex">
                    <span
                        v-for="day in week_trend"
                        :key="day.date"
                        class="flex-1 text-center text-[11px] font-semibold text-gray-400 dark:text-gray-600"
                    >{{ day.label }}</span>
                </div>
                <!-- X-axis: counts -->
                <div class="mt-0.5 flex">
                    <span
                        v-for="day in week_trend"
                        :key="day.date + '-n'"
                        class="flex-1 text-center text-[11px] font-bold text-gray-700 dark:text-gray-300"
                    >{{ day.count }}</span>
                </div>
            </div>
        </div>

        <!-- ── Bottom: Top Doctors + Recent Appointments ── -->
        <div class="mt-5 grid gap-5 lg:grid-cols-2">

            <!-- Top Doctors -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Top Doctors</h2>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Ranked by appointment count</p>
                    </div>
                    <Link href="/admin/doctors" class="text-xs font-semibold text-orange-600 transition hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                        View all →
                    </Link>
                </div>

                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    <div
                        v-for="(doctor, i) in top_doctors"
                        :key="doctor.id"
                        class="flex items-center gap-4 px-6 py-3.5 transition-colors hover:bg-gray-50/70 dark:hover:bg-gray-800/40"
                    >
                        <!-- Rank badge -->
                        <span
                            class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                            :class="i === 0
                                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'
                                : i === 1
                                    ? 'bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                        >{{ i + 1 }}</span>
                        <!-- Avatar -->
                        <div class="relative h-9 w-9 shrink-0">
                            <div class="absolute inset-0 flex items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-indigo-600 text-xs font-bold text-white">
                                {{ doctor.name?.charAt(0)?.toUpperCase() }}
                            </div>
                            <img
                                v-if="doctor.avatar_url"
                                :src="doctor.avatar_url"
                                :alt="doctor.name"
                                class="absolute inset-0 h-9 w-9 rounded-full object-cover ring-2 ring-white dark:ring-gray-900"
                                @error="($event.target as HTMLImageElement).style.display = 'none'"
                            />
                        </div>
                        <!-- Name + specialization -->
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">Dr. {{ doctor.name }}</p>
                            <p class="truncate text-xs text-gray-400 dark:text-gray-500">
                                {{ Array.isArray(doctor.specialization) ? doctor.specialization[0] : doctor.specialization }}
                            </p>
                        </div>
                        <!-- Appointment count -->
                        <span class="inline-flex h-7 min-w-[1.75rem] shrink-0 items-center justify-center rounded-lg bg-orange-50 px-2 text-xs font-bold text-orange-700 dark:bg-orange-900/20 dark:text-orange-300">
                            {{ doctor.appointments_count }}
                        </span>
                    </div>

                    <div v-if="!top_doctors.length" class="flex flex-col items-center gap-2 px-6 py-14 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                            <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No doctors yet</p>
                    </div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Recent Appointments</h2>
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Latest activity across the platform</p>
                    </div>
                    <Link href="/admin/appointments" class="text-xs font-semibold text-orange-600 transition hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                        View all →
                    </Link>
                </div>

                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    <div
                        v-for="apt in recent_appointments"
                        :key="apt.id"
                        class="flex items-center gap-3 px-6 py-3.5 transition-colors hover:bg-gray-50/70 dark:hover:bg-gray-800/40"
                    >
                        <!-- Doctor initial avatar -->
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-orange-500 to-indigo-600 text-xs font-bold text-white">
                            {{ apt.doctor?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                        </div>
                        <!-- Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-baseline gap-1.5">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ apt.patient_name }}</p>
                                <span class="hidden shrink-0 rounded bg-gray-100 px-1.5 py-0.5 font-mono text-[10px] text-gray-500 dark:bg-gray-800 dark:text-gray-400 sm:inline">{{ apt.reference }}</span>
                            </div>
                            <p class="truncate text-xs text-gray-400 dark:text-gray-500">
                                Dr. {{ apt.doctor?.name }} · {{ formatDate(apt.appointment_date) }}, {{ formatTime(apt.appointment_time) }}
                            </p>
                        </div>
                        <!-- Status badge -->
                        <span
                            class="shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="[statusConfig[apt.status]?.bg, statusConfig[apt.status]?.text]"
                        >
                            <span class="h-1.5 w-1.5 rounded-full" :class="statusConfig[apt.status]?.dot"></span>
                            {{ statusConfig[apt.status]?.label }}
                        </span>
                    </div>

                    <div v-if="!recent_appointments.length" class="flex flex-col items-center gap-2 px-6 py-14 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No appointments yet</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
