<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useDark } from '@vueuse/core';
import VueApexCharts from 'vue3-apexcharts';
import AdminLayout from '@/layouts/AdminLayout.vue';

const isDark = useDark();

// ── Types ────────────────────────────────────────────────────────────
interface TopDoctor     { id: number; name: string; specialization: string[] | null; appointments_count: number }
interface SpecItem      { spec: string; total: number }
interface MonthlyStatus { month: string; pending: number; confirmed: number; completed: number; cancelled: number }
interface GrowthPoint   { month: string; count: number }
interface RevenuePoint  { month: string; revenue: number }
interface TrendPoint    { date: string; count: number }

const props = defineProps<{
    filters: { period: string; date_from?: string; date_to?: string };
    stats: { total_appointments: number; total_revenue: number; new_doctors: number; completion_rate: number };
    appointment_trend: TrendPoint[];
    status_breakdown: Record<string, number>;
    type_breakdown: Record<string, number>;
    doctor_growth: GrowthPoint[];
    sub_revenue: RevenuePoint[];
    monthly_status: MonthlyStatus[];
    top_doctors: TopDoctor[];
    specialization_data: SpecItem[];
    doctor_status_breakdown: Record<string, number>;
}>();

// ── Period filter ─────────────────────────────────────────────────────
const period   = ref(props.filters.period);
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo   = ref(props.filters.date_to   ?? '');

const periods = [
    { key: '7d',     label: '7D'    },
    { key: '30d',    label: '30D'   },
    { key: '90d',    label: '3M'    },
    { key: '1y',     label: '1Y'    },
    { key: 'custom', label: 'Custom'},
] as const;

function applyFilter() {
    const params: Record<string, string> = { period: period.value };
    if (period.value === 'custom') {
        if (dateFrom.value) params.date_from = dateFrom.value;
        if (dateTo.value)   params.date_to   = dateTo.value;
    }
    router.get('/admin/analytics', params, { preserveScroll: true });
}

watch(period, (v) => { if (v !== 'custom') applyFilter(); });

// ── ApexCharts helpers ────────────────────────────────────────────────
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

// ── Platform Appointment Trend ────────────────────────────────────────
const trendSeries = computed(() => [{ name: 'Appointments', data: props.appointment_trend.map(d => d.count) }]);
const trendOptions = computed(() => ({
    ...base(),
    chart:      { ...base().chart, type: 'area' as const },
    colors:     ['#3b82f6'],
    stroke:     { curve: 'smooth' as const, width: 2 },
    fill:       { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.01 } },
    xaxis:      { categories: props.appointment_trend.map(d => d.date), labels: axisL(), axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:      { labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    dataLabels: { enabled: false },
    markers:    { size: props.appointment_trend.length < 40 ? 4 : 0, strokeWidth: 0 },
}));

// ── Monthly Status Stacked Bar ────────────────────────────────────────
const monthlyStatusSeries = computed(() => [
    { name: 'Pending',   data: props.monthly_status.map(m => m.pending)   },
    { name: 'Confirmed', data: props.monthly_status.map(m => m.confirmed) },
    { name: 'Completed', data: props.monthly_status.map(m => m.completed) },
    { name: 'Cancelled', data: props.monthly_status.map(m => m.cancelled) },
]);
const monthlyStatusOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'bar' as const, stacked: true },
    colors:      ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
    plotOptions: { bar: { borderRadius: 2, columnWidth: '60%' } },
    xaxis:       { categories: props.monthly_status.map(m => m.month), labels: { ...axisL(), rotate: -45 }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:       { labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    dataLabels:  { enabled: false },
    legend:      { position: 'top' as const, labels: legendL() },
}));

// ── Status Donut ──────────────────────────────────────────────────────
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

// ── Type Donut ────────────────────────────────────────────────────────
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

