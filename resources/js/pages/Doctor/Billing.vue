<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import { toast } from 'vue-sonner';

const props = defineProps<{
    plan: 'basic' | 'pro';
    isInTrial: boolean;
    isPaidPro: boolean;
    hasProAccess: boolean;
    trialDaysRemaining: number;
    trialEndsAt: string | null;
    proExpiresAt: string | null;
}>();

const monthlyForm = useForm({ billing_period: 'monthly' });
const yearlyForm  = useForm({ billing_period: 'yearly' });

function checkout(period: 'monthly' | 'yearly') {
    const form = period === 'yearly' ? yearlyForm : monthlyForm;
    form.post('/doctor/billing/checkout', {
        preserveScroll: true,
        onError: () => toast.error('Could not initiate payment. Please try again.'),
    });
}

const statusLabel = computed(() => {
    if (props.isPaidPro)  return 'Pro';
    if (props.isInTrial)  return 'Trial';
    return 'Basic';
});

const statusColor = computed(() => {
    if (props.isPaidPro)  return 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300';
    if (props.isInTrial)  return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
    return 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
});

const PRO_FEATURES = [
    { label: 'Patients module', desc: 'Full patient records, history & management' },
    { label: 'Diagnoses & prescriptions', desc: 'Create and manage clinical notes per patient' },
    { label: 'Unlimited patient records', desc: 'No caps on active patients' },
    { label: 'Priority support', desc: 'Faster response from our team' },
];

const BASIC_FEATURES = [
    { label: 'Public doctor profile', desc: 'Listed on the DokHub directory' },
    { label: 'Appointment management', desc: 'Accept & track all your bookings' },
    { label: 'Schedule management', desc: 'Set your availability and slots' },
    { label: 'Profile & photo', desc: 'Customise your public-facing profile' },
];

