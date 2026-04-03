<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Causer {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface LogEntry {
    id: number;
    description: string;
    ip_address: string | null;
    user_agent: string | null;
    properties: {
        method: string;
        url: string;
        path: string;
        status: number;
        device: string;
        browser: string;
        os: string;
        is_bot: boolean;
        robot: string | null;
        referrer: string | null;
    };
    causer: Causer | null;
    created_at: string;
}

interface PaginatedLogs {
    data: LogEntry[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    logs: PaginatedLogs;
    stats: {
        total: number;
        today: number;
        unique_ips: number;
        guests: number;
        users: number;
    };
    filters: {
        search?: string;
        method_filter?: string;
        causer_type?: string;
        device?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const search      = ref(props.filters.search ?? '');
const methodFilter = ref(props.filters.method_filter ?? '');
const causerType  = ref(props.filters.causer_type ?? '');
const device      = ref(props.filters.device ?? '');
const dateFrom    = ref(props.filters.date_from ?? '');
const dateTo      = ref(props.filters.date_to ?? '');

const expandedId  = ref<number | null>(null);

let debounce: ReturnType<typeof setTimeout>;

watch([search, methodFilter, causerType, device, dateFrom, dateTo], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/admin/system-logs', {
            search:        search.value        || undefined,
            method_filter: methodFilter.value  || undefined,
            causer_type:   causerType.value    || undefined,
            device:        device.value        || undefined,
            date_from:     dateFrom.value      || undefined,
            date_to:       dateTo.value        || undefined,
        }, { preserveScroll: true, replace: true });
    }, 300);
});

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('en-PH', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit',
    });
}

function toggleRow(id: number) {
    expandedId.value = expandedId.value === id ? null : id;
}

const methodColors: Record<string, string> = {
    GET:    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    POST:   'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    PUT:    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    PATCH:  'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
    DELETE: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
};

function statusColor(code: number): string {
    if (code >= 500) return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300';
    if (code >= 400) return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    if (code >= 300) return 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300';
    return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300';
}

function deviceIcon(d: string): string {
    if (d === 'Mobile')  return '📱';
    if (d === 'Tablet')  return '🖱️';
    if (d === 'Desktop') return '🖥️';
    return '🤖';
}
</script>

