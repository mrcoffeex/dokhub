<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { CheckCircle2 } from 'lucide-vue-next';
</script>

<template>
    <AuthBase
        title="Application Submitted"
        description="We've received your doctor application"
    >
        <Head title="Application Submitted" />

        <div class="flex flex-col items-center gap-6">
            <div class="relative">
                <div class="absolute inset-0 bg-green-100 rounded-full animate-ping opacity-20" />
                <CheckCircle2 class="relative h-16 w-16 text-green-600" />
            </div>

            <div class="text-center">
                <h2 class="text-xl font-bold text-gray-900 mb-2">Application Received!</h2>
                <p class="text-sm text-gray-600 max-w-sm">
                    Thank you for applying to join DokHub. Our team will review your professional credentials and get back to you within 24-48 hours.
                </p>
            </div>

            <div class="w-full rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm text-amber-700">
                    <strong>What happens next?</strong>
                    <br />
                    We'll verify your credentials and qualifications. Once approved, you'll be able to log in and start managing your schedule and appointments.
                </p>
            </div>

            <div class="w-full space-y-3">
                <p class="text-xs text-gray-500 text-center">Check your email for updates</p>
                <Link
                    href="/"
                    class="w-full inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2.5 font-semibold text-white hover:bg-violet-700 transition-colors"
                >
                    Back to Home
                </Link>
            </div>

            <p class="text-center text-xs text-gray-500">
                Already have an account?
                <TextLink
                    href="/login"
                    class="text-violet-600 hover:text-violet-700 font-semibold"
                >
                    Sign in
                </TextLink>
            </p>
        </div>
    </AuthBase>
</template>
