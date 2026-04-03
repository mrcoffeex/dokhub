<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    pricing: {
        monthly_price_cents: number;
        annual_price_cents: number;
        annual_savings_cents: number;
        updated_at: string | null;
        updated_by: string | null;
    };
}>();

const form = useForm({
    monthly_price: (props.pricing.monthly_price_cents / 100).toFixed(2),
    annual_price:  (props.pricing.annual_price_cents  / 100).toFixed(2),
});

function formatPesos(centavos: number): string {
    return '₱' + (centavos / 100).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

/** Live preview values from form inputs (in centavos) */
const previewMonthlyCents = computed(() => Math.round(parseFloat(form.monthly_price || '0') * 100));
const previewAnnualCents  = computed(() => Math.round(parseFloat(form.annual_price  || '0') * 100));
const previewSavingsCents = computed(() => (previewMonthlyCents.value * 12) - previewAnnualCents.value);
const previewSavingsPct   = computed(() => {
    const full = previewMonthlyCents.value * 12;
    if (!full) return 0;
    return Math.round((previewSavingsCents.value / full) * 100);
});

function submit() {
    form.put('/admin/pricing', {
        preserveScroll: true,
        onSuccess: () => toast.success('Pricing updated successfully.'),
        onError:   () => toast.error('Please fix the errors below.'),
    });
}
</script>

<template>
    <Head title="Pricing Settings" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Pricing Settings</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">Control the Pro subscription rates shown to doctors</p>
            </div>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">

            <!-- Edit form -->
            <form @submit.prevent="submit" class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Pro Plan Rates</h2>
                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">Prices are in Philippine Peso (₱). Changes take effect immediately for new checkouts.</p>
                </div>

                <div class="space-y-5 px-6 py-6">

                    <!-- Monthly -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Monthly Price
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">₱</span>
                            <input
                                v-model="form.monthly_price"
                                type="number"
                                min="1"
                                max="999999"
                                step="0.01"
                                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-8 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400 ring-2 ring-red-100': form.errors.monthly_price }"
                            />
                        </div>
                        <p v-if="form.errors.monthly_price" class="mt-1 text-xs text-red-500">{{ form.errors.monthly_price }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Charged per month for doctors on the monthly plan.</p>
                    </div>

                    <!-- Annual -->
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Annual Price
                        </label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-gray-400">₱</span>
                            <input
                                v-model="form.annual_price"
                                type="number"
                                min="1"
                                max="9999999"
                                step="0.01"
                                class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-8 pr-4 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                                :class="{ 'border-red-400 ring-2 ring-red-100': form.errors.annual_price }"
                            />
                        </div>
                        <p v-if="form.errors.annual_price" class="mt-1 text-xs text-red-500">{{ form.errors.annual_price }}</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Charged once per year. Discount vs monthly is computed automatically.</p>
                    </div>

                    <!-- Live preview -->
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Live Preview</p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <!-- Monthly preview -->
                            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Monthly</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatPesos(previewMonthlyCents) }}
                                    <span class="text-sm font-normal text-gray-400">/ mo</span>
                                </p>
                                <p class="mt-1 text-xs text-gray-400">{{ formatPesos(previewMonthlyCents * 12) }} / year if billed monthly</p>
                            </div>

                            <!-- Annual preview -->
                            <div class="relative rounded-xl border-2 border-orange-400 bg-white p-4 dark:bg-gray-900">
                                <span v-if="previewSavingsCents > 0" class="absolute -top-2.5 left-1/2 -translate-x-1/2 rounded-full bg-orange-600 px-2.5 py-0.5 text-[10px] font-bold uppercase text-white whitespace-nowrap">
                                    Save {{ previewSavingsPct }}% ({{ formatPesos(previewSavingsCents) }})
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Annual</p>
                                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatPesos(previewAnnualCents) }}
                                    <span class="text-sm font-normal text-gray-400">/ yr</span>
                                </p>
                                <p class="mt-1 text-xs text-orange-500 dark:text-orange-400">
                                    ~{{ formatPesos(Math.round(previewAnnualCents / 12)) }} / mo effective rate
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/60">
                    <p v-if="pricing.updated_at" class="text-xs text-gray-400 dark:text-gray-500">
                        Last updated {{ pricing.updated_at }}
                        <span v-if="pricing.updated_by">by <strong class="text-gray-600 dark:text-gray-400">{{ pricing.updated_by }}</strong></span>
                    </p>
                    <span v-else class="text-xs text-gray-400">Never updated</span>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-orange-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                    >
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ form.processing ? 'Saving…' : 'Save Pricing' }}
                    </button>
                </div>
            </form>

            <!-- Info card -->
            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4 dark:border-blue-900/30 dark:bg-blue-900/10">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z" />
                    </svg>
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-blue-700 dark:text-blue-400">How pricing works</p>
                        <ul class="space-y-0.5 text-xs text-blue-600 dark:text-blue-400">
                            <li>• Prices are stored in centavos and converted to pesos for display.</li>
                            <li>• Changes apply to <strong>new checkout sessions only</strong> — existing subscriptions are unaffected.</li>
                            <li>• The annual "Save X%" badge is computed automatically from the two prices.</li>
                            <li>• Payment processing is handled by PayMongo at the time of checkout.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </AdminLayout>
</template>
