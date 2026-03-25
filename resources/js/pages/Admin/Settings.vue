<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import InputError from '@/components/InputError.vue';
import type { PageProps } from '@/types';
import { toast } from 'vue-sonner';

defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
}>();

const page = usePage<PageProps>();
const authUser = (page.props.auth as any)?.user;

const profileForm = useForm({
    name: authUser?.name ?? '',
    email: authUser?.email ?? '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updateProfile() {
    profileForm.patch('/settings/profile', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Profile updated', {
                description: 'Your account details have been saved.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not update profile', {
                description: 'Please check the highlighted fields.',
                duration: 5000,
            });
        },
    });
}

function updatePassword() {
    passwordForm.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            toast.success('Password changed', {
                description: 'Your new password is active.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not change password', {
                description: 'Please check your current password and try again.',
                duration: 5000,
            });
        },
    });
}
</script>

<template>
    <Head title="Profile Settings" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Profile Settings</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">Manage your account details and password</p>
            </div>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">

            <!-- Profile Information -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <!-- Card header -->
                <div class="flex items-center gap-4 border-b border-gray-100 px-4 py-4 sm:px-6 sm:py-5 dark:border-gray-800">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-900/30">
                        <svg class="h-5 w-5 text-violet-600 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Profile Information</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Update your name and email address</p>
                    </div>
                </div>

                <form @submit.prevent="updateProfile" class="px-4 py-5 sm:px-6 sm:py-6">
                    <!-- Success banner -->
                    <div
                        v-if="status === 'profile-information-updated'"
                        class="mb-5 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800/40 dark:bg-emerald-900/20 dark:text-emerald-400"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Profile updated successfully.
                    </div>

                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                            <input
                                v-model="profileForm.name"
                                type="text"
                                required
                                autocomplete="name"
                                placeholder="Your full name"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                :class="{ 'border-red-400': profileForm.errors.name }"
                            />
                            <InputError :message="profileForm.errors.name" class="mt-1 text-xs" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                            <input
                                v-model="profileForm.email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="you@example.com"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                :class="{ 'border-red-400': profileForm.errors.email }"
                            />
                            <InputError :message="profileForm.errors.email" class="mt-1 text-xs" />
                            <p v-if="mustVerifyEmail" class="mt-1.5 text-xs text-amber-600 dark:text-amber-400">
                                Your email is unverified. A new verification link will be sent on save.
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            :disabled="profileForm.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 disabled:opacity-60 active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <svg v-if="profileForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ profileForm.processing ? 'Saving…' : 'Save changes' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <!-- Card header -->
                <div class="flex items-center gap-4 border-b border-gray-100 px-4 py-4 sm:px-6 sm:py-5 dark:border-gray-800">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/20">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Change Password</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Ensure your account uses a strong password</p>
                    </div>
                </div>

                <form @submit.prevent="updatePassword" class="px-4 py-5 sm:px-6 sm:py-6">
                    <!-- Success banner -->
                    <div
                        v-if="status === 'password-updated'"
                        class="mb-5 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800/40 dark:bg-emerald-900/20 dark:text-emerald-400"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Password updated successfully.
                    </div>

                    <div class="space-y-4">
                        <!-- Current password -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Current password</label>
                            <input
                                v-model="passwordForm.current_password"
                                type="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                :class="{ 'border-red-400': passwordForm.errors.current_password }"
                            />
                            <InputError :message="passwordForm.errors.current_password" class="mt-1 text-xs" />
                        </div>

                        <!-- New password -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">New password</label>
                            <input
                                v-model="passwordForm.password"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                :class="{ 'border-red-400': passwordForm.errors.password }"
                            />
                            <InputError :message="passwordForm.errors.password" class="mt-1 text-xs" />
                        </div>

                        <!-- Confirm password -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm new password</label>
                            <input
                                v-model="passwordForm.password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-violet-500 dark:focus:ring-violet-900/40"
                                :class="{ 'border-red-400': passwordForm.errors.password_confirmation }"
                            />
                            <InputError :message="passwordForm.errors.password_confirmation" class="mt-1 text-xs" />
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 disabled:opacity-60 active:scale-95 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <svg v-if="passwordForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ passwordForm.processing ? 'Updating…' : 'Update password' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AdminLayout>
</template>
