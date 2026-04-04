<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useDark } from '@vueuse/core';
import VueApexCharts from 'vue3-apexcharts';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Doctor } from '@/types';

const isDark = useDark();

// ── Types ────────────────────────────────────────────────────────────
interface DiagItem    { title: string; total: number }
interface PeakHour    { hour: string; count: number }
interface InvCategory { category: string; items: number; total_stock: number }
interface MonthlyRow  { month: string; completed: number; revenue: number }
interface TrendPoint  { date: string; count: number }

const props = defineProps<{
    doctor: Doctor;
    filters: { period: string; date_from?: string; date_to?: string };
    stats: { total: number; completed: number; cancelled: number; revenue: number; unique_patients: number };
    appointment_trend: TrendPoint[];
    status_breakdown: Record<string, number>;
    type_breakdown: Record<string, number>;
    monthly_revenue: MonthlyRow[];
    top_diagnoses: DiagItem[];
    peak_hours: PeakHour[];
    inventory_by_category: InvCategory[];
}>();

// ── Period filter ─────────────────────────────────────────────────────
const period   = ref(props.filters.period);
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo   = ref(props.filters.date_to   ?? '');

const periods = [
    { key: '7d',     label: '7D'       },
    { key: '30d',    label: '30D'      },
    { key: '90d',    label: '3M'       },
    { key: '1y',     label: '1Y'       },
    { key: 'custom', label: 'Custom'   },
] as const;

function applyFilter() {
    const params: Record<string, string> = { period: period.value };
    if (period.value === 'custom') {
        if (dateFrom.value) params.date_from = dateFrom.value;
        if (dateTo.value)   params.date_to   = dateTo.value;
    }
    router.get('/doctor/analytics', params, { preserveScroll: true });
}

watch(period, (v) => { if (v !== 'custom') applyFilter(); });

// ── ApexCharts helpers ────────────────────────────────────────────────
// Key forces remount when theme changes
const themeKey = computed(() => isDark.value ? 'dark' : 'light');

function base() {
    return {
        chart:   { background: 'transparent', toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: true, speed: 400 } },
        theme:   { mode: (isDark.value ? 'dark' : 'light') as 'dark' | 'light' },
        tooltip: { theme: isDark.value ? 'dark' : 'light' },
        grid:    { borderColor: isDark.value ? '#374151' : '#f3f4f6', strokeDashArray: 3 },
    };
}
function axisL() { return { style: { colors: isDark.value ? '#9ca3af' : '#6b7280', fontSize: '11px' } }; }
function legendL() { return { colors: isDark.value ? '#d1d5db' : '#374151' }; }

// ── Appointments trend (area) ─────────────────────────────────────────
const trendSeries = computed(() => [{ name: 'Appointments', data: props.appointment_trend.map(d => d.count) }]);
const trendOptions = computed(() => ({
    ...base(),
    chart:      { ...base().chart, type: 'area' as const },
    colors:     ['#f97316'],
    stroke:     { curve: 'smooth' as const, width: 2 },
    fill:       { type: 'gradient', gradient: { opacityFrom: 0.28, opacityTo: 0.01 } },
    xaxis:      { categories: props.appointment_trend.map(d => d.date), labels: axisL(), axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:      { labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    dataLabels: { enabled: false },
    markers:    { size: props.appointment_trend.length < 40 ? 4 : 0, strokeWidth: 0 },
}));

// ── Status donut ──────────────────────────────────────────────────────
const STATUS_COLORS: Record<string, string> = { pending: '#f59e0b', confirmed: '#3b82f6', completed: '#10b981', cancelled: '#ef4444' };
const statusKeys    = computed(() => Object.keys(props.status_breakdown));
const statusSeries  = computed(() => statusKeys.value.map(k => Number(props.status_breakdown[k])));
const statusOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'donut' as const },
    labels:      statusKeys.value.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
    colors:      statusKeys.value.map(k => STATUS_COLORS[k] ?? '#94a3b8'),
    legend:      { position: 'bottom' as const, labels: legendL() },
    dataLabels:  { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
    plotOptions: { pie: { donut: { size: '58%', labels: { show: true, total: { show: true, label: 'Total', color: isDark.value ? '#d1d5db' : '#374151' } } } } },
}));

