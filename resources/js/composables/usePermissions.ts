import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { SubUserRole } from '@/types/dokhub';

const PERMISSIONS: Record<SubUserRole, string[]> = {
    secretary: [
        'appointments.view',
        'appointments.manage',
        'patients.view',
        'patients.create',
        'patients.edit',
        'inventory.view',
    ],
    nurse: [
        'appointments.view',
        'patients.view',
        'vitals.manage',
        'diagnoses.view',
        'prescriptions.view',
        'records.manage',
        'inventory.view',
        'inventory.movements',
    ],
};

export function usePermissions() {
    const page = usePage();
    const subUserRole = computed(() => page.props.sub_user_context as SubUserRole | null);
    const isOwner = computed(() => subUserRole.value === null);

    function can(permission: string): boolean {
        if (isOwner.value) return true;
        return PERMISSIONS[subUserRole.value!]?.includes(permission) ?? false;
    }

    return { isOwner, subUserRole, can };
}
