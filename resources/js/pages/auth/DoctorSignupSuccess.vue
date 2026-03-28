<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
</script>

<template>
    <AuthBase>
        <Head title="Application Submitted" />

        <div class="mx-auto w-full max-w-xl">
            <div class="relative">
                <!-- Floating logo badge -->
                <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 z-10">
                    <div class="h-20 w-20 rounded-full bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-900/10 flex items-center justify-center shadow-md">
                        <!-- compact logo mark + check -->
                        <svg class="h-12 w-12" viewBox="0 0 64 64" role="img" aria-hidden="true">
                            <defs>
                                <linearGradient id="lg1" x1="0" x2="1" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#16A34A" />
                                    <stop offset="100%" stop-color="#059669" />
                                </linearGradient>
                            </defs>
                            <circle cx="32" cy="32" r="28" fill="url(#lg1)" opacity="0.95" />
                            <path d="M22 34l6 6 14-14" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-2xl pt-12 pb-8 px-8 shadow-sm border border-gray-100 dark:border-gray-800">
                    <div class="text-center">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Application Received</h2>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 max-w-lg mx-auto">
                            Thanks — we've received your doctor application. Our review team will verify your professional credentials and notify you by email within 24–48 hours.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-3">
                        <div class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-green-50 dark:bg-green-900/20 text-green-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Verification</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">We review your credentials to ensure they meet our requirements.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-lg border border-gray-100 bg-gray-50 p-3 dark:bg-gray-800 dark:border-gray-700">
                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-violet-50 dark:bg-violet-900/20 text-violet-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Email updates</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">We'll send progress updates and final approval to the email you provided.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row gap-3">
                        <Link
                            href="/"
                            class="flex-1 inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 font-semibold text-white hover:bg-violet-700 transition"
                        >
                            Back to Home
                        </Link>
                        <Link
                            href="/login"
                            class="flex-1 inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-gray-700"
                        >
                            Sign in
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthBase>
</template>