// ── Doctor Growth Bar ─────────────────────────────────────────────────
const growthSeries = computed(() => [{ name: 'New Doctors', data: props.doctor_growth.map(g => g.count) }]);
const growthOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'bar' as const },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    colors:      ['#10b981'],
    xaxis:       { categories: props.doctor_growth.map(g => g.month), labels: { ...axisL(), rotate: -45 }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:       { labels: { ...axisL(), formatter: (v: number) => String(Math.round(v)) }, min: 0 },
    dataLabels:  { enabled: false },
}));

// ── Subscription Revenue Area ─────────────────────────────────────────
const subRevSeries = computed(() => [{ name: 'Revenue (₱)', data: props.sub_revenue.map(r => r.revenue) }]);
const subRevOptions = computed(() => ({
    ...base(),
    chart:      { ...base().chart, type: 'area' as const },
    colors:     ['#f97316'],
    stroke:     { curve: 'smooth' as const, width: 2 },
    fill:       { type: 'gradient', gradient: { opacityFrom: 0.28, opacityTo: 0.01 } },
    xaxis:      { categories: props.sub_revenue.map(r => r.month), labels: { ...axisL(), rotate: -45 }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis:      { labels: { ...axisL(), formatter: (v: number) => '₱' + Math.round(v).toLocaleString() }, min: 0 },
    dataLabels: { enabled: false },
    markers:    { size: 4, strokeWidth: 0 },
}));

// ── Top Doctors Horizontal Bar ────────────────────────────────────────
const topDocSeries = computed(() => [{ name: 'Appointments', data: props.top_doctors.map(d => d.appointments_count) }]);
const topDocOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'bar' as const },
    plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '65%' } },
    colors:      ['#3b82f6'],
    xaxis:       { categories: props.top_doctors.map(d => d.name), labels: axisL(), axisBorder: { show: false } },
    yaxis:       { labels: { ...axisL(), maxWidth: 200 } },
    dataLabels:  { enabled: false },
}));

// ── Specialization Donut ──────────────────────────────────────────────
const specPalette = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#0ea5e9','#f97316','#14b8a6','#ec4899','#6b7280'];
const specSeries  = computed(() => props.specialization_data.map(s => Number(s.total)));
const specOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'donut' as const },
    labels:      props.specialization_data.map(s => s.spec),
    colors:      specPalette,
    legend:      { position: 'bottom' as const, labels: legendL() },
    dataLabels:  { enabled: false },
    plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total', color: isDark.value ? '#d1d5db' : '#374151' } } } } },
}));

