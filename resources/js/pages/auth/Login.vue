<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    loginError?: string;
}>();
</script>

<template>
    <Head title="Sign In — DokHub" />

    <div class="flex min-h-screen">

        <!-- Left branding panel -->
        <div class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-indigo-700 p-10 lg:flex lg:w-5/12 xl:w-2/5">
            <!-- Background decoration -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-20 -left-20 h-72 w-72 rounded-full bg-white/5 blur-3xl"></div>
                <div class="absolute bottom-10 -right-16 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 h-48 w-48 -translate-x-1/2 -translate-y-1/2 rounded-full bg-violet-400/10 blur-2xl"></div>
            </div>

            <!-- Logo -->
            <div class="relative flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm ring-1 ring-white/30">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-white">DokHub</p>
                    <p class="text-xs text-violet-200">Healthcare Platform</p>
                </div>
            </div>

            <!-- Hero text -->
            <div class="relative">
                <h1 class="text-4xl font-bold leading-tight text-white">
                    Your practice,<br />
                    <span class="text-violet-200">streamlined.</span>
                </h1>
                <p class="mt-4 text-base leading-relaxed text-violet-200">
                    Manage your appointments, patients, and schedule — all from one powerful dashboard built for healthcare professionals.
                </p>

                <!-- Feature list -->
                <ul class="mt-8 space-y-3">
                    <li v-for="feature in [
                        'View and manage patient appointments',
                        'Write and track prescriptions easily',
                        'Stay on top of your daily schedule',
                    ]" :key="feature" class="flex items-center gap-3 text-sm text-violet-100">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/20">
                            <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        {{ feature }}
                    </li>
                </ul>
            </div>

            <!-- Bottom link -->
            <div class="relative">
                <Link href="/" class="inline-flex items-center gap-1.5 text-sm text-violet-200 transition-colors hover:text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to home
                </Link>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex flex-1 flex-col items-center justify-center bg-gray-50 px-6 py-12 dark:bg-gray-950">
            <!-- Mobile logo -->
            <div class="mb-8 flex items-center gap-2 lg:hidden">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <span class="text-lg font-bold text-gray-900 dark:text-white">DokHub</span>
            </div>

            <div class="w-full max-w-md">
                <!-- Card -->
                <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="mb-7">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Sign in to your account to continue</p>
                    </div>

                    <!-- Status message -->
                    <div v-if="status" class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-400">
                        {{ status }}
                    </div>

                    <!-- Login error (e.g. not approved) -->
                    <div v-if="loginError" class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-400">
                        {{ loginError }}
                    </div>

                    <Form
                        v-bind="store.form()"
                        :reset-on-success="['password']"
                        v-slot="{ errors, processing }"
                        class="space-y-5"
                    >
                        <!-- Email -->
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                :class="{ 'border-red-400 focus:border-red-400': errors.email }"
                            />
                            <InputError :message="errors.email" class="mt-1 text-xs" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="mb-1.5 flex items-center justify-between">
                                <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <a
                                    v-if="canResetPassword"
                                    :href="request.url()"
                                    :tabindex="5"
                                    class="text-xs font-medium text-violet-600 transition-colors hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                                >
                                    Forgot password?
                                </a>
                            </div>
                            <PasswordInput
                                id="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                            />
                            <InputError :message="errors.password" class="mt-1 text-xs" />
                        </div>

                        <!-- Remember me -->
                        <div class="flex items-center gap-2.5">
                            <Checkbox id="remember" name="remember" :tabindex="3" class="rounded-md" />
                            <label for="remember" class="cursor-pointer text-sm text-gray-600 dark:text-gray-400">Remember me for 30 days</label>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :tabindex="4"
                            :disabled="processing"
                            class="flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 disabled:opacity-60 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                            data-test="login-button"
                        >
                            <Spinner v-if="processing" class="h-4 w-4" />
                            {{ processing ? 'Signing in…' : 'Sign in' }}
                        </button>
                    </Form>
                </div>

                <!-- Doctor signup CTA -->
                <div class="mt-4 overflow-hidden rounded-2xl border border-violet-100 bg-white shadow-sm dark:border-violet-900/30 dark:bg-gray-900">
                    <div class="flex flex-wrap items-center gap-4 p-5">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-900/30">
                            <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Are you a doctor?</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Join our network of verified healthcare professionals</p>
                        </div>
                        <Link
                            href="/auth/signup/doctor"
                            :tabindex="6"
                            class="shrink-0 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-violet-700"
                        >
                            Apply now
                        </Link>
                    </div>
                </div>

                <!-- Back to home (mobile) -->
                <p class="mt-6 text-center text-xs text-gray-400 dark:text-gray-600 lg:hidden">
                    <Link href="/" class="hover:text-violet-600 dark:hover:text-violet-400">← Back to home</Link>
                </p>
            </div>
        </div>
    </div>
</template>