/** Payment methods accepted via PayMongo checkout */
const PAYMENT_METHODS = [
    { label: 'GCash',   color: 'bg-blue-50   text-blue-600   dark:bg-blue-900/20   dark:text-blue-400'   },
    { label: 'Maya',    color: 'bg-green-50  text-green-600  dark:bg-green-900/20  dark:text-green-400'  },
    { label: 'Card',    color: 'bg-gray-100  text-gray-600   dark:bg-gray-800      dark:text-gray-300'   },
    { label: 'GrabPay', color: 'bg-green-50  text-green-700  dark:bg-green-900/20  dark:text-green-400'  },
    { label: 'BDO',     color: 'bg-red-50    text-red-600    dark:bg-red-900/20    dark:text-red-400'    },
    { label: 'UBP',     color: 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400' },
];
</script>

<template>
    <Head title="Billing & Plan" />
    <DoctorLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Billing &amp; Plan</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">Manage your subscription and access</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">

            <!-- Current plan card -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-5 dark:border-gray-800">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                        :class="isPaidPro ? 'bg-orange-600' : isInTrial ? 'bg-amber-500' : 'bg-gray-200 dark:bg-gray-700'">
                        <!-- Crown icon -->
                        <svg class="h-6 w-6" :class="(isPaidPro || isInTrial) ? 'text-white' : 'text-gray-500 dark:text-gray-400'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l4 6 3-4 3 4 4-6v14H5V3z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Current Plan</h2>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColor">
                                {{ statusLabel }}
                            </span>
                        </div>
                        <!-- Active pro notice — checked first; trial_ends_at may still be future after subscribing -->
                        <p v-if="isPaidPro" class="mt-0.5 text-sm text-orange-600 dark:text-orange-400">
                            Subscribed to Pro
                            <span v-if="proExpiresAt" class="text-xs text-gray-400 dark:text-gray-500"> · expires {{ proExpiresAt }}</span>
                        </p>
                        <!-- Trial notice — only shown when NOT yet a paid subscriber -->
                        <p v-else-if="isInTrial" class="mt-0.5 text-sm text-amber-600 dark:text-amber-400">
                            Free trial — <strong>{{ trialDaysRemaining }} day{{ trialDaysRemaining !== 1 ? 's' : '' }}</strong> remaining
                            <span class="text-xs text-gray-400 dark:text-gray-500">(expires {{ trialEndsAt }})</span>
                        </p>
                        <!-- Basic -->
                        <p v-else class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                            Free plan · Upgrade to unlock all features
                        </p>
                    </div>
                </div>

                <!-- Trial expiry progress bar — hidden once on paid Pro -->
                <div v-if="isInTrial && !isPaidPro" class="px-6 py-4">
                    <div class="mb-1.5 flex justify-between text-xs text-gray-400">
                        <span>Trial period</span>
                        <span>{{ trialDaysRemaining }} / 15 days left</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                        <div
                            class="h-full rounded-full bg-amber-400 transition-all duration-500"
                            :style="{ width: `${(trialDaysRemaining / 15) * 100}%` }"
                        />
                    </div>
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                        Your trial gives you full Pro access. Upgrade before it ends to keep access.
                    </p>
                </div>
            </div>

            <!-- Feature comparison -->
            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Basic -->
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-400">BASIC</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Free</span>
                    </div>
                    <ul class="space-y-2.5">
                        <li v-for="f in BASIC_FEATURES" :key="f.label" class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ f.label }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ f.desc }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Pro -->
                <div class="rounded-2xl border-2 border-orange-500 bg-white p-5 shadow-sm dark:bg-gray-900">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="rounded-lg bg-orange-600 px-2.5 py-1 text-xs font-bold text-white">PRO</span>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Everything in Basic +</span>
                    </div>
                    <ul class="space-y-2.5">
                        <li v-for="f in PRO_FEATURES" :key="f.label" class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ f.label }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ f.desc }}</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Upgrade section — hide if already paid pro -->
            <div v-if="!isPaidPro" class="overflow-hidden rounded-2xl border border-orange-200 bg-orange-50 dark:border-orange-900/40 dark:bg-orange-900/10">
                <div class="px-6 pt-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        {{ isInTrial ? 'Lock in Pro before your trial ends' : 'Subscribe to Pro' }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Choose a billing period. Cancel anytime.
                    </p>
                    <!-- Accepted payment methods -->
                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <span class="text-xs text-gray-400">Pay with:</span>
                        <span
                            v-for="pm in PAYMENT_METHODS"
                            :key="pm.label"
                            class="rounded-md px-2 py-0.5 text-xs font-semibold"
                            :class="pm.color"
                        >{{ pm.label }}</span>
                    </div>
                </div>

                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <!-- Monthly -->
                    <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-900">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Monthly</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">₱499 <span class="text-sm font-normal text-gray-400">/ mo</span></p>
                        </div>
                        <button
                            type="button"
                            @click="checkout('monthly')"
                            :disabled="monthlyForm.processing"
                            class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl border border-orange-600 px-4 py-2.5 text-sm font-semibold text-orange-600 transition hover:bg-orange-600 hover:text-white disabled:opacity-50 dark:border-orange-500 dark:text-orange-400 dark:hover:bg-orange-600 dark:hover:text-white"
                        >
                            <svg v-if="monthlyForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ monthlyForm.processing ? 'Redirecting…' : 'Subscribe Monthly' }}
                        </button>
                    </div>

                    <!-- Yearly -->
                    <div class="relative flex flex-col gap-3 rounded-xl border-2 border-orange-500 bg-white p-5 dark:bg-gray-900">
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-orange-600 px-3 py-0.5 text-xs font-bold text-white">BEST VALUE</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Yearly</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                                ₱4,499 <span class="text-sm font-normal text-gray-400">/ yr</span>
                            </p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Save ₱1,489 vs monthly</p>
                        </div>
                        <button
                            type="button"
                            @click="checkout('yearly')"
                            :disabled="yearlyForm.processing"
                            class="mt-auto inline-flex items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                        >
                            <svg v-if="yearlyForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ yearlyForm.processing ? 'Redirecting…' : 'Subscribe Yearly' }}
                        </button>
                    </div>
                </div>

                <p class="px-6 pb-5 text-xs text-gray-400 dark:text-gray-500">
                    Payments are processed securely. By subscribing you agree to our
                    <a href="/terms-of-service" target="_blank" class="underline hover:text-orange-600">Terms of Service</a>.
                </p>
            </div>

            <!-- Already pro -->
            <div v-else class="rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-5 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Subscribed to Pro</p>
                        <p v-if="proExpiresAt" class="text-xs text-emerald-600 dark:text-emerald-400">Renews on {{ proExpiresAt }}</p>
                    </div>
                </div>
                <!-- Extend option -->
                <div class="mt-4 flex flex-wrap gap-3">
                    <button
                        type="button"
                        @click="checkout('monthly')"
                        :disabled="monthlyForm.processing"
                        class="rounded-xl border border-emerald-300 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-50 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                    >
                        Extend +1 month (₱499)
                    </button>
                    <button
                        type="button"
                        @click="checkout('yearly')"
                        :disabled="yearlyForm.processing"
                        class="rounded-xl border border-emerald-300 px-4 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 disabled:opacity-50 dark:border-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/30"
                    >
                        Extend +1 year (₱4,499)
                    </button>
                </div>
            </div>

        </div>
    </DoctorLayout>
</template>
