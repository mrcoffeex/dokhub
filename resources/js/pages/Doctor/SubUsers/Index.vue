<script setup lang="ts">
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import type { DoctorSubUser, SubUserRole } from '@/types/dokhub';

const props = defineProps<{
    subUsers: DoctorSubUser[];
    permissions: Record<SubUserRole, string[]>;
}>();

const showAdd = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'secretary' as SubUserRole,
});

function submit() {
    form.post('/doctor/sub-users', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showAdd.value = false;
            toast.success('Staff account created.');
        },
        onError: () => toast.error('Could not create account. Check the form for errors.'),
    });
}

const deleteForm = useForm({});
function removeSubUser(id: number, name: string) {
    toast(`Remove ${name}?`, {
        description: 'Their account will be permanently deleted. This cannot be undone.',
        action: {
            label: 'Remove',
            onClick: () => {
                deleteForm.delete(`/doctor/sub-users/${id}`, {
                    preserveScroll: true,
                    onSuccess: () => toast.success(`${name}'s account has been removed.`),
                    onError: () => toast.error('Could not remove account. Please try again.'),
                });
            },
        },
        cancel: { label: 'Cancel', onClick: () => {} },
        duration: 8000,
    });
}

const toggleForm = useForm({});
function toggleActive(id: number, name: string, currentlyActive: boolean) {
    toggleForm.patch(`/doctor/sub-users/${id}/toggle`, {
        preserveScroll: true,
        onSuccess: () => toast.success(`${name}'s account ${currentlyActive ? 'deactivated' : 'activated'}.`),
        onError: () => toast.error('Could not update account status.'),
    });
}

// Edit
const editingSubUser = ref<DoctorSubUser | null>(null);
const editForm = useForm({
    name: '',
    email: '',
    role: 'secretary' as SubUserRole,
    password: '',
    password_confirmation: '',
});

function openEdit(su: DoctorSubUser) {
    editingSubUser.value = su;
    editForm.name = su.user?.name ?? '';
    editForm.email = su.user?.email ?? '';
    editForm.role = su.role;
    editForm.password = '';
    editForm.password_confirmation = '';
}

function closeEdit() {
    editingSubUser.value = null;
    editForm.reset();
}

