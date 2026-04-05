<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '@/types';
import { useAppearance } from '@/composables/useAppearance';
import ChatWidget from '@/components/ChatWidget.vue';

const APP_VERSION = import.meta.env.VITE_APP_VERSION as string | undefined;

const page = usePage<PageProps>();
const flash = computed(() => page.props.flash);
const isAdmin = computed(() => (page.props.auth as any)?.isAdmin ?? false);
const user = computed(() => (page.props.auth as any)?.user ?? null);

const mobileMenuOpen = ref(false);

const { resolvedAppearance, updateAppearance } = useAppearance();

function toggleTheme() {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 antialiased dark:bg-gray-950">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/95 backdrop-blur-sm dark:border-gray-800/80 dark:bg-gray-950/95">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5">
                    <img src="/logo.png" alt="DokHub" class="h-8 w-auto" />
                    <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">DokHub</span>
                </Link>

                <nav class="hidden items-center gap-5 sm:flex">
                    <Link href="/doctors" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Find Doctors
                    </Link>
                    <Link href="/my-appointment" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        My Appointment
                    </Link>
                    <Link href="/docs" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Docs
                    </Link>
                    <template v-if="user">
                        <Link v-if="isAdmin" href="/admin" class="text-sm font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300">
                            Admin Panel
                        </Link>
                        <Link v-else href="/dashboard" class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                            Sign In
                        </Link>
                    </template>

                    <!-- Theme toggle -->
                    <button
                        type="button"
                        class="rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        :aria-label="resolvedAppearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                        @click="toggleTheme"
                    >
                        <svg v-if="resolvedAppearance === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <template v-if="!user">
                        <Link href="/doctors" class="inline-flex items-center rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-orange-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600">
                            Book Appointment
                        </Link>
                    </template>
                </nav>

                <!-- Mobile: theme toggle + hamburger -->
                <div class="flex items-center gap-1 sm:hidden">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                        :aria-label="resolvedAppearance === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
                        @click="toggleTheme"
                    >
                        <svg v-if="resolvedAppearance === 'dark'" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-500 dark:text-gray-400 dark:hover:bg-gray-800"
                        :aria-expanded="mobileMenuOpen"
                        aria-label="Toggle navigation menu"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu dropdown -->
            <div
                v-show="mobileMenuOpen"
                class="border-t border-gray-200 bg-white px-4 pb-4 pt-3 sm:hidden dark:border-gray-800 dark:bg-gray-950"
            >
                <nav class="flex flex-col gap-1">
                    <Link
                        href="/doctors"
                        class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        Find Doctors
                    </Link>
                    <Link
                        href="/my-appointment"
                        class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                        @click="mobileMenuOpen = false"
                    >
                        My Appointment
                    </Link>
                    <template v-if="user">
                        <Link
                            v-if="isAdmin"
                            href="/admin"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium text-orange-600 hover:bg-orange-50 hover:text-orange-700 dark:text-orange-400 dark:hover:bg-orange-950/30"
                            @click="mobileMenuOpen = false"
                        >
                            Admin Panel
                        </Link>
                        <Link
                            v-else
                            href="/dashboard"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                            @click="mobileMenuOpen = false"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            href="/login"
                            class="rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                            @click="mobileMenuOpen = false"
                        >
                            Sign In
                        </Link>
                        <div class="pt-2">
                            <Link
                                href="/doctors"
                                class="block rounded-lg bg-orange-600 px-4 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-orange-700"
                                @click="mobileMenuOpen = false"
                            >
                                Book Appointment
                            </Link>
                        </div>
                    </template>
                </nav>
            </div>
        </header>

        <!-- Flash messages -->
        <div v-if="flash?.success || flash?.error" class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div v-if="flash.success" class="flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <svg class="h-4 w-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <svg class="h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9v4a1 1 0 102 0V9a1 1 0 10-2 0zm0-4a1 1 0 112 0 1 1 0 01-2 0z" clip-rule="evenodd" />
                </svg>
                {{ flash.error }}
            </div>
        </div>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="mt-24 border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <Link href="/" class="flex items-center gap-2">
                        <img src="/logo.png" alt="DokHub" class="h-7 w-auto" />
                        <span class="text-sm font-bold text-gray-900 dark:text-white">DokHub</span>
                    </Link>
                    <div class="flex flex-wrap items-center gap-4">
                        <Link href="/docs" class="text-sm text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Docs</Link>
                        <Link href="/terms-of-service" class="text-sm text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Terms</Link>
                        <Link href="/privacy-policy" class="text-sm text-gray-500 transition hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">Privacy</Link>
                    </div>
                    <div class="flex items-center gap-3">
                        <p class="text-sm text-gray-500 dark:text-gray-600">© {{ new Date().getFullYear() }} DokHub. All rights reserved.</p>
                        <span v-if="APP_VERSION" class="rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500">v{{ APP_VERSION }}</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- AI Chat Widget -->
        <ChatWidget role="guest" />
    </div>
</template>
