<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import type { Insurance } from '@/types';
import { toast } from 'vue-sonner';

const props = defineProps<{
    insurances: Insurance[];
    stats: { total: number; active: number; inactive: number };
}>();

// ── Add form ──────────────────────────────────────────────────────────────────
const addForm = useForm({ name: '' });

function submitAdd() {
    addForm.post('/admin/insurances', {
        preserveScroll: true,
        onSuccess: () => {
            addForm.reset();
            toast.success('Insurance provider added.');
        },
        onError: () => {
            toast.error(addForm.errors.name ?? 'Could not add insurance provider.');
        },
    });
}

// ── Inline edit ───────────────────────────────────────────────────────────────
const editingId = ref<number | null>(null);
const editName  = ref('');

function startEdit(ins: Insurance) {
    editingId.value = ins.id;
    editName.value  = ins.name;
}

function cancelEdit() {
    editingId.value = null;
    editName.value  = '';
}

function submitEdit(ins: Insurance) {
    if (!editName.value.trim() || editName.value.trim() === ins.name) {
        cancelEdit();
        return;
    }
    router.patch(`/admin/insurances/${ins.id}`, { name: editName.value.trim() }, {
        preserveScroll: true,
        onSuccess: () => {
            cancelEdit();
            toast.success('Insurance provider renamed.');
        },
        onError: (errors) => {
            toast.error((errors as any).name ?? 'Could not rename insurance provider.');
        },
    });
}

// ── Toggle active ─────────────────────────────────────────────────────────────
function toggleActive(ins: Insurance) {
    router.patch(`/admin/insurances/${ins.id}`, { is_active: !ins.is_active }, {
        preserveScroll: true,
        onSuccess: () => toast.success(ins.is_active ? `"${ins.name}" deactivated.` : `"${ins.name}" activated.`),
    });
}

// ── Delete ────────────────────────────────────────────────────────────────────
const deletingId = ref<number | null>(null);

function confirmDelete(ins: Insurance) {
    deletingId.value = ins.id;
}

function cancelDelete() {
    deletingId.value = null;
}