function submitEdit() {
    if (!editingSubUser.value) return;
    editForm.patch(`/doctor/sub-users/${editingSubUser.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Staff account updated.');
            closeEdit();
        },
        onError: () => toast.error('Could not update account. Check the form for errors.'),
    });
}

const roleBadgeClass = (role: SubUserRole) =>
    role === 'secretary'
        ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
        : 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300';

const permissionLabels: Record<string, string> = {
    'appointments.view':    'View appointments',
    'appointments.manage':  'Confirm / cancel appointments',
    'patients.view':        'View patients',
    'patients.create':      'Register patients',
    'patients.edit':        'Edit patient info',
    'vitals.manage':        'Record & edit vitals',
    'diagnoses.view':       'View diagnoses',
    'prescriptions.view':   'View prescriptions',
    'records.manage':       'Upload & delete records',
    'inventory.view':       'View inventory',
    'inventory.movements':  'Record stock movements',
};
</script>

<template>
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Sub-Users</h1>
        </template>

        <div class="max-w-4xl space-y-6">
            <!-- Header row -->
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage staff accounts (secretaries &amp; nurses) that can access your portal with limited permissions.
                    </p>
                </div>
                <button
                    @click="showAdd = !showAdd"
                    class="flex items-center gap-2 rounded-xl bg-orange-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-orange-700 transition"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Staff
                </button>
            </div>

            <!-- Add form -->
            <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                leave-active-class="transition-all duration-150 ease-in"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div v-if="showAdd" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Add New Staff Account</h2>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                <input v-model="form.name" type="text" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                <input v-model="form.email" type="email" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                                <input v-model="form.password" type="password" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                <input v-model="form.password_confirmation" type="password" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select v-model="form.role"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="secretary">Secretary</option>
                                    <option value="nurse">Nurse</option>
                                </select>
                                <p v-if="form.errors.role" class="mt-1 text-xs text-red-500">{{ form.errors.role }}</p>
                            </div>
                        </div>

                        <!-- Role permissions preview -->
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-800/50">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Permissions for <span class="capitalize">{{ form.role }}</span>
                            </p>
                            <ul class="space-y-1">
                                <li v-for="perm in permissions[form.role]" :key="perm"
                                    class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ permissionLabels[perm] ?? perm }}
                                </li>
                            </ul>
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" :disabled="form.processing"
                                class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-medium text-white hover:bg-orange-700 disabled:opacity-60 transition">
                                Create Account
                            </button>
                            <button type="button" @click="showAdd = false"
                                class="rounded-xl border border-gray-200 px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </Transition>

            <!-- Sub-users list -->
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                <div v-if="subUsers.length === 0" class="py-16 text-center">
                    <svg class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No staff accounts yet</p>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Add a secretary or nurse to give them limited access.</p>
                </div>

                <table v-else class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50">
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Staff Member</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="su in subUsers" :key="su.id" class="transition hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ su.user?.name?.charAt(0)?.toUpperCase() ?? '?' }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ su.user?.name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ su.user?.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="roleBadgeClass(su.role)">
                                    {{ su.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium"
                                    :class="su.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500'">
                                    <span class="h-1.5 w-1.5 rounded-full"
                                        :class="su.is_active ? 'bg-green-500' : 'bg-gray-400'" />
                                    {{ su.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openEdit(su)"
                                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="toggleActive(su.id, su.user?.name ?? 'Sub-user', su.is_active)"
                                        :disabled="toggleForm.processing"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition"
                                        :class="su.is_active
                                            ? 'border border-gray-200 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800'
                                            : 'border border-green-200 text-green-600 hover:bg-green-50 dark:border-green-800 dark:text-green-400 dark:hover:bg-green-900/20'"
                                    >
                                        {{ su.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        @click="removeSubUser(su.id, su.user?.name ?? 'Sub-user')"
                                        :disabled="deleteForm.processing"
                                        class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-500 transition hover:bg-red-50 disabled:opacity-60 dark:border-red-900/40 dark:text-red-400 dark:hover:bg-red-900/20"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Permissions reference -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Role Permissions Reference</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div v-for="(perms, role) in permissions" :key="role">
                        <p class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
                            <span class="inline-flex rounded-full px-2 py-0.5 capitalize"
                                :class="roleBadgeClass(role as SubUserRole)">
                                {{ role }}
                            </span>
                        </p>
                        <ul class="space-y-1">
                            <li v-for="perm in perms" :key="perm"
                                class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                <svg class="h-3.5 w-3.5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ permissionLabels[perm] ?? perm }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </DoctorLayout>

    <!-- Edit modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-150"
            leave-to-class="opacity-0"
        >
            <div v-if="editingSubUser" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeEdit" />
                <div class="relative w-full max-w-2xl rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Edit Staff Account</h2>
                        <button @click="closeEdit" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submitEdit" class="space-y-4 p-6">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                                <input v-model="editForm.name" type="text" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-500">{{ editForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                <input v-model="editForm.email" type="email" required
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-500">{{ editForm.errors.email }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                <select v-model="editForm.role"
                                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="secretary">Secretary</option>
                                    <option value="nurse">Nurse</option>
                                </select>
                                <p v-if="editForm.errors.role" class="mt-1 text-xs text-red-500">{{ editForm.errors.role }}</p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-dashed border-gray-200 p-4 dark:border-gray-700">
                            <p class="mb-3 text-xs font-semibold text-gray-500 dark:text-gray-400">Change Password <span class="font-normal">(leave blank to keep current)</span></p>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">New Password</label>
                                    <input v-model="editForm.password" type="password" autocomplete="new-password"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                    <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-500">{{ editForm.errors.password }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password</label>
                                    <input v-model="editForm.password_confirmation" type="password" autocomplete="new-password"
                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm shadow-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-400/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="submit" :disabled="editForm.processing"
                                class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-medium text-white hover:bg-orange-700 disabled:opacity-60 transition">
                                Save Changes
                            </button>
                            <button type="button" @click="closeEdit"
                                class="rounded-xl border border-gray-200 px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 transition dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