// ── Type donut ────────────────────────────────────────────────────────
const typeKeys    = computed(() => Object.keys(props.type_breakdown));
const typeSeries  = computed(() => typeKeys.value.map(k => Number(props.type_breakdown[k])));
const typeOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'donut' as const },
    labels:      typeKeys.value.map(t => t === 'in_person' ? 'In-Person' : 'Online'),
    colors:      ['#f97316', '#0ea5e9'],
    legend:      { position: 'bottom' as const, labels: legendL() },
    dataLabels:  { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
    plotOptions: { pie: { donut: { size: '58%', labels: { show: true, total: { show: true, label: 'Total', color: isDark.value ? '#d1d5db' : '#374151' } } } } },
}));

// ── Monthly revenue (area) ────────────────────────────────────────────
const revenueSeries = computed(() => [
    { name: 'Revenue (₱)', data: props.monthly_revenue.map(r => r.revenue) },
    { name: 'Completed',   data: props.monthly_revenue.map(r => r.completed) },
]);
const revenueOptions = computed(() => ({
    ...base(),
    chart:      { ...base().chart, type: 'area' as const },
    colors:     ['#10b981', '#3b82f6'],
    stroke:     { curve: 'smooth' as const, width: 2 },
    fill:       { type: 'gradient', gradient: { opacityFrom: 0.22, opacityTo: 0.01 } },
    xaxis:      { categories: props.monthly_revenue.map(r => r.month), labels: axisL(), axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: [
        { seriesName: 'Revenue (₱)', labels: { ...axisL(), formatter: (v: number) => '₱' + Math.round(v).toLocaleString() }, min: 0 },
        { seriesName: 'Completed', opposite: true, labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    ],
    dataLabels: { enabled: false },
    legend:     { labels: legendL() },
}));

// ── Top diagnoses (horizontal bar) ───────────────────────────────────
const diagSeries = computed(() => [{ name: 'Count', data: props.top_diagnoses.map(d => Number(d.total)) }]);
const diagOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'bar' as const },
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%' } },
    colors:      ['#8b5cf6'],
    xaxis:       { categories: props.top_diagnoses.map(d => d.title.length > 28 ? d.title.slice(0, 28) + '…' : d.title), labels: axisL(), axisBorder: { show: false } },
    yaxis:       { labels: { ...axisL(), maxWidth: 200 } },
    dataLabels:  { enabled: false },
    tooltip:     { theme: isDark.value ? 'dark' : 'light', y: { title: { formatter: () => 'Cases: ' } } },
}));

// ── Peak hours (column bar) ───────────────────────────────────────────
const peakSeries = computed(() => [{ name: 'Appointments', data: props.peak_hours.map(h => h.count) }]);
const peakOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'bar' as const },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    colors:      ['#f97316'],
    xaxis:       { categories: props.peak_hours.map(h => h.hour), labels: { ...axisL(), rotate: -45 }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:       { labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    dataLabels:  { enabled: false },
}));

// ── Inventory by category (grouped bar) ─────────────────────────────
const invSeries = computed(() => [
    { name: 'Total Stock', data: props.inventory_by_category.map(i => Number(i.total_stock)) },
    { name: 'Item Types',  data: props.inventory_by_category.map(i => Number(i.items))       },
]);
const invOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'bar' as const },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    colors:      ['#0ea5e9', '#f97316'],
    xaxis:       { categories: props.inventory_by_category.map(i => i.category.charAt(0).toUpperCase() + i.category.slice(1)), labels: axisL(), axisBorder: { show: false } },
    yaxis:       { labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    dataLabels:  { enabled: false },
    legend:      { labels: legendL() },
}));