function submitDelete(ins: Insurance) {
    router.delete(`/admin/insurances/${ins.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            deletingId.value = null;
            toast.success(`"${ins.name}" deleted.`);
        },
        onError: (errors) => {
            deletingId.value = null;
            toast.error((errors as any).delete ?? 'Could not delete insurance provider.');
        },
    });
}
</script>

<template>
    <Head title="Insurance Providers" />
    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Insurance Providers</h1>
                <p class="text-xs text-gray-400 dark:text-gray-500">Manage the accepted insurance options used across the platform</p>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-6">

            <!-- Stats row -->
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-gray-100 bg-white px-5 py-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Total</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 px-5 py-4 dark:border-emerald-900/40 dark:bg-emerald-900/10">
                    <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Active</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ stats.active }}</p>
                </div>
                <div class="rounded-2xl border border-gray-100 bg-white px-5 py-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Inactive</p>
                    <p class="mt-1 text-2xl font-bold text-gray-500 dark:text-gray-400">{{ stats.inactive }}</p>
                </div>
            </div>

            <!-- Main card -->
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

                <!-- Card header + Add form -->
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/30">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">All Insurance Providers</h2>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Add, rename, activate, or remove entries</p>
                        </div>
                    </div>

                    <!-- Add new -->
                    <form @submit.prevent="submitAdd" class="mt-4 flex items-center gap-2">
                        <input
                            v-model="addForm.name"
                            type="text"
                            placeholder="New insurance provider name…"
                            maxlength="100"
                            class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:border-blue-500 dark:focus:ring-blue-900/40"
                            :class="{ 'border-red-400 dark:border-red-500': addForm.errors.name }"
                        />
                        <button
                            type="submit"
                            :disabled="addForm.processing || !addForm.name.trim()"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add
                        </button>
                    </form>
                    <p v-if="addForm.errors.name" class="mt-1.5 text-xs text-red-500">{{ addForm.errors.name }}</p>
                </div>

                <!-- List -->
                <ul class="divide-y divide-gray-50 dark:divide-gray-800">
                    <li
                        v-for="ins in insurances"
                        :key="ins.id"
                        class="group flex items-center gap-3 px-5 py-3.5 transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-800/40"
                        :class="{ 'opacity-60': !ins.is_active }"
                    >
                        <!-- Active indicator dot -->
                        <span
                            class="h-2 w-2 shrink-0 rounded-full"
                            :class="ins.is_active ? 'bg-emerald-400' : 'bg-gray-300 dark:bg-gray-600'"
                            :title="ins.is_active ? 'Active' : 'Inactive'"
                        />

                        <!-- Name / inline edit -->
                        <div class="min-w-0 flex-1">
                            <template v-if="editingId === ins.id">
                                <input
                                    v-model="editName"
                                    type="text"
                                    maxlength="100"
                                    class="w-full rounded-lg border border-blue-400 bg-white px-3 py-1.5 text-sm text-gray-900 outline-none ring-2 ring-blue-100 focus:ring-blue-200 dark:border-blue-500 dark:bg-gray-800 dark:text-gray-100 dark:ring-blue-900/40"
                                    @keydown.enter.prevent="submitEdit(ins)"
                                    @keydown.escape.prevent="cancelEdit"
                                    autofocus
                                />
                            </template>
                            <template v-else>
                                <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-200">{{ ins.name }}</span>
                            </template>
                        </div>

                        <!-- Doctors count badge -->
                        <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            {{ ins.doctors_count }} {{ ins.doctors_count === 1 ? 'doctor' : 'doctors' }}
                        </span>

                        <!-- Actions -->
                        <div class="flex shrink-0 items-center gap-1">
                            <!-- Delete confirmation inline -->
                            <template v-if="deletingId === ins.id">
                                <span class="mr-1 text-xs text-gray-500 dark:text-gray-400">Delete?</span>
                                <button
                                    @click="submitDelete(ins)"
                                    class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-red-700 active:scale-95"
                                >
                                    Yes, delete
                                </button>
                                <button
                                    @click="cancelDelete"
                                    class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-500 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400"
                                >
                                    Cancel
                                </button>
                            </template>

                            <!-- Edit save/cancel -->
                            <template v-else-if="editingId === ins.id">
                                <button
                                    @click="submitEdit(ins)"
                                    class="rounded-lg bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white transition hover:bg-blue-700 active:scale-95"
                                >
                                    Save
                                </button>
                                <button
                                    @click="cancelEdit"
                                    class="rounded-lg border border-gray-200 px-2.5 py-1 text-xs font-medium text-gray-500 transition hover:border-gray-300 hover:text-gray-700 dark:border-gray-700 dark:text-gray-400"
                                >
                                    Cancel
                                </button>
                            </template>

                            <!-- Default action buttons -->
                            <template v-else>
                                <!-- Toggle active -->
                                <button
                                    @click="toggleActive(ins)"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                    :title="ins.is_active ? 'Deactivate' : 'Activate'"
                                >
                                    <svg v-if="ins.is_active" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>

                                <!-- Rename -->
                                <button
                                    @click="startEdit(ins)"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                                    title="Rename"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <!-- Delete (disabled if in use) -->
                                <button
                                    @click="confirmDelete(ins)"
                                    :disabled="ins.doctors_count > 0"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                    :title="ins.doctors_count > 0 ? 'Cannot delete — accepted by doctors' : 'Delete'"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </template>
                        </div>
                    </li>

                    <!-- Empty state -->
                    <li v-if="insurances.length === 0" class="px-5 py-12 text-center">
                        <svg class="mx-auto mb-3 h-8 w-8 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No insurance providers yet. Add one above.</p>
                    </li>
                </ul>
            </div>

        </div>
    </AdminLayout>
</template>
