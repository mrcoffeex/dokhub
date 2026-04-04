<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { Patient, Diagnosis, Appointment, Doctor, PatientVital, PatientRecord } from '@/types';
import { toast } from 'vue-sonner';
import { usePermissions } from '@/composables/usePermissions';

type TransferDoctor = Pick<Doctor, 'id' | 'name' | 'specialization'>;

const props = defineProps<{
    patient: Patient;
    appointments: Appointment[];
    doctor: Doctor;
    approvedDoctors: TransferDoctor[];
}>();

const { can, isOwner } = usePermissions();

// ---- Tabs ----
type Tab = 'overview' | 'vitals' | 'diagnoses' | 'prescriptions' | 'appointments' | 'records';
const activeTab = ref<Tab>('overview');

const tabs: { key: Tab; label: string }[] = [
    { key: 'overview',      label: 'Overview' },
    { key: 'vitals',        label: 'Vitals' },
    { key: 'diagnoses',     label: 'Diagnoses' },
    { key: 'prescriptions', label: 'Prescriptions' },
    { key: 'appointments',  label: 'Appointments' },
    { key: 'records',       label: 'Records' },
];

function tabCount(key: Tab): number | null {
    if (key === 'diagnoses')     return props.patient.diagnoses?.length ?? 0;
    if (key === 'prescriptions') return props.patient.prescriptions?.length ?? 0;
    if (key === 'vitals')        return props.patient.vitals?.length ?? 0;
    if (key === 'records')       return props.patient.records?.length ?? 0;
    return null;
}

// ---- Patient edit ----
const editingInfo = ref(false);
const infoForm = useForm({
    name:            props.patient.name ?? '',
    email:           props.patient.email ?? '',
    phone:           props.patient.phone ?? '',
    gender:          props.patient.gender ?? '',
    date_of_birth:   props.patient.date_of_birth ?? '',
    allergies:       props.patient.allergies ?? '',
    medical_history: props.patient.medical_history ?? '',
    notes:           props.patient.notes ?? '',
});
function submitInfo() {
    infoForm.patch(`/doctor/patients/${props.patient.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingInfo.value = false;
            toast.success('Patient info updated', {
                description: 'Changes have been saved.',
                duration: 4000,
            });
        },
        onError: () => {
            toast.error('Could not save changes', {
                description: 'Please review the fields and try again.',
                duration: 5000,
            });
        },
    });
}

// ---- Diagnosis form ----
const showDiagForm = ref(false);
const editingDiagId = ref<number | null>(null);
const diagForm = useForm({
    title:          '',
    symptoms:       '',
    description:    '',
    treatment:      '',
    follow_up_date: '',
    appointment_id: null as number | null,
});

function openNewDiag() {
    diagForm.reset();
    editingDiagId.value = null;
    showDiagForm.value = true;
}

function openEditDiag(d: Diagnosis) {
    diagForm.title          = d.title;
    diagForm.symptoms       = d.symptoms ?? '';
    diagForm.description    = d.description ?? '';
    diagForm.treatment      = d.treatment ?? '';
    diagForm.follow_up_date = d.follow_up_date ?? '';
    diagForm.appointment_id = d.appointment_id;
    editingDiagId.value     = d.id;
    showDiagForm.value      = true;
    activeTab.value         = 'diagnoses';
}

function submitDiag() {
    if (editingDiagId.value) {
        diagForm.put(`/doctor/patients/${props.patient.id}/diagnoses/${editingDiagId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showDiagForm.value = false;
                diagForm.reset();
                editingDiagId.value = null;
                toast.success('Diagnosis updated', { duration: 4000 });
            },
            onError: () => {
                toast.error('Could not update diagnosis', { duration: 5000 });
            },
        });
    } else {
        diagForm.post(`/doctor/patients/${props.patient.id}/diagnoses`, {
            preserveScroll: true,
            onSuccess: () => {
                showDiagForm.value = false;
                diagForm.reset();
                toast.success('Diagnosis added', { duration: 4000 });
            },
            onError: () => {
                toast.error('Could not add diagnosis', { duration: 5000 });
            },
        });
    }
}

function deleteDiag(d: Diagnosis) {
    if (!confirm(`Delete diagnosis "${d.title}"?`)) return;
    router.delete(`/doctor/patients/${props.patient.id}/diagnoses/${d.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Diagnosis deleted', { duration: 3000 });
        },
        onError: () => {
            toast.error('Could not delete diagnosis', { duration: 4000 });
        },
    });
}

// ---- Vitals form ----
const showVitalForm = ref(false);
const editingVitalId = ref<number | null>(null);
const vitalForm = useForm({
    recorded_at:              new Date().toISOString().slice(0, 16),
    blood_pressure_systolic:  '' as string | number,
    blood_pressure_diastolic: '' as string | number,
    heart_rate:               '' as string | number,
    temperature:              '' as string | number,
    weight:                   '' as string | number,
    height:                   '' as string | number,
    oxygen_saturation:        '' as string | number,
    notes:                    '',
});

function toLocalInput(iso: string): string {
    const d = new Date(iso);
    const p = (n: number) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
}

function openNewVital() {
    vitalForm.reset();
    vitalForm.recorded_at = new Date().toISOString().slice(0, 16);
    editingVitalId.value = null;
    showVitalForm.value = true;
}

function openEditVital(v: PatientVital) {
    vitalForm.recorded_at              = toLocalInput(v.recorded_at);
    vitalForm.blood_pressure_systolic  = v.blood_pressure_systolic  ?? '';
    vitalForm.blood_pressure_diastolic = v.blood_pressure_diastolic ?? '';
    vitalForm.heart_rate               = v.heart_rate               ?? '';
    vitalForm.temperature              = v.temperature              ?? '';
    vitalForm.weight                   = v.weight                   ?? '';
    vitalForm.height                   = v.height                   ?? '';
    vitalForm.oxygen_saturation        = v.oxygen_saturation        ?? '';
    vitalForm.notes                    = v.notes                    ?? '';
    editingVitalId.value               = v.id;
    showVitalForm.value                = true;
    activeTab.value                    = 'vitals';
}

function submitVital() {
    if (editingVitalId.value) {
        vitalForm.put(`/doctor/patients/${props.patient.id}/vitals/${editingVitalId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showVitalForm.value = false;
                vitalForm.reset();
                editingVitalId.value = null;
                toast.success('Vitals updated', { duration: 4000 });
            },
            onError: () => toast.error('Could not update vitals', { duration: 5000 }),
        });
    } else {
        vitalForm.post(`/doctor/patients/${props.patient.id}/vitals`, {
            preserveScroll: true,
            onSuccess: () => {
                showVitalForm.value = false;
                vitalForm.reset();
                toast.success('Vitals recorded', { duration: 4000 });
            },
            onError: () => toast.error('Could not record vitals', { duration: 5000 }),
        });
    }
}