// ── Doctor Status Donut ───────────────────────────────────────────────
const DOC_STATUS_COLORS: Record<string, string> = { approved: '#10b981', pending: '#f59e0b', suspended: '#ef4444' };
const docStatusKeys    = computed(() => Object.keys(props.doctor_status_breakdown));
const docStatusSeries  = computed(() => docStatusKeys.value.map(k => Number(props.doctor_status_breakdown[k])));
const docStatusOptions = computed(() => ({
    ...base(),
    chart:       { ...base().chart, type: 'donut' as const },
    labels:      docStatusKeys.value.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
    colors:      docStatusKeys.value.map(k => DOC_STATUS_COLORS[k] ?? '#94a3b8'),
    legend:      { position: 'bottom' as const, labels: legendL() },
    dataLabels:  { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
    plotOptions: { pie: { donut: { size: '58%', labels: { show: true, total: { show: true, label: 'Doctors', color: isDark.value ? '#d1d5db' : '#374151' } } } } },
}));

// ── Helpers ───────────────────────────────────────────────────────────
function fmtCurrency(v: number) {
    return '₱' + v.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function exportHref() {
    const p = new URLSearchParams({ period: period.value });
    if (period.value === 'custom') {
        if (dateFrom.value) p.set('date_from', dateFrom.value);
        if (dateTo.value)   p.set('date_to',   dateTo.value);
    }
    return '/admin/analytics/export?' + p.toString();
}
</script>

<template>
    <Head title="Analytics" />
    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Platform Analytics</h1>
            <p class="mt-0.5 text-sm text-gray-400 dark:text-gray-500">Appointments, doctors, revenue and growth insights</p>
        </div>

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
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Total Appointments</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_appointments.toLocaleString() }}</p>
                <p class="mt-1 text-xs text-gray-400">in selected period</p>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm dark:border-orange-900/30 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Subscription Revenue</p>
                <p class="mt-1 text-2xl font-bold text-orange-600 dark:text-orange-400">{{ fmtCurrency(stats.total_revenue) }}</p>
                <p class="mt-1 text-xs text-gray-400">from Pro subscriptions</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">New Doctors</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.new_doctors.toLocaleString() }}</p>
                <p class="mt-1 text-xs text-gray-400">registered in period</p>
            </div>
            <div class="rounded-2xl border border-sky-100 bg-white p-5 shadow-sm dark:border-sky-900/30 dark:bg-gray-900">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">Completion Rate</p>
                <p class="mt-1 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ stats.completion_rate }}%</p>
                <p class="mt-1 text-xs text-gray-400">appointments completed</p>
            </div>
        </div>

        <!-- Platform Appointment Trend -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Platform Appointments Trend</p>
            <p class="mb-4 text-xs text-gray-400">All appointments across the platform for selected period</p>
            <VueApexCharts :key="themeKey + '-trend'" type="area" height="230" :options="trendOptions" :series="trendSeries" />
        </div>

        <!-- Monthly status stacked bar -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Monthly Appointment Status</p>
            <p class="mb-4 text-xs text-gray-400">Breakdown by status — last 12 months</p>
            <VueApexCharts :key="themeKey + '-mstatus'" type="bar" height="260" :options="monthlyStatusOptions" :series="monthlyStatusSeries" />
        </div>

        <!-- Status + Type -->
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Status Distribution</p>
                <p class="mb-3 text-xs text-gray-400">For selected period</p>
                <VueApexCharts v-if="statusSeries.length" :key="themeKey + '-status'"
                    type="donut" height="256" :options="statusOptions" :series="statusSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No data</div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointment Type</p>
                <p class="mb-3 text-xs text-gray-400">In-person vs online — selected period</p>
                <VueApexCharts v-if="typeSeries.length" :key="themeKey + '-type'"
                    type="donut" height="256" :options="typeOptions" :series="typeSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No data</div>
            </div>
        </div>

        <!-- Doctor Growth + Subscription Revenue -->
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Doctor Growth</p>
                <p class="mb-4 text-xs text-gray-400">New doctor registrations — last 12 months</p>
                <VueApexCharts :key="themeKey + '-growth'" type="bar" height="230" :options="growthOptions" :series="growthSeries" />
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Subscription Revenue</p>
                <p class="mb-4 text-xs text-gray-400">Pro subscription payments — last 12 months</p>
                <VueApexCharts :key="themeKey + '-subrev'" type="area" height="230" :options="subRevOptions" :series="subRevSeries" />
            </div>
        </div>

        <!-- Top Doctors -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Top Doctors by Appointments</p>
            <p class="mb-4 text-xs text-gray-400">Top 10 doctors in selected period</p>
            <VueApexCharts v-if="top_doctors.length" :key="themeKey + '-topdoc'"
                type="bar" height="280" :options="topDocOptions" :series="topDocSeries" />
            <div v-else class="flex h-40 items-center justify-center text-sm text-gray-400">No data</div>
        </div>

        <!-- Specialization + Doctor Status -->
        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Top Specializations</p>
                <p class="mb-3 text-xs text-gray-400">Distribution across all doctors</p>
                <VueApexCharts v-if="specSeries.length" :key="themeKey + '-spec'"
                    type="donut" height="280" :options="specOptions" :series="specSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No data</div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Doctor Account Status</p>
                <p class="mb-3 text-xs text-gray-400">Approved, pending, and suspended accounts</p>
                <VueApexCharts v-if="docStatusSeries.length" :key="themeKey + '-docstatus'"
                    type="donut" height="280" :options="docStatusOptions" :series="docStatusSeries" />
                <div v-else class="flex h-52 items-center justify-center text-sm text-gray-400">No data</div>
            </div>
        </div>

    </AdminLayout>
</template>
