<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';

interface Doctor {
    id: number;
    name: string;
    email: string;
    slug: string;
}

interface PaymentLog {
    id: number;
    doctor_id: number;
    doctor?: Doctor;
    checkout_session_id: string;
    billing_period: 'monthly' | 'yearly';
    amount: number; // centavos
    status: string;
    source: string;
    pro_expires_at: string | null;
    created_at: string;
}

interface PaginatedLogs {
    data: PaymentLog[];
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
        total_revenue: number; // centavos
        this_month: number;
        monthly_subs: number;
        yearly_subs: number;
    };
    filters: {
        search?: string;
        billing_period?: string;
        source?: string;
    };
}>();

const search         = ref(props.filters.search ?? '');
const billingPeriod  = ref(props.filters.billing_period ?? '');
const source         = ref(props.filters.source ?? '');

let debounce: ReturnType<typeof setTimeout>;

watch([search, billingPeriod, source], () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        router.get('/admin/payment-logs', {
            search: search.value || undefined,
            billing_period: billingPeriod.value || undefined,
            source: source.value || undefined,
        }, { preserveScroll: true, replace: true });
    }, 250);
});

function formatPesos(centavos: number): string {
    return '₱' + (centavos / 100).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Payment Logs" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Logs</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">All PayMongo Pro subscription transactions</p>
            </div>
        </template>

        <div class="space-y-6">

            <!-- Stats row -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Total Transactions</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-violet-100 bg-violet-50 p-4 shadow-sm dark:border-violet-900/40 dark:bg-violet-900/10">
                    <p class="text-xs font-medium text-violet-500">Total Revenue</p>
                    <p class="mt-1 text-2xl font-bold text-violet-700 dark:text-violet-300">{{ formatPesos(stats.total_revenue) }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">This Month</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.this_month }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Monthly Subs</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.monthly_subs }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Yearly Subs</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.yearly_subs }}</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-3">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search doctor name or email…"
                    class="w-64 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-900 placeholder-gray-400 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"
                />
                <select
                    v-model="billingPeriod"
                    class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">All periods</option>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <select
                    v-model="source"
                    class="rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm text-gray-700 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">All sources</option>
                    <option value="return_url">Return URL</option>
                    <option value="webhook">Webhook</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/60">
                                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Doctor</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Session ID</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Plan</th>
                                <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-gray-400">Amount</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-400">Status</th>
                                <th class="px-5 py-3.5 text-center text-xs font-semibold uppercase tracking-wide text-gray-400">Source</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Pro Expires</th>
                                <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-400">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            <tr v-if="logs.data.length === 0">
                                <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No payment records found.
                                </td>
                            </tr>
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-800/50"
                            >
                                <!-- Doctor -->
                                <td class="px-5 py-3.5">
                                    <div v-if="log.doctor">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ log.doctor.name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ log.doctor.email }}</p>
                                    </div>
                                    <span v-else class="text-xs text-gray-400">—</span>
                                </td>
                                <!-- Session ID -->
                                <td class="px-5 py-3.5">
                                    <span class="max-w-[140px] truncate block font-mono text-xs text-gray-500 dark:text-gray-400" :title="log.checkout_session_id">
                                        {{ log.checkout_session_id }}
                                    </span>
                                </td>
                                <!-- Plan -->
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="log.billing_period === 'yearly'
                                            ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'"
                                    >
                                        Pro {{ log.billing_period === 'yearly' ? 'Yearly' : 'Monthly' }}
                                    </span>
                                </td>
                                <!-- Amount -->
                                <td class="px-5 py-3.5 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatPesos(log.amount) }}
                                </td>
                                <!-- Status -->
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="log.status === 'completed'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'"
                                    >
                                        {{ log.status }}
                                    </span>
                                </td>
                                <!-- Source -->
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        {{ log.source === 'webhook' ? 'Webhook' : 'Return URL' }}
                                    </span>
                                </td>
                                <!-- Pro Expires -->
                                <td class="px-5 py-3.5 text-sm text-gray-600 dark:text-gray-400">
                                    {{ log.pro_expires_at ? new Date(log.pro_expires_at).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) : '—' }}
                                </td>
                                <!-- Date -->
                                <td class="px-5 py-3.5 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.last_page > 1" class="flex items-center justify-between border-t border-gray-100 px-5 py-3 dark:border-gray-800">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Showing {{ logs.from }}–{{ logs.to }} of {{ logs.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in logs.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                                :class="link.active
                                    ? 'bg-violet-600 text-white'
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