function deleteVital(v: PatientVital) {
    if (!confirm('Delete this vitals record?')) return;
    router.delete(`/doctor/patients/${props.patient.id}/vitals/${v.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('Vitals deleted', { duration: 3000 }),
        onError:   () => toast.error('Could not delete', { duration: 4000 }),
    });
}

const latestVital = computed<PatientVital | null>(() => (props.patient.vitals ?? [])[0] ?? null);

// ---- Vital status helpers ----
type VitalStatus = { label: string; textCls: string; bgCls: string };

function bpStatus(s: number | null, d: number | null): VitalStatus {
    if (!s || !d) return { label: '—', textCls: 'text-gray-400', bgCls: 'bg-gray-50 dark:bg-gray-800' };
    if (s >= 140 || d >= 90) return { label: 'High',     textCls: 'text-red-600 dark:text-red-400',       bgCls: 'bg-red-50 dark:bg-red-900/20' };
    if (s >= 130 || d >= 80) return { label: 'Stage 1',  textCls: 'text-orange-600 dark:text-orange-400',  bgCls: 'bg-orange-50 dark:bg-orange-900/20' };
    if (s >= 120)            return { label: 'Elevated', textCls: 'text-amber-600 dark:text-amber-400',    bgCls: 'bg-amber-50 dark:bg-amber-900/20' };
    return { label: 'Normal', textCls: 'text-emerald-600 dark:text-emerald-400', bgCls: 'bg-emerald-50 dark:bg-emerald-900/20' };
}

function hrStatus(hr: number | null): VitalStatus {
    if (!hr)      return { label: '—',      textCls: 'text-gray-400',                              bgCls: 'bg-gray-50 dark:bg-gray-800' };
    if (hr < 60)  return { label: 'Low',    textCls: 'text-amber-600 dark:text-amber-400',         bgCls: 'bg-amber-50 dark:bg-amber-900/20' };
    if (hr > 100) return { label: 'High',   textCls: 'text-orange-600 dark:text-orange-400',       bgCls: 'bg-orange-50 dark:bg-orange-900/20' };
    return { label: 'Normal', textCls: 'text-emerald-600 dark:text-emerald-400', bgCls: 'bg-emerald-50 dark:bg-emerald-900/20' };
}

function tempStatus(t: number | null): VitalStatus {
    if (!t)        return { label: '—',          textCls: 'text-gray-400',                        bgCls: 'bg-gray-50 dark:bg-gray-800' };
    if (t >= 38)   return { label: 'Fever',      textCls: 'text-red-600 dark:text-red-400',       bgCls: 'bg-red-50 dark:bg-red-900/20' };
    if (t >= 37.3) return { label: 'Low-grade',  textCls: 'text-amber-600 dark:text-amber-400',   bgCls: 'bg-amber-50 dark:bg-amber-900/20' };
    if (t < 36.1)  return { label: 'Subnormal',  textCls: 'text-blue-600 dark:text-blue-400',     bgCls: 'bg-blue-50 dark:bg-blue-900/20' };
    return { label: 'Normal', textCls: 'text-emerald-600 dark:text-emerald-400', bgCls: 'bg-emerald-50 dark:bg-emerald-900/20' };
}

function spo2Status(o: number | null): VitalStatus {
    if (!o)     return { label: '—',        textCls: 'text-gray-400',                            bgCls: 'bg-gray-50 dark:bg-gray-800' };
    if (o < 90) return { label: 'Critical', textCls: 'text-red-600 dark:text-red-400',           bgCls: 'bg-red-50 dark:bg-red-900/20' };
    if (o < 95) return { label: 'Low',      textCls: 'text-amber-600 dark:text-amber-400',       bgCls: 'bg-amber-50 dark:bg-amber-900/20' };
    return { label: 'Normal', textCls: 'text-emerald-600 dark:text-emerald-400', bgCls: 'bg-emerald-50 dark:bg-emerald-900/20' };
}

function calcBMI(w: number | null, h: number | null): string | null {
    if (!w || !h) return null;
    return (w / ((h / 100) ** 2)).toFixed(1);
}

// ---- General helpers ----
function formatDate(d: string | null, time = false): string {
    if (!d) return '—';
    const opts: Intl.DateTimeFormatOptions = { month: 'short', day: 'numeric', year: 'numeric' };
    if (time) { opts.hour = '2-digit'; opts.minute = '2-digit'; }
    return new Date(d.includes('T') ? d : d + 'T00:00:00').toLocaleDateString('en-US', opts);
}

function formatVitalDate(iso: string): string {
    return new Date(iso).toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: 'numeric', minute: '2-digit', hour12: true,
    });
}

function initials(name: string): string {
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

const statusConfig: Record<string, { label: string; cls: string }> = {
    pending:   { label: 'Pending',   cls: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    confirmed: { label: 'Confirmed', cls: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' },
    cancelled: { label: 'Cancelled', cls: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' },
    completed: { label: 'Completed', cls: 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' },
};

const confirmedAppts = computed(() => props.appointments.filter(a => ['confirmed', 'completed'].includes(a.status)));

// ---- Patient records ----
const showRecordForm = ref(false);
const recordForm = useForm({
    file:  null as File | null,
    name:  '',
});

function onFileChange(e: Event) {
    const input = e.target as HTMLInputElement;
    const file  = input.files?.[0] ?? null;
    recordForm.file = file;
    if (file && !recordForm.name) {
        recordForm.name = file.name.replace(/\.[^.]+$/, '');
    }
}

function submitRecord() {
    recordForm.post(`/doctor/patients/${props.patient.id}/records`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            recordForm.reset();
            showRecordForm.value = false;
            toast.success('Record uploaded', { duration: 4000 });
        },
        onError: () => toast.error('Upload failed', { duration: 5000 }),
    });
}

function deleteRecord(r: PatientRecord) {
    if (!confirm(`Delete "${r.name}"?`)) return;
    router.delete(`/doctor/patients/${props.patient.id}/records/${r.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('Record deleted', { duration: 3000 }),
        onError:   () => toast.error('Could not delete', { duration: 4000 }),
    });
}

