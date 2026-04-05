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
const doctorPlan = computed(() => page.props.doctor_plan);
const subUserRole = computed(() => page.props.sub_user_context ?? null);
const isOwner = computed(() => subUserRole.value === null);

const mobileOpen = ref(false);

const allNavItems = [
    { label: 'Dashboard',    href: '/doctor-dashboard',    icon: 'grid',        ownerOnly: false },
    { label: 'Analytics',    href: '/doctor/analytics',   icon: 'chart',       ownerOnly: false },
    { label: 'Appointments', href: '/doctor/appointments', icon: 'calendar',    ownerOnly: false },
    { label: 'Patients',     href: '/doctor/patients',     icon: 'users',       ownerOnly: false },
    { label: 'Inventory',    href: '/doctor/inventory',    icon: 'inventory',   ownerOnly: false },
    { label: 'Schedule',     href: '/doctor/schedule',     icon: 'clock',       ownerOnly: true  },
    { label: 'My Profile',   href: '/doctor/profile',      icon: 'user',        ownerOnly: true  },
    { label: 'Sub-Users',    href: '/doctor/sub-users',    icon: 'team',        ownerOnly: true  },
    { label: 'Poster',       href: '/doctor/poster',       icon: 'printer',     ownerOnly: true  },
    { label: 'Billing',      href: '/doctor/billing',      icon: 'credit-card', ownerOnly: true  },
];

const navItems = computed(() =>
    allNavItems.filter(item => !item.ownerOnly || isOwner.value)
);

function isActive(href: string): boolean {
    const path = window.location.pathname;
    if (href === '/doctor-dashboard') return path === '/doctor-dashboard';
    return path.startsWith(href);
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950">
        <!-- Mobile overlay -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-200"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
                @click="mobileOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-gray-200 bg-white transition-transform duration-300 dark:border-gray-800 dark:bg-gray-900 lg:translate-x-0"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-full flex-col">
                <!-- Logo + mobile close -->
                <div class="flex h-16 items-center justify-between border-b border-gray-100 px-6 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <img src="/logo.png" alt="DokHub" class="h-8 w-auto" />
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">DokHub</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Doctor Portal</p>
                        </div>
                    </div>
                    <!-- Close button (mobile only) -->
                    <button
                        @click="mobileOpen = false"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 lg:hidden dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Close menu"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Nav -->
                <nav class="flex-1 space-y-0.5 px-3 py-4">
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="mobileOpen = false"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors"
                        :class="isActive(item.href)
                            ? 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-100'"
                    >
                        <!-- Grid / Dashboard icon -->
                        <svg v-if="item.icon === 'grid'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <!-- Calendar icon -->
                        <svg v-else-if="item.icon === 'calendar'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <!-- User icon -->
                        <svg v-else-if="item.icon === 'user'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <!-- Clock icon -->
                        <svg v-else-if="item.icon === 'clock'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <!-- Users icon -->
                        <svg v-else-if="item.icon === 'users'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <!-- Credit-card icon -->
                        <svg v-else-if="item.icon === 'credit-card'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <!-- Printer icon -->
                        <svg v-else-if="item.icon === 'printer'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        <!-- Inventory icon -->
                        <svg v-else-if="item.icon === 'inventory'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <!-- Team / Sub-users icon -->
                        <svg v-else-if="item.icon === 'team'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
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
                                @click="mobileOpen = false"
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

                <!-- Trial warning banner — hidden once on a paid Pro subscription -->
                <div
                    v-if="doctorPlan?.isInTrial && !doctorPlan?.isPaidPro"
                    class="mx-3 mb-2 rounded-xl p-3"
                    :class="doctorPlan.trialDays <= 3 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-amber-50 dark:bg-amber-900/20'"
                >
                    <p class="text-xs font-semibold" :class="doctorPlan.trialDays <= 3 ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300'">
                        {{ doctorPlan.trialDays }} day{{ doctorPlan.trialDays === 1 ? '' : 's' }} left in trial
                    </p>
                    <Link href="/doctor/billing" class="mt-1 block text-xs font-medium underline" :class="doctorPlan.trialDays <= 3 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'">
                        Subscribe to Pro →
                    </Link>
                </div>
                <div
                    v-else-if="doctorPlan && !doctorPlan.hasProAccess"
                    class="mx-3 mb-2 rounded-xl bg-orange-50 p-3 dark:bg-orange-900/20"
                >
                    <p class="text-xs font-semibold text-orange-700 dark:text-orange-300">Upgrade to unlock all features</p>
                    <Link href="/doctor/billing" class="mt-1 block text-xs font-medium text-orange-600 underline dark:text-orange-400">
                        View plans →
                    </Link>
                </div>

                <!-- User footer -->
                <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                    <!-- User info -->
                    <div class="mb-2 flex items-center gap-3 rounded-xl px-3 py-2.5">
                        <div class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-orange-600 text-sm font-bold text-white ring-2 ring-orange-100 dark:ring-orange-900/40">
                            {{ user?.name?.charAt(0)?.toUpperCase() ?? 'D' }}
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-white bg-green-400 dark:border-gray-900"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ user?.name }}</p>
                                <span
                                    v-if="doctorPlan"
                                    class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase leading-none"
                                    :class="doctorPlan.isPaidPro
                                        ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300'
                                        : doctorPlan.isInTrial
                                            ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'
                                            : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'"
                                >
                                    {{ doctorPlan.isPaidPro ? 'Pro' : doctorPlan.isInTrial ? 'Trial' : 'Basic' }}
                                </span>
                            </div>
                            <p class="truncate text-xs text-gray-400 dark:text-gray-500">
                                {{ subUserRole ? (subUserRole === 'secretary' ? 'Secretary' : 'Nurse') : 'Doctor' }}
                            </p>
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
            <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-gray-200 bg-white px-4 sm:px-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Hamburger (mobile / tablet only) -->
                    <button
                        @click="mobileOpen = true"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-gray-200 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 lg:hidden dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Open menu"
                    >
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <slot name="header">
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Doctor Portal</h1>
                    </slot>
                </div>
                <ThemeToggle />
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error" class="px-4 pt-4 sm:px-6">
                <div v-if="flash?.success" class="flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-300">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800/40 dark:bg-red-900/20 dark:text-red-300">
                    {{ flash.error }}
                </div>
            </div>

            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>

        <!-- AI Chat Widget -->
        <ChatWidget role="doctor" />
    </div>
</template>