<template>
    <Head title="System Logs" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">System Logs</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">Detailed activity & request audit trail with IP and device info</p>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Stats row -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Total Requests</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total.toLocaleString() }}</p>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-orange-50 p-4 shadow-sm dark:border-orange-900/40 dark:bg-orange-900/10">
                    <p class="text-xs font-medium text-orange-500">Today</p>
                    <p class="mt-1 text-2xl font-bold text-orange-700 dark:text-orange-300">{{ stats.today.toLocaleString() }}</p>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-4 shadow-sm dark:border-blue-900/40 dark:bg-blue-900/10">
                    <p class="text-xs font-medium text-blue-500">Unique IPs</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-300">{{ stats.unique_ips.toLocaleString() }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Authenticated</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.users.toLocaleString() }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Guests</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.guests.toLocaleString() }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3">
                <!-- Search — full width on mobile -->
                <div class="relative w-full sm:w-56">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search path, IP, user…"
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3.5 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"
                    />
                </div>

                <!-- Selects + date range — scrollable row on mobile -->
                <div class="flex items-center gap-2 overflow-x-auto pb-0.5 sm:flex-wrap sm:overflow-visible sm:pb-0">
                    <select
                        v-model="methodFilter"
                        class="shrink-0 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">All methods</option>
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                    <select
                        v-model="causerType"
                        class="shrink-0 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">All visitors</option>
                        <option value="user">Authenticated</option>
                        <option value="guest">Guest</option>
                    </select>
                    <select
                        v-model="device"
                        class="shrink-0 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                    >
                        <option value="">All devices</option>
                        <option value="Desktop">Desktop</option>
                        <option value="Mobile">Mobile</option>
                        <option value="Tablet">Tablet</option>
                        <option value="Robot">Bot / Crawler</option>
                    </select>
                    <div class="flex shrink-0 items-center gap-2">
                        <input type="date" v-model="dateFrom"
                            class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        />
                        <span class="text-xs text-gray-400">to</span>
                        <input type="date" v-model="dateTo"
                            class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                        />
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/60">
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400"></th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Method</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Path</th>
                                <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-400">Status</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">IP Address</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Device</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">User</th>
                                <th class="px-4 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr v-if="logs.data.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No system log entries found.
                                </td>
                            </tr>

                            <template v-for="log in logs.data" :key="log.id">
                                <!-- Main row -->
                                <tr
                                    class="cursor-pointer transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                    :class="expandedId === log.id ? 'bg-orange-50/40 dark:bg-orange-900/10' : ''"
                                    @click="toggleRow(log.id)"
                                >
                                    <!-- Expand toggle -->
                                    <td class="w-8 px-4 py-3.5">
                                        <svg
                                            class="h-3.5 w-3.5 text-gray-400 transition-transform duration-200"
                                            :class="expandedId === log.id ? 'rotate-90' : ''"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </td>

                                    <!-- Method -->
                                    <td class="px-4 py-3.5">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold"
                                            :class="methodColors[log.properties?.method] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'"
                                        >
                                            {{ log.properties?.method ?? '—' }}
                                        </span>
                                    </td>

                                    <!-- Path -->
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <span class="block truncate font-mono text-xs text-gray-700 dark:text-gray-300" :title="log.properties?.path">
                                            {{ log.properties?.path ?? '—' }}
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3.5 text-center">
                                        <span
                                            class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold"
                                            :class="statusColor(log.properties?.status ?? 200)"
                                        >
                                            {{ log.properties?.status ?? '—' }}
                                        </span>
                                    </td>

                                    <!-- IP -->
                                    <td class="px-4 py-3.5">
                                        <span class="font-mono text-xs text-gray-600 dark:text-gray-400">
                                            {{ log.ip_address ?? '—' }}
                                        </span>
                                    </td>

                                    <!-- Device -->
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-base leading-none">{{ deviceIcon(log.properties?.device ?? '') }}</span>
                                            <div>
                                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ log.properties?.device ?? 'Unknown' }}</p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ log.properties?.browser }} / {{ log.properties?.os }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- User -->
                                    <td class="px-4 py-3.5">
                                        <div v-if="log.causer">
                                            <p class="text-xs font-medium text-gray-800 dark:text-gray-200">{{ log.causer.name }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ log.causer.role }}</p>
                                        </div>
                                        <span v-else class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                            Guest
                                        </span>
                                    </td>

                                    <!-- Date -->
                                    <td class="px-4 py-3.5 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                </tr>

                                <!-- Expanded detail row -->
                                <tr v-if="expandedId === log.id" class="bg-orange-50/30 dark:bg-orange-900/5">
                                    <td colspan="8" class="px-6 py-4">
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                            <!-- Full URL -->
                                            <div class="rounded-xl border border-gray-100 bg-white p-3.5 dark:border-gray-800 dark:bg-gray-900">
                                                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">Full URL</p>
                                                <p class="break-all font-mono text-xs text-gray-700 dark:text-gray-300">{{ log.properties?.url ?? '—' }}</p>
                                            </div>

                                            <!-- User Agent -->
                                            <div class="rounded-xl border border-gray-100 bg-white p-3.5 dark:border-gray-800 dark:bg-gray-900">
                                                <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">User Agent</p>
                                                <p class="break-all text-xs text-gray-600 dark:text-gray-400">{{ log.user_agent ?? '—' }}</p>
                                            </div>

                                            <!-- Visitor details -->
                                            <div class="rounded-xl border border-gray-100 bg-white p-3.5 dark:border-gray-800 dark:bg-gray-900">
                                                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Visitor Details</p>
                                                <dl class="space-y-1">
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">IP</dt>
                                                        <dd class="font-mono text-xs text-gray-700 dark:text-gray-300">{{ log.ip_address ?? '—' }}</dd>
                                                    </div>
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Device</dt>
                                                        <dd class="text-xs text-gray-700 dark:text-gray-300">{{ log.properties?.device ?? '—' }}</dd>
                                                    </div>
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Browser</dt>
                                                        <dd class="text-xs text-gray-700 dark:text-gray-300">{{ log.properties?.browser ?? '—' }}</dd>
                                                    </div>
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">OS</dt>
                                                        <dd class="text-xs text-gray-700 dark:text-gray-300">{{ log.properties?.os ?? '—' }}</dd>
                                                    </div>
                                                    <div v-if="log.properties?.is_bot" class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Robot</dt>
                                                        <dd class="text-xs font-medium text-red-500">{{ log.properties?.robot ?? 'Bot' }}</dd>
                                                    </div>
                                                    <div v-if="log.properties?.referrer" class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Referrer</dt>
                                                        <dd class="max-w-[160px] truncate text-xs text-gray-700 dark:text-gray-300" :title="log.properties?.referrer">
                                                            {{ log.properties?.referrer }}
                                                        </dd>
                                                    </div>
                                                </dl>
                                            </div>

                                            <!-- Authenticated user details -->
                                            <div v-if="log.causer" class="rounded-xl border border-gray-100 bg-white p-3.5 dark:border-gray-800 dark:bg-gray-900">
                                                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400">Authenticated User</p>
                                                <dl class="space-y-1">
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Name</dt>
                                                        <dd class="text-xs text-gray-700 dark:text-gray-300">{{ log.causer.name }}</dd>
                                                    </div>
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Email</dt>
                                                        <dd class="text-xs text-gray-700 dark:text-gray-300">{{ log.causer.email }}</dd>
                                                    </div>
                                                    <div class="flex justify-between gap-2">
                                                        <dt class="text-xs text-gray-400">Role</dt>
                                                        <dd class="text-xs font-medium capitalize text-orange-600 dark:text-orange-400">{{ log.causer.role }}</dd>
                                                    </div>
                                                </dl>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3 dark:border-gray-800">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Showing {{ logs.from }}–{{ logs.to }} of {{ logs.total.toLocaleString() }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in logs.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                                :class="link.active
                                    ? 'bg-orange-600 text-white'
                                    : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'"
                                v-html="link.label"
                                preserve-scroll
                            />
                            <span
                                v-else
                                class="rounded-lg px-3 py-1.5 text-xs font-medium text-gray-300 dark:text-gray-700"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