function formatBytes(bytes: number): string {
    if (bytes < 1024)        return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function fileIcon(mime: string): string {
    if (mime === 'application/pdf')                return '📄';
    if (mime.startsWith('image/'))                 return '🖼️';
    if (mime.includes('word') || mime.includes('document')) return '📝';
    return '📎';
}

// ---- Patient transfer ----
const showTransferModal = ref(false);
const transferSearch = ref('');
const transferForm = useForm({
    target_doctor_id: null as number | null,
});

const filteredDoctors = computed(() => {
    const q = transferSearch.value.trim().toLowerCase();
    if (!q) return props.approvedDoctors;
    return props.approvedDoctors.filter(d =>
        d.name.toLowerCase().includes(q) ||
        (d.specialization ?? []).some((s: string) => s.toLowerCase().includes(q))
    );
});

function openTransferModal() {
    transferForm.reset();
    transferSearch.value = '';
    showTransferModal.value = true;
}

function submitTransfer() {
    if (!transferForm.target_doctor_id) return;
    const target = props.approvedDoctors.find(d => d.id === transferForm.target_doctor_id);
    if (!confirm(`Send a copy of ${props.patient.name}'s records to Dr. ${target?.name}?`)) return;
    transferForm.post(`/doctor/patients/${props.patient.id}/transfer`, {
        preserveScroll: true,
        onSuccess: () => {
            showTransferModal.value = false;
            toast.success('Copy sent successfully', {
                description: `A copy of the patient's records was sent to Dr. ${target?.name}.`,
                duration: 5000,
            });
        },
        onError: () => toast.error('Could not send copy. Please try again.', { duration: 5000 }),
    });
}
</script>

<template>
    <Head :title="patient.name" />
    <DoctorLayout>
        <template #header>
            <div class="flex items-center gap-2 text-sm">
                <Link href="/doctor/patients" class="text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-400 transition-colors">
                    Patients
                </Link>
                <svg class="h-4 w-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="font-semibold text-gray-900 dark:text-white">{{ patient.name }}</span>
            </div>
        </template>

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <!-- LEFT: Patient card -->
            <div class="shrink-0 lg:w-72">
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <!-- Gradient header -->
                    <div class="relative bg-gradient-to-br from-orange-500 via-orange-600 to-indigo-600 px-5 pb-14 pt-5">
                        <span class="pointer-events-none absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></span>
                        <span class="pointer-events-none absolute bottom-2 right-12 h-10 w-10 rounded-full bg-white/10"></span>
                        <div class="relative flex items-start justify-between">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-lg font-bold text-white ring-2 ring-white/30 shadow-lg">
                                {{ initials(patient.name) }}
                            </div>
                            <button
                                v-if="can('patients.edit')"
                                @click="editingInfo = !editingInfo"
                                class="flex items-center gap-1 rounded-lg bg-white/20 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-white/30"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                {{ editingInfo ? 'Cancel' : 'Edit' }}
                            </button>
                        </div>
                        <div class="relative mt-2.5">
                            <h2 class="text-base font-bold leading-tight text-white">{{ patient.name }}</h2>
                            <p class="text-xs text-orange-100">{{ patient.email }}</p>
                        </div>
                    </div>
                    <!-- Floating stats row -->
                    <div class="relative -mt-6 mx-4 mb-4 flex gap-2">
                        <div class="flex-1 rounded-xl bg-white p-2.5 text-center shadow-md dark:bg-gray-800">
                            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500">Visits</p>
                            <p class="text-lg font-bold leading-tight text-gray-800 dark:text-white">{{ appointments.length }}</p>
                        </div>
                        <div class="flex-1 rounded-xl bg-white p-2.5 text-center shadow-md dark:bg-gray-800">
                            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500">Diagnoses</p>
                            <p class="text-lg font-bold leading-tight text-indigo-600 dark:text-indigo-400">{{ patient.diagnoses?.length ?? 0 }}</p>
                        </div>
                        <div class="flex-1 rounded-xl bg-white p-2.5 text-center shadow-md dark:bg-gray-800">
                            <p class="text-[10px] font-medium text-gray-400 dark:text-gray-500">Rx</p>
                            <p class="text-lg font-bold leading-tight text-emerald-600 dark:text-emerald-400">{{ patient.prescriptions?.length ?? 0 }}</p>
                        </div>
                    </div>

                    <!-- Edit form -->
                    <form v-if="editingInfo" @submit.prevent="submitInfo" class="border-b border-gray-100 p-4 dark:border-gray-800 space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name <span class="text-red-500">*</span></label>
                            <input
                                v-model="infoForm.name"
                                type="text"
                                required
                                :class="infoForm.errors.name
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 focus:ring-1 focus:ring-orange-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.name" class="mt-1 text-xs text-red-500">{{ infoForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email</label>
                            <input
                                v-model="infoForm.email"
                                type="email"
                                :class="infoForm.errors.email
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 focus:ring-1 focus:ring-orange-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.email" class="mt-1 text-xs text-red-500">{{ infoForm.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Phone</label>
                            <input
                                v-model="infoForm.phone"
                                type="tel"
                                :class="infoForm.errors.phone
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 focus:ring-1 focus:ring-orange-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.phone" class="mt-1 text-xs text-red-500">{{ infoForm.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Gender</label>
                            <select
                                v-model="infoForm.gender"
                                :class="infoForm.errors.gender
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            >
                                <option value="">— Select —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <p v-if="infoForm.errors.gender" class="mt-1 text-xs text-red-500">{{ infoForm.errors.gender }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Date of Birth</label>
                            <input
                                v-model="infoForm.date_of_birth"
                                type="date"
                                :class="infoForm.errors.date_of_birth
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.date_of_birth" class="mt-1 text-xs text-red-500">{{ infoForm.errors.date_of_birth }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Allergies</label>
                            <textarea
                                v-model="infoForm.allergies"
                                rows="2"
                                :class="infoForm.errors.allergies
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.allergies" class="mt-1 text-xs text-red-500">{{ infoForm.errors.allergies }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Medical History</label>
                            <textarea
                                v-model="infoForm.medical_history"
                                rows="3"
                                :class="infoForm.errors.medical_history
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.medical_history" class="mt-1 text-xs text-red-500">{{ infoForm.errors.medical_history }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Internal Notes</label>
                            <textarea
                                v-model="infoForm.notes"
                                rows="2"
                                :class="infoForm.errors.notes
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="infoForm.errors.notes" class="mt-1 text-xs text-red-500">{{ infoForm.errors.notes }}</p>
                        </div>
                        <button type="submit" :disabled="infoForm.processing" class="w-full rounded-xl bg-orange-600 py-2 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50">
                            Save Changes
                        </button>
                    </form>

                    <!-- Info display -->
                    <div v-else class="space-y-3 p-4 text-sm">
                        <div v-if="patient.phone" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ patient.phone }}
                        </div>
                        <div v-if="patient.gender" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 capitalize">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ patient.gender }}
                            <span v-if="patient.age" class="text-gray-400">· {{ patient.age }} yrs</span>
                        </div>
                        <div v-if="patient.date_of_birth" class="text-xs text-gray-500 dark:text-gray-400 pl-6">
                            DOB: {{ formatDate(patient.date_of_birth) }}
                        </div>
                        <div v-if="patient.allergies" class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                            <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1">⚠ Allergies</p>
                            <p class="text-xs text-red-700 dark:text-red-300 whitespace-pre-wrap">{{ patient.allergies }}</p>
                        </div>
                        <div v-if="patient.medical_history" class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Medical History</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ patient.medical_history }}</p>
                        </div>
                        <div v-if="patient.notes" class="rounded-lg bg-amber-50 p-3 dark:bg-amber-900/20">
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Notes</p>
                            <p class="text-xs text-amber-700 dark:text-amber-300 whitespace-pre-wrap">{{ patient.notes }}</p>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-600 pt-1 border-t border-gray-100 dark:border-gray-800">
                            Patient since {{ formatDate(patient.first_seen_at) }}
                        </p>
                        <button
                            v-if="isOwner"
                            @click="openTransferModal"
                            class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300"
                        >
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Send a Copy
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Tabs -->
            <div class="flex-1 min-w-0">
                <!-- Tab nav -->
                <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        class="flex shrink-0 items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition-all"
                        :class="activeTab === tab.key
                            ? 'bg-white shadow-sm text-orange-700 dark:bg-gray-900 dark:text-orange-300'
                            : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tabCount(tab.key) !== null"
                            class="rounded-full px-1.5 py-0.5 text-[10px] leading-none tabular-nums"
                            :class="activeTab === tab.key
                                ? 'bg-orange-100 text-orange-600 dark:bg-orange-900/40 dark:text-orange-300'
                                : 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                        >{{ tabCount(tab.key) }}</span>
                    </button>
                </div>

                <!-- OVERVIEW TAB -->
                <div v-if="activeTab === 'overview'" class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm dark:border-orange-900/30 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Visits</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ appointments.length }}</p>
                    </div>
                    <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm dark:border-indigo-900/30 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Diagnoses</p>
                        <p class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ patient.diagnoses?.length ?? 0 }}</p>
                        <button @click="activeTab = 'diagnoses'" class="mt-1 text-xs text-indigo-500 hover:underline">View all →</button>
                    </div>
                    <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm dark:border-emerald-900/30 dark:bg-gray-900">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Prescriptions</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ patient.prescriptions?.length ?? 0 }}</p>
                        <button @click="activeTab = 'prescriptions'" class="mt-1 text-xs text-emerald-500 hover:underline">View all →</button>
                    </div>

                    <!-- Quick actions -->
                    <div class="sm:col-span-3 flex flex-wrap gap-3">
                        <button
                            v-if="isOwner"
                            @click="openNewDiag(); activeTab = 'diagnoses'"
                            class="flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Diagnosis
                        </button>
                        <Link
                            v-if="isOwner"
                            :href="`/doctor/patients/${patient.id}/prescriptions/create`"
                            class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Write Prescription
                        </Link>
                    </div>

                    <!-- Latest vitals summary card -->
                    <div v-if="latestVital" class="sm:col-span-3 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Latest Vitals</p>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatVitalDate(latestVital.recorded_at) }}</span>
                                <button @click="activeTab = 'vitals'" class="text-xs font-medium text-orange-500 hover:underline">View all →</button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <div v-if="latestVital.blood_pressure_systolic" :class="bpStatus(latestVital.blood_pressure_systolic, latestVital.blood_pressure_diastolic).bgCls" class="rounded-xl px-3 py-2.5 text-center">
                                <p class="text-[10px] font-medium text-gray-400">Blood Pressure</p>
                                <p :class="bpStatus(latestVital.blood_pressure_systolic, latestVital.blood_pressure_diastolic).textCls" class="mt-0.5 text-sm font-bold">{{ latestVital.blood_pressure_systolic }}/{{ latestVital.blood_pressure_diastolic }}</p>
                                <p class="text-[10px] text-gray-400">mmHg</p>
                            </div>
                            <div v-if="latestVital.heart_rate" :class="hrStatus(latestVital.heart_rate).bgCls" class="rounded-xl px-3 py-2.5 text-center">
                                <p class="text-[10px] font-medium text-gray-400">Heart Rate</p>
                                <p :class="hrStatus(latestVital.heart_rate).textCls" class="mt-0.5 text-sm font-bold">{{ latestVital.heart_rate }}</p>
                                <p class="text-[10px] text-gray-400">bpm</p>
                            </div>
                            <div v-if="latestVital.temperature" :class="tempStatus(latestVital.temperature).bgCls" class="rounded-xl px-3 py-2.5 text-center">
                                <p class="text-[10px] font-medium text-gray-400">Temperature</p>
                                <p :class="tempStatus(latestVital.temperature).textCls" class="mt-0.5 text-sm font-bold">{{ latestVital.temperature }}°C</p>
                                <p :class="tempStatus(latestVital.temperature).textCls" class="text-[10px] font-medium">{{ tempStatus(latestVital.temperature).label }}</p>
                            </div>
                            <div v-if="latestVital.oxygen_saturation" :class="spo2Status(latestVital.oxygen_saturation).bgCls" class="rounded-xl px-3 py-2.5 text-center">
                                <p class="text-[10px] font-medium text-gray-400">SpO₂</p>
                                <p :class="spo2Status(latestVital.oxygen_saturation).textCls" class="mt-0.5 text-sm font-bold">{{ latestVital.oxygen_saturation }}%</p>
                                <p class="text-[10px] text-gray-400">oxygen</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="sm:col-span-3 flex items-center justify-between rounded-2xl border border-dashed border-gray-200 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-900">
                        <p class="text-sm text-gray-400 dark:text-gray-500">No vitals recorded yet.</p>
                        <button v-if="can('vitals.manage')" @click="activeTab = 'vitals'; openNewVital()" class="text-xs font-semibold text-orange-600 hover:underline dark:text-orange-400">+ Record Vitals</button>
                    </div>

                    <!-- Recent diagnosis -->
                    <div v-if="(patient.diagnoses?.length ?? 0) > 0" class="sm:col-span-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <p class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-200">Latest Diagnosis</p>
                        <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                            <div class="flex items-start justify-between">
                                <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ patient.diagnoses![0].title }}</p>
                                <span class="text-xs text-gray-400">{{ formatDate(patient.diagnoses![0].created_at) }}</span>
                            </div>
                            <p v-if="patient.diagnoses![0].symptoms" class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                <span class="font-medium text-gray-600 dark:text-gray-300">Symptoms:</span> {{ patient.diagnoses![0].symptoms }}
                            </p>
                            <p v-if="patient.diagnoses![0].treatment" class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">
                                <span class="font-medium text-gray-600 dark:text-gray-300">Treatment:</span> {{ patient.diagnoses![0].treatment }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- VITALS TAB -->
                <div v-else-if="activeTab === 'vitals'">
                    <!-- Latest vitals summary -->
                    <div v-if="latestVital" class="mb-5 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Latest Vitals</p>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatVitalDate(latestVital.recorded_at) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                            <div v-if="latestVital.blood_pressure_systolic" :class="bpStatus(latestVital.blood_pressure_systolic, latestVital.blood_pressure_diastolic).bgCls" class="rounded-xl p-3 text-center">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Blood Pressure</p>
                                <p :class="bpStatus(latestVital.blood_pressure_systolic, latestVital.blood_pressure_diastolic).textCls" class="mt-1 text-xl font-bold leading-tight">{{ latestVital.blood_pressure_systolic }}/{{ latestVital.blood_pressure_diastolic }}</p>
                                <p class="text-[10px] text-gray-400">mmHg</p>
                                <span :class="bpStatus(latestVital.blood_pressure_systolic, latestVital.blood_pressure_diastolic).textCls" class="mt-1 block text-[10px] font-semibold uppercase">{{ bpStatus(latestVital.blood_pressure_systolic, latestVital.blood_pressure_diastolic).label }}</span>
                            </div>
                            <div v-if="latestVital.heart_rate" :class="hrStatus(latestVital.heart_rate).bgCls" class="rounded-xl p-3 text-center">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Heart Rate</p>
                                <p :class="hrStatus(latestVital.heart_rate).textCls" class="mt-1 text-xl font-bold leading-tight">{{ latestVital.heart_rate }}</p>
                                <p class="text-[10px] text-gray-400">bpm</p>
                                <span :class="hrStatus(latestVital.heart_rate).textCls" class="mt-1 block text-[10px] font-semibold uppercase">{{ hrStatus(latestVital.heart_rate).label }}</span>
                            </div>
                            <div v-if="latestVital.temperature" :class="tempStatus(latestVital.temperature).bgCls" class="rounded-xl p-3 text-center">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Temperature</p>
                                <p :class="tempStatus(latestVital.temperature).textCls" class="mt-1 text-xl font-bold leading-tight">{{ latestVital.temperature }}</p>
                                <p class="text-[10px] text-gray-400">°C</p>
                                <span :class="tempStatus(latestVital.temperature).textCls" class="mt-1 block text-[10px] font-semibold uppercase">{{ tempStatus(latestVital.temperature).label }}</span>
                            </div>
                            <div v-if="latestVital.oxygen_saturation" :class="spo2Status(latestVital.oxygen_saturation).bgCls" class="rounded-xl p-3 text-center">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">SpO₂</p>
                                <p :class="spo2Status(latestVital.oxygen_saturation).textCls" class="mt-1 text-xl font-bold leading-tight">{{ latestVital.oxygen_saturation }}%</p>
                                <p class="text-[10px] text-gray-400">oxygen</p>
                                <span :class="spo2Status(latestVital.oxygen_saturation).textCls" class="mt-1 block text-[10px] font-semibold uppercase">{{ spo2Status(latestVital.oxygen_saturation).label }}</span>
                            </div>
                            <div v-if="latestVital.weight" class="rounded-xl bg-sky-50 p-3 text-center dark:bg-sky-900/20">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Weight</p>
                                <p class="mt-1 text-xl font-bold leading-tight text-sky-600 dark:text-sky-400">{{ latestVital.weight }}</p>
                                <p class="text-[10px] text-gray-400">kg</p>
                                <span v-if="calcBMI(latestVital.weight, latestVital.height)" class="mt-1 block text-[10px] font-semibold text-sky-500 dark:text-sky-400">BMI {{ calcBMI(latestVital.weight, latestVital.height) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Header + Record button -->
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Vitals History</p>
                        <button v-if="can('vitals.manage')" @click="openNewVital" class="flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-orange-700">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Record Vitals
                        </button>
                    </div>

                    <!-- Vitals form -->
                    <form v-if="showVitalForm && can('vitals.manage')" @submit.prevent="submitVital" class="mb-5 rounded-2xl border border-orange-200 bg-orange-50 p-5 dark:border-orange-800 dark:bg-orange-900/20 space-y-4">
                        <h3 class="text-sm font-semibold text-orange-800 dark:text-orange-200">{{ editingVitalId ? 'Edit Vitals Record' : 'New Vitals Entry' }}</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Date &amp; Time <span class="text-red-500">*</span></label>
                            <input
                                v-model="vitalForm.recorded_at"
                                type="datetime-local"
                                required
                                :class="vitalForm.errors.recorded_at ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100"
                            />
                        </div>
                        <!-- BP + HR -->
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Blood Pressure (mmHg)</label>
                                <div class="flex items-start gap-2">
                                    <div class="flex-1">
                                        <input v-model="vitalForm.blood_pressure_systolic" type="number" min="40" max="300" placeholder="Systolic"
                                            :class="vitalForm.errors.blood_pressure_systolic ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                            class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                                        <p class="mt-0.5 text-center text-[10px] text-gray-400">systolic</p>
                                    </div>
                                    <span class="mt-2 font-bold text-gray-400">/</span>
                                    <div class="flex-1">
                                        <input v-model="vitalForm.blood_pressure_diastolic" type="number" min="20" max="200" placeholder="Diastolic"
                                            :class="vitalForm.errors.blood_pressure_diastolic ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                            class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                                        <p class="mt-0.5 text-center text-[10px] text-gray-400">diastolic</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Heart Rate (bpm)</label>
                                <input v-model="vitalForm.heart_rate" type="number" min="20" max="300" placeholder="e.g. 72"
                                    :class="vitalForm.errors.heart_rate ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                            </div>
                        </div>
                        <!-- Temp + SpO2 + Weight + Height -->
                        <div class="grid gap-3 sm:grid-cols-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Temp (°C)</label>
                                <input v-model="vitalForm.temperature" type="number" min="30" max="45" step="0.1" placeholder="36.5"
                                    :class="vitalForm.errors.temperature ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">SpO₂ (%)</label>
                                <input v-model="vitalForm.oxygen_saturation" type="number" min="50" max="100" step="0.1" placeholder="98"
                                    :class="vitalForm.errors.oxygen_saturation ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Weight (kg)</label>
                                <input v-model="vitalForm.weight" type="number" min="1" max="500" step="0.1" placeholder="65.0"
                                    :class="vitalForm.errors.weight ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Height (cm)</label>
                                <input v-model="vitalForm.height" type="number" min="30" max="300" step="0.1" placeholder="170"
                                    :class="vitalForm.errors.height ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                            </div>
                        </div>
                        <!-- Notes -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Notes</label>
                            <textarea v-model="vitalForm.notes" rows="2" placeholder="Additional observations…"
                                :class="vitalForm.errors.notes ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none focus:ring-1 focus:ring-orange-100" />
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" :disabled="vitalForm.processing" class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50">
                                {{ editingVitalId ? 'Update' : 'Save Vitals' }}
                            </button>
                            <button type="button" @click="showVitalForm = false" class="rounded-xl border border-gray-200 px-5 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                                Cancel
                            </button>
                        </div>
                    </form>

                    <!-- Empty state -->
                    <div v-if="(patient.vitals?.length ?? 0) === 0 && !showVitalForm" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 text-center dark:border-gray-700 dark:bg-gray-900">
                        <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">No vitals recorded yet</p>
                        <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Click "Record Vitals" to add the first entry</p>
                    </div>

                    <!-- Vitals history -->
                    <div class="space-y-3">
                        <div v-for="vital in patient.vitals" :key="vital.id"
                            class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ formatVitalDate(vital.recorded_at) }}</p>
                                <div class="flex shrink-0 gap-1.5">
                                    <button v-if="can('vitals.manage')" @click="openEditVital(vital)" class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-orange-300 hover:text-orange-600 dark:border-gray-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button v-if="can('vitals.manage')" @click="deleteVital(vital)" class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-red-300 hover:text-red-500 dark:border-gray-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2.5 flex flex-wrap gap-2">
                                <span v-if="vital.blood_pressure_systolic"
                                    :class="[bpStatus(vital.blood_pressure_systolic, vital.blood_pressure_diastolic).textCls, bpStatus(vital.blood_pressure_systolic, vital.blood_pressure_diastolic).bgCls]"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold">
                                    BP {{ vital.blood_pressure_systolic }}/{{ vital.blood_pressure_diastolic }} mmHg
                                    <span class="ml-1 opacity-70">({{ bpStatus(vital.blood_pressure_systolic, vital.blood_pressure_diastolic).label }})</span>
                                </span>
                                <span v-if="vital.heart_rate"
                                    :class="[hrStatus(vital.heart_rate).textCls, hrStatus(vital.heart_rate).bgCls]"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold">
                                    HR {{ vital.heart_rate }} bpm
                                </span>
                                <span v-if="vital.temperature"
                                    :class="[tempStatus(vital.temperature).textCls, tempStatus(vital.temperature).bgCls]"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold">
                                    {{ vital.temperature }}°C · {{ tempStatus(vital.temperature).label }}
                                </span>
                                <span v-if="vital.oxygen_saturation"
                                    :class="[spo2Status(vital.oxygen_saturation).textCls, spo2Status(vital.oxygen_saturation).bgCls]"
                                    class="rounded-lg px-2.5 py-1 text-xs font-semibold">
                                    SpO₂ {{ vital.oxygen_saturation }}%
                                </span>
                                <span v-if="vital.weight"
                                    class="rounded-lg bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-600 dark:bg-sky-900/20 dark:text-sky-400">
                                    {{ vital.weight }} kg<span v-if="vital.height"> · BMI {{ calcBMI(vital.weight, vital.height) }}</span>
                                </span>
                            </div>
                            <p v-if="vital.notes" class="mt-2 text-xs italic text-gray-500 dark:text-gray-400">{{ vital.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- DIAGNOSES TAB -->
                <div v-else-if="activeTab === 'diagnoses'">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Diagnosis History</p>
                        <button v-if="isOwner" @click="openNewDiag" class="flex items-center gap-1.5 rounded-xl bg-orange-600 px-4 py-2 text-xs font-semibold text-white hover:bg-orange-700 transition">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Diagnosis
                        </button>
                    </div>

                    <!-- Diagnosis form -->
                    <form v-if="showDiagForm && isOwner" @submit.prevent="submitDiag" class="mb-4 rounded-2xl border border-orange-200 bg-orange-50 p-5 dark:border-orange-800 dark:bg-orange-900/20 space-y-3">
                        <h3 class="text-sm font-semibold text-orange-800 dark:text-orange-200">{{ editingDiagId ? 'Edit Diagnosis' : 'New Diagnosis' }}</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Diagnosis Title <span class="text-red-500">*</span></label>
                            <input
                                v-model="diagForm.title"
                                type="text"
                                required
                                placeholder="e.g. Type 2 Diabetes, Acute Pharyngitis…"
                                :class="diagForm.errors.title
                                    ? 'border-red-400 focus:border-red-400 focus:ring-1 focus:ring-red-100 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 focus:ring-1 focus:ring-orange-100 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="diagForm.errors.title" class="mt-1 text-xs text-red-500">{{ diagForm.errors.title }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Symptoms</label>
                                <textarea
                                    v-model="diagForm.symptoms"
                                    rows="3"
                                    placeholder="Describe symptoms…"
                                    :class="diagForm.errors.symptoms
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                />
                                <p v-if="diagForm.errors.symptoms" class="mt-1 text-xs text-red-500">{{ diagForm.errors.symptoms }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Clinical Notes</label>
                                <textarea
                                    v-model="diagForm.description"
                                    rows="3"
                                    placeholder="Detailed clinical notes…"
                                    :class="diagForm.errors.description
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                />
                                <p v-if="diagForm.errors.description" class="mt-1 text-xs text-red-500">{{ diagForm.errors.description }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Treatment Plan</label>
                            <textarea
                                v-model="diagForm.treatment"
                                rows="2"
                                placeholder="Treatment plan, medications, lifestyle advice…"
                                :class="diagForm.errors.treatment
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full resize-none rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="diagForm.errors.treatment" class="mt-1 text-xs text-red-500">{{ diagForm.errors.treatment }}</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Follow-up Date</label>
                                <input
                                    v-model="diagForm.follow_up_date"
                                    type="date"
                                    :class="diagForm.errors.follow_up_date
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                />
                                <p v-if="diagForm.errors.follow_up_date" class="mt-1 text-xs text-red-500">{{ diagForm.errors.follow_up_date }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Linked Appointment</label>
                                <select
                                    v-model="diagForm.appointment_id"
                                    :class="diagForm.errors.appointment_id
                                        ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                        : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                                >
                                    <option :value="null">— None —</option>
                                    <option v-for="a in confirmedAppts" :key="a.id" :value="a.id">
                                        {{ a.reference }} — {{ formatDate(a.appointment_date) }}
                                    </option>
                                </select>
                                <p v-if="diagForm.errors.appointment_id" class="mt-1 text-xs text-red-500">{{ diagForm.errors.appointment_id }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" :disabled="diagForm.processing" class="rounded-xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-700 disabled:opacity-50 transition">
                                {{ editingDiagId ? 'Update' : 'Save Diagnosis' }}
                            </button>
                            <button type="button" @click="showDiagForm = false" class="rounded-xl border border-gray-200 px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 transition">
                                Cancel
                            </button>
                        </div>
                    </form>

                    <!-- Diagnosis list -->
                    <div v-if="(patient.diagnoses?.length ?? 0) === 0 && !showDiagForm" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">No diagnoses recorded yet</p>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="diag in patient.diagnoses"
                            :key="diag.id"
                            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ diag.title }}</h3>
                                        <span v-if="diag.follow_up_date" class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                            Follow-up: {{ formatDate(diag.follow_up_date) }}
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ formatDate(diag.created_at) }}
                                        <span v-if="diag.appointment"> · {{ diag.appointment.reference }}</span>
                                    </p>
                                </div>
                                <div class="flex shrink-0 gap-2">
                                    <Link
                                        v-if="isOwner"
                                        :href="`/doctor/patients/${patient.id}/prescriptions/create?diagnosis_id=${diag.id}`"
                                        class="flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                                    >
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                        </svg>
                                        Rx
                                    </Link>
                                    <button v-if="isOwner" @click="openEditDiag(diag)" class="rounded-lg border border-gray-200 p-1.5 text-gray-400 hover:border-orange-300 hover:text-orange-600 transition dark:border-gray-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button v-if="isOwner" @click="deleteDiag(diag)" class="rounded-lg border border-gray-200 p-1.5 text-gray-400 hover:border-red-300 hover:text-red-500 transition dark:border-gray-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 grid gap-2 sm:grid-cols-2 text-xs">
                                <div v-if="diag.symptoms" class="rounded-lg bg-orange-50 p-3 dark:bg-orange-900/20">
                                    <p class="font-semibold text-orange-700 dark:text-orange-400 mb-1">Symptoms</p>
                                    <p class="text-orange-700 dark:text-orange-300 whitespace-pre-wrap">{{ diag.symptoms }}</p>
                                </div>
                                <div v-if="diag.description" class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                                    <p class="font-semibold text-blue-700 dark:text-blue-400 mb-1">Clinical Notes</p>
                                    <p class="text-blue-700 dark:text-blue-300 whitespace-pre-wrap">{{ diag.description }}</p>
                                </div>
                                <div v-if="diag.treatment" class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20 sm:col-span-2">
                                    <p class="font-semibold text-green-700 dark:text-green-400 mb-1">Treatment</p>
                                    <p class="text-green-700 dark:text-green-300 whitespace-pre-wrap">{{ diag.treatment }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRESCRIPTIONS TAB -->
                <div v-else-if="activeTab === 'prescriptions'">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Prescriptions</p>
                        <Link
                            v-if="isOwner"
                            :href="`/doctor/patients/${patient.id}/prescriptions/create`"
                            class="flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            New Prescription
                        </Link>
                    </div>

                    <div v-if="(patient.prescriptions?.length ?? 0) === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <svg class="h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">No prescriptions yet</p>
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="rx in patient.prescriptions"
                            :key="rx.id"
                            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ rx.reference }}</p>
                                    <p class="text-xs text-gray-400">{{ formatDate(rx.created_at) }}
                                        <span v-if="rx.diagnosis"> · {{ rx.diagnosis.title }}</span>
                                    </p>
                                </div>
                                <Link
                                    :href="`/doctor/prescriptions/${rx.id}`"
                                    class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-600 hover:border-orange-300 hover:text-orange-600 transition dark:border-gray-700 dark:text-gray-400"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    View &amp; Print
                                </Link>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span
                                    v-for="med in rx.medications"
                                    :key="med.name"
                                    class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                                >
                                    {{ med.name }} {{ med.dosage }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- APPOINTMENTS TAB -->
                <div v-else-if="activeTab === 'appointments'">
                    <p class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-200">Appointment History</p>
                    <div v-if="appointments.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <p class="text-sm text-gray-500">No appointments found.</p>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="appt in appointments"
                            :key="appt.id"
                            class="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
                        >
                            <div class="shrink-0 text-center">
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ new Date(appt.appointment_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short' }) }}</p>
                                <p class="text-xl font-bold text-gray-800 dark:text-gray-100 leading-tight">{{ new Date(appt.appointment_date + 'T00:00:00').getDate() }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ appt.reference }}</p>
                                <p class="text-xs text-gray-400">{{ appt.appointment_time?.slice(0, 5) }} · {{ appt.reason ?? 'No reason specified' }}</p>
                            </div>
                            <span :class="statusConfig[appt.status]?.cls" class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium">
                                {{ statusConfig[appt.status]?.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- RECORDS TAB -->
                <div v-else-if="activeTab === 'records'">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">Patient Records</p>
                        <button
                            v-if="can('records.manage')"
                            @click="showRecordForm = !showRecordForm"
                            class="flex items-center gap-2 rounded-xl bg-orange-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-orange-700"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ showRecordForm ? 'Cancel' : 'Upload Record' }}
                        </button>
                    </div>

                    <!-- Upload form -->
                    <form
                        v-if="showRecordForm && can('records.manage')"
                        @submit.prevent="submitRecord"
                        class="mb-4 rounded-2xl border border-orange-100 bg-white p-5 shadow-sm dark:border-orange-900/30 dark:bg-gray-900 space-y-3"
                    >
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                File <span class="text-red-500">*</span>
                                <span class="ml-1 font-normal text-gray-400">(PDF, image, Word, TXT — max 10 MB)</span>
                            </label>
                            <input
                                type="file"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.txt"
                                @change="onFileChange"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-orange-700 hover:file:bg-orange-100"
                            />
                            <p v-if="recordForm.errors.file" class="mt-1 text-xs text-red-500">{{ recordForm.errors.file }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Label (optional)</label>
                            <input
                                v-model="recordForm.name"
                                type="text"
                                placeholder="e.g. Lab Result June 2026"
                                :class="recordForm.errors.name
                                    ? 'border-red-400 focus:border-red-400 dark:border-red-500'
                                    : 'border-gray-200 focus:border-orange-400 dark:border-gray-700'"
                                class="w-full rounded-lg border px-3 py-2 text-sm dark:bg-gray-800 dark:text-gray-100 outline-none"
                            />
                            <p v-if="recordForm.errors.name" class="mt-1 text-xs text-red-500">{{ recordForm.errors.name }}</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="recordForm.processing || !recordForm.file"
                            class="w-full rounded-xl bg-orange-600 py-2 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                        >
                            {{ recordForm.processing ? 'Uploading…' : 'Upload' }}
                        </button>
                    </form>

                    <!-- Records list -->
                    <div v-if="(patient.records?.length ?? 0) === 0 && !showRecordForm" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-white py-16 dark:border-gray-700 dark:bg-gray-900 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-3 text-sm text-gray-500">No records uploaded yet.</p>
                        <button v-if="can('records.manage')" @click="showRecordForm = true" class="mt-2 text-xs font-semibold text-orange-600 hover:underline dark:text-orange-400">+ Upload first record</button>
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="rec in patient.records"
                            :key="rec.id"
                            class="flex items-center gap-3 rounded-xl border border-gray-100 bg-white p-4 dark:border-gray-800 dark:bg-gray-900"
                        >
                            <span class="text-2xl leading-none select-none">{{ fileIcon(rec.mime_type) }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ rec.name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ rec.original_name }} · {{ formatBytes(rec.file_size) }} · {{ formatDate(rec.created_at) }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a
                                    :href="`/doctor/patients/${patient.id}/records/${rec.id}/download`"
                                    class="flex items-center gap-1 rounded-lg bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-600 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download
                                </a>
                                <button
                                    v-if="can('records.manage')"
                                    @click="deleteRecord(rec)"
                                    class="flex items-center gap-1 rounded-lg bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/40"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DoctorLayout>

    <!-- Transfer Patient Modal -->
    <Teleport to="body">
        <div
            v-if="showTransferModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showTransferModal = false"
        >
            <div class="w-full max-w-md rounded-2xl bg-white shadow-xl dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Send Patient Copy</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Select a doctor to receive a copy of <span class="font-semibold text-gray-700 dark:text-gray-300">{{ patient.name }}</span>'s records</p>
                    </div>
                    <button @click="showTransferModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Search -->
                    <div>
                        <input
                            v-model="transferSearch"
                            type="text"
                            placeholder="Search by name or specialization…"
                            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-400 focus:ring-1 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        />
                    </div>

                    <!-- Doctor list -->
                    <div class="max-h-60 overflow-y-auto space-y-1.5 pr-1">
                        <p v-if="filteredDoctors.length === 0" class="py-6 text-center text-sm text-gray-400 dark:text-gray-500">No doctors found.</p>
                        <label
                            v-for="doc in filteredDoctors"
                            :key="doc.id"
                            class="flex cursor-pointer items-center gap-3 rounded-xl border px-3.5 py-3 transition"
                            :class="transferForm.target_doctor_id === doc.id
                                ? 'border-indigo-400 bg-indigo-50 dark:border-indigo-600 dark:bg-indigo-900/30'
                                : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800'"
                        >
                            <input
                                type="radio"
                                :value="doc.id"
                                v-model="transferForm.target_doctor_id"
                                class="accent-indigo-600"
                            />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate dark:text-gray-100">Dr. {{ doc.name }}</p>
                                <p v-if="doc.specialization?.length" class="text-xs text-gray-400 truncate dark:text-gray-500">
                                    {{ doc.specialization.join(', ') }}
                                </p>
                            </div>
                            <svg v-if="transferForm.target_doctor_id === doc.id" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    </div>

                    <p v-if="transferForm.errors.target_doctor_id" class="text-xs text-red-500">{{ transferForm.errors.target_doctor_id }}</p>

                    <div class="flex gap-2 pt-1">
                        <button
                            type="button"
                            @click="submitTransfer"
                            :disabled="!transferForm.target_doctor_id || transferForm.processing"
                            class="flex-1 rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ transferForm.processing ? 'Sending…' : 'Send Copy' }}
                        </button>
                        <button
                            type="button"
                            @click="showTransferModal = false"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