// ── Helpers ───────────────────────────────────────────────────────────
function fmt(v: number) {
    return '₱' + v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function completionRate() {
    return props.stats.total > 0 ? ((props.stats.completed / props.stats.total) * 100).toFixed(1) : '0.0';
}
function exportHref() {
    const p = new URLSearchParams({ period: period.value });
    if (period.value === 'custom') {
        if (dateFrom.value) p.set('date_from', dateFrom.value);
        if (dateTo.value)   p.set('date_to',   dateTo.value);
    }
    return '/doctor/analytics/export?' + p.toString();
}
</script>

<template>
    <Head title="Analytics" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Analytics & Reports</h1>
            <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Appointments, revenue, diagnoses and inventory insights</p>
        </template>

        <!-- Period filter + export -->
        <div class="mb-6 flex flex-wrap items-center gap-3 justify-between">
            <div class="flex rounded-xl border border-gray-200 bg-white p-1 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <button
                    v-for="p in periods"
                    :key="p.key"
                    @click="period = p.key"
                    class="rounded-lg px-4 py-1.5 text-sm font-medium transition-colors"
                    :class="period === p.key
                        ? 'bg-orange-600 text-white shadow-sm'
                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100'"
                >
                    {{ p.label }}
                </button>
            </div>
            <template v-if="period === 'custom'">
                <input v-model="dateFrom" type="date"
                    class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                <span class="text-sm text-gray-400">—</span>
                <input v-model="dateTo" type="date"
                    class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                <button @click="applyFilter"
                    class="rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-700">
                    Apply
                </button>
            </template>
            <a
                :href="exportHref()"
                class="ml-auto flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export CSV
            </a>
        </div>

        <!-- KPI cards -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Total</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total.toLocaleString() }}</p>
                <p class="mt-1 text-xs text-gray-400">appointments</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/40 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Completed</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.completed.toLocaleString() }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ completionRate() }}% rate</p>
            </div>
            <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm dark:border-red-900/30 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Cancelled</p>
                <p class="mt-1 text-2xl font-bold text-red-500 dark:text-red-400">{{ stats.cancelled.toLocaleString() }}</p>
                <p class="mt-1 text-xs text-gray-400">appointments</p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm dark:border-orange-900/30 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Est. Revenue</p>
                <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ fmt(stats.revenue) }}</p>
                <p class="mt-1 text-xs text-gray-400">consultation fees</p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm dark:border-sky-900/30 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Unique Patients</p>
                <p class="mt-1 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ stats.unique_patients.toLocaleString() }}</p>
                <p class="mt-1 text-xs text-gray-400">distinct emails</p>
            </div>
        </div>

        <!-- Appointments Over Time -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointments Over Time</p>
            <p class="mb-4 text-xs text-gray-400">Daily or monthly view depending on selected period</p>
            <VueApexCharts :key="themeKey + '-trend'" type="area" height="230" :options="trendOptions" :series="trendSeries" />
        </div>

        <!-- Status distribution + Type distribution -->
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointment Status</p>
                <p class="mb-3 text-xs text-gray-400">Distribution for selected period</p>
                <VueApexCharts v-if="statusSeries.length" :key="themeKey + '-status'"
                    type="donut" height="256" :options="statusOptions" :series="statusSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No data</div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointment Type</p>
                <p class="mb-3 text-xs text-gray-400">In-person vs online for selected period</p>
                <VueApexCharts v-if="typeSeries.length" :key="themeKey + '-type'"
                    type="donut" height="256" :options="typeOptions" :series="typeSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No data</div>
            </div>
        </div>

        <!-- Monthly Revenue & Completions (last 12 months) -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Revenue & Completions</p>
            <p class="mb-4 text-xs text-gray-400">Last 12 months — estimated from consultation fee</p>
            <VueApexCharts :key="themeKey + '-revenue'" type="area" height="230" :options="revenueOptions" :series="revenueSeries" />
        </div>

        <!-- Top Diagnoses + Peak Hours -->
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Top Diagnoses</p>
                <p class="mb-3 text-xs text-gray-400">Most frequent diagnoses in selected period</p>
                <VueApexCharts v-if="top_diagnoses.length" :key="themeKey + '-diag'"
                    type="bar" height="270" :options="diagOptions" :series="diagSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No diagnoses recorded</div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Peak Appointment Hours</p>
                <p class="mb-3 text-xs text-gray-400">Busiest times of day — 06:00 to 20:00</p>
                <VueApexCharts :key="themeKey + '-peak'" type="bar" height="270" :options="peakOptions" :series="peakSeries" />
            </div>
        </div>

        <!-- Inventory by Category -->
        <div v-if="inventory_by_category.length > 0" class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Inventory by Category</p>
            <p class="mb-4 text-xs text-gray-400">Total stock units and distinct item types per category</p>
            <VueApexCharts :key="themeKey + '-inv'" type="bar" height="230" :options="invOptions" :series="invSeries" />
        </div>

    </DoctorLayout>
</template>
