<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const flash = computed(() => page.props.flash);
const isAdmin = computed(() => (page.props.auth as any)?.isAdmin ?? false);
const user = computed(() => (page.props.auth as any)?.user ?? null);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gray-50 antialiased">
        <!-- Navigation -->
        <header class="sticky top-0 z-50 border-b border-gray-200/80 bg-white/95 backdrop-blur-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link href="/" class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-600">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <span class="text-lg font-bold tracking-tight text-gray-900">DokHub</span>
                </Link>

                <nav class="hidden items-center gap-6 sm:flex">
                    <Link href="/doctors" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900">
                        Find Doctors
                    </Link>
                    <Link href="/my-appointment" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900">
                        My Appointment
                    </Link>
                    <template v-if="user">
                        <Link v-if="isAdmin" href="/admin" class="text-sm font-medium text-violet-600 hover:text-violet-700">
                            Admin Panel
                        </Link>
                        <Link v-else href="/dashboard" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900">
                            Sign In
                        </Link>
                        <Link href="/doctors" class="inline-flex items-center rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-violet-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-600">
                            Book Appointment
                        </Link>
                    </template>
                </nav>

                <!-- Mobile menu button -->
                <div class="sm:hidden">
                    <Link href="/doctors" class="rounded-lg bg-violet-600 px-3 py-1.5 text-sm font-semibold text-white">
                        Book Now
                    </Link>
                </div>
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
        <footer class="mt-24 border-t border-gray-200 bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <Link href="/" class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-violet-600">
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-gray-900">DokHub</span>
                    </Link>
                    <p class="text-sm text-gray-500">© {{ new Date().getFullYear() }} DokHub. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
