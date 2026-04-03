<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/password';

const props = defineProps<{
    token: string;
    email: string;
}>();

const inputEmail = ref(props.email);
</script>

<template>
    <Head title="Reset Password — DokHub" />

    <div class="flex min-h-screen">

        <!-- Left branding panel -->
        <div class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-orange-700 via-orange-600 to-indigo-700 p-10 lg:flex lg:w-5/12 xl:w-2/5">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
                <div class="absolute bottom-10 -right-16 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 h-48 w-48 -translate-x-1/2 -translate-y-1/2 rounded-full bg-orange-400/10 blur-2xl"></div>
            </div>

            <!-- Logo -->
            <div class="relative flex items-center gap-3">
                <img src="/logo.png" alt="DokHub" class="h-10 w-auto" />
                <div>
                    <p class="text-lg font-bold text-white">DokHub</p>
                    <p class="text-xs text-orange-200">Healthcare Platform</p>
                </div>
            </div>

            <!-- Hero text -->
            <div class="relative">
                <h1 class="text-4xl font-bold leading-tight text-white">
                    Create a new<br />
                    <span class="text-orange-200">password.</span>
                </h1>
                <p class="mt-4 text-base leading-relaxed text-orange-200">
                    Choose a strong, unique password to keep your account secure.
                </p>

                <ul class="mt-8 space-y-3">
                    <li v-for="point in [
                        'At least 8 characters long',
                        'Use a mix of letters, numbers and symbols',
                        'Don\'t reuse a previous password',
                    ]" :key="point" class="flex items-center gap-3 text-sm text-orange-100">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        {{ point }}
                    </li>
                </ul>
            </div>

            <!-- Back link -->
            <div class="relative">
                <Link href="/login" class="inline-flex items-center gap-1.5 text-sm text-orange-200 transition-colors hover:text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to sign in
                </Link>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex flex-1 flex-col items-center justify-center bg-gray-50 px-6 py-12 dark:bg-gray-950">
            <!-- Mobile logo -->
            <div class="mb-8 flex items-center gap-2 lg:hidden">
                <img src="/logo.png" alt="DokHub" class="h-8 w-auto" />
                <span class="text-lg font-bold text-gray-900 dark:text-white">DokHub</span>
            </div>

            <div class="w-full max-w-md">
                <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">

                    <!-- Icon -->
                    <div class="mb-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-50 dark:bg-orange-900/30">
                        <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>

                    <div class="mb-7">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Set new password</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Your new password must be at least 8 characters long.
                        </p>
                    </div>

                    <Form
                        v-bind="update.form()"
                        :transform="(data) => ({ ...data, token, email })"
                        :reset-on-success="['password', 'password_confirmation']"
                        v-slot="{ errors, processing }"
                        class="space-y-5"
                    >
                        <!-- Email (read-only, shown for clarity) -->
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Email address
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                autocomplete="email"
                                readonly
                                v-model="inputEmail"
                                class="w-full cursor-not-allowed rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 outline-none dark:border-gray-700 dark:bg-gray-800/60 dark:text-gray-400"
                            />
                            <InputError :message="errors.email" class="mt-1 text-xs" />
                        </div>

                        <!-- New password -->
                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                New password
                            </label>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                autofocus
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                            />
                            <InputError :message="errors.password" class="mt-1 text-xs" />
                        </div>

                        <!-- Confirm password -->
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Confirm new password
                            </label>
                            <PasswordInput
                                id="password_confirmation"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-orange-400 focus:ring-2 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-orange-500 dark:focus:ring-orange-900/40"
                            />
                            <InputError :message="errors.password_confirmation" class="mt-1 text-xs" />
                        </div>

                        <button
                            type="submit"
                            :disabled="processing"
                            data-test="reset-password-button"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-orange-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <Spinner v-if="processing" class="h-4 w-4" />
                            {{ processing ? 'Saving…' : 'Reset password' }}
                        </button>
                    </Form>
                </div>

                <p class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    Remember your password?
                    <Link href="/login" class="font-medium text-orange-600 transition-colors hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                        Sign in
                    </Link>
                </p>

                <!-- Back to home (mobile) -->
                <p class="mt-3 text-center text-xs text-gray-400 dark:text-gray-600 lg:hidden">
                    <Link href="/" class="hover:text-orange-600 dark:hover:text-orange-400">← Back to home</Link>
                </p>
            </div>
        </div>
    </div>
</template>
