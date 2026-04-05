<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '@/types';
import ThemeToggle from '@/components/ThemeToggle.vue';
import ChatWidget from '@/components/ChatWidget.vue';

const APP_VERSION = import.meta.env.VITE_APP_VERSION as string | undefined;

const page = usePage<PageProps>();
const flash = computed(() => page.props.flash);
const user = computed(() => (page.props.auth as any)?.user);

const navItems = [
    { label: 'Dashboard',       href: '/admin',                  icon: 'grid' },
    { label: 'Analytics',       href: '/admin/analytics',         icon: 'chart'  },
    { label: 'Doctors',         href: '/admin/doctors',           icon: 'users' },
    { label: 'Appointments',    href: '/admin/appointments',      icon: 'calendar' },
    { label: 'Specializations', href: '/admin/specializations',   icon: 'tag' },
    { label: 'Insurance',       href: '/admin/insurances',        icon: 'credit-card' },
    { label: 'Pricing',         href: '/admin/pricing',            icon: 'tag' },
    { label: 'Payment Logs',    href: '/admin/payment-logs',      icon: 'receipt' },
    { label: 'System Logs',     href: '/admin/system-logs',        icon: 'logs' },
    { label: 'Spam Detection',  href: '/admin/spam-detection',    icon: 'shield' },
    { label: 'Profile',         href: '/admin/profile',           icon: 'user' },
];

function isActive(href: string): boolean {
    const path = window.location.pathname;
    if (href === '/admin') return path === '/admin';
    return path.startsWith(href);
}

const sidebarOpen = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Mobile overlay -->
        <div
            v-show="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 border-r border-gray-200 bg-white shadow-xl transition-transform duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900 lg:shadow-none"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="flex h-full flex-col">
                <!-- Logo -->
                <div class="flex h-16 items-center gap-3 border-b border-gray-100 px-6 dark:border-gray-800">
                    <img src="/logo.png" alt="DokHub" class="h-8 w-auto" />
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">DokHub</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Admin Panel</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 space-y-0.5 px-3 py-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="sidebarOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors"
                        :class="isActive(item.href)
                            ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100'"
                    >
                        <!-- Grid icon -->
                        <svg v-if="item.icon === 'grid'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <!-- Users icon -->
                        <svg v-else-if="item.icon === 'users'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <!-- Calendar icon -->
                        <svg v-else-if="item.icon === 'calendar'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <!-- Tag icon -->
                        <svg v-else-if="item.icon === 'tag'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <!-- User icon -->
                        <svg v-else-if="item.icon === 'user'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <!-- Shield icon -->
                        <svg v-else-if="item.icon === 'shield'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <!-- Credit card icon (Insurance) -->
                        <svg v-else-if="item.icon === 'credit-card'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <!-- Receipt icon (Payment Logs) -->
                        <svg v-else-if="item.icon === 'receipt'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <!-- Logs icon (System Logs) -->
                        <svg v-else-if="item.icon === 'logs'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <!-- Chart / Analytics icon -->
                        <svg v-else-if="item.icon === 'chart'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        {{ item.label }}
                    </Link>

                    <div class="pt-4">
                        <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                            <a
                                href="/"
                                target="_blank"
                                @click="sidebarOpen = false"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                View Public Site
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- User footer -->
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    <!-- User info -->
                    <div class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5">
                        <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-600 text-sm font-bold text-white ring-2 ring-orange-100 dark:ring-orange-900/40">
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? 'A' }}
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-green-400 dark:border-gray-900"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ user?.name }}</p>
                            <p class="truncate text-xs text-gray-400 dark:text-gray-500">Administrator</p>
                        </div>
                    </div>
                    <!-- Version -->
                    <p v-if="APP_VERSION" class="mb-1 px-3 text-[11px] text-gray-300 dark:text-gray-600">v{{ APP_VERSION }}</p>
                    <!-- Sign out button -->
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-500 transition-all duration-200 hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                    >
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100 transition-all duration-200 group-hover:bg-red-100 dark:bg-gray-800 dark:group-hover:bg-red-900/40">
                            <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </span>
                        <span class="flex-1 text-left">Sign out</span>
                        <svg class="h-3.5 w-3.5 opacity-0 transition-all duration-200 group-hover:opacity-100 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-40 flex h-16 items-center gap-3 border-b border-gray-200 bg-white px-4 sm:px-6 dark:border-gray-800 dark:bg-gray-900">
                <!-- Hamburger (mobile only) -->
                <button
                    @click="sidebarOpen = !sidebarOpen"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                    aria-label="Toggle navigation"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0 flex-1">
                    <slot name="header">
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Admin</h1>
                    </slot>
                </div>
                <ThemeToggle />
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error" class="px-4 pt-4 sm:px-6">
                <div v-if="flash?.success" class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                    <svg class="h-4 w-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page slot -->
            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>

        <!-- AI Chat Widget -->
        <ChatWidget role="admin" />
    </div>
</template>
