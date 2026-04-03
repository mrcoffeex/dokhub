<script setup lang="ts">
import type { Doctor } from '@/types';
import ClinicMapView from '@/components/ClinicMapView.vue';
import { useClipboard } from '@vueuse/core';
import { computed } from 'vue';
import { DialogClose } from '@/components/ui/dialog';
import { X } from 'lucide-vue-next';

const props = defineProps<{ doctor: Doctor }>();

const { copy, copied } = useClipboard();
function handleCopyEmail() {
    if (props.doctor.email) copy(props.doctor.email);
}

function formatTime(time: string): string {
    const [hStr, mStr] = (time || '').split(':');
    const h = Number(hStr || 0);
    const m = Number(mStr || 0);
    const ampm = h < 12 ? 'AM' : 'PM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

const statusLabel = computed(() => {
    const s = (props.doctor.status || 'pending').toString();
    return s.charAt(0).toUpperCase() + s.slice(1);
});
</script>

<template>
    <div class="w-full bg-white dark:bg-gray-900">
        <!-- Modal Header -->
        <header class="sticky top-0 z-30 border-b border-gray-100 bg-white/95 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900/95">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 sm:p-5 pr-12 sm:pr-0">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="h-12 w-12 sm:h-16 sm:w-16 rounded-lg overflow-hidden bg-gradient-to-br from-orange-500 to-indigo-600 flex items-center justify-center text-white text-lg sm:text-xl font-bold flex-shrink-0">
                        <img v-if="props.doctor.avatar_url" :src="props.doctor.avatar_url" :alt="props.doctor.name" class="h-full w-full object-cover" />
                        <span v-else>{{ props.doctor.name?.charAt(0)?.toUpperCase() }}</span>
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">Dr. {{ props.doctor.name }}</h2>
                            <span
                                v-if="props.doctor.status"
                                class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="props.doctor.status === 'approved'
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400'
                                    : props.doctor.status === 'suspended'
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                            >{{ statusLabel }}</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ props.doctor.email ?? '—' }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <span v-for="s in props.doctor.specialization ?? []" :key="s" class="inline-block rounded-full bg-orange-100 px-2 py-0.5 text-xs font-medium text-orange-700 dark:bg-orange-900/30 dark:text-orange-300">{{ s }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex w-full sm:w-auto flex-col sm:flex-row gap-2">
                        <a :href="`/admin/doctors/${props.doctor.slug}/edit`" class="w-full sm:w-auto text-center rounded-lg bg-orange-600 px-3 py-2 text-sm font-medium text-white hover:bg-orange-700">
                            Edit
                        </a>
                        <a :href="`/doctors/${props.doctor.slug}`" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto text-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            Open public
                        </a>
                        <button @click="handleCopyEmail" class="w-full sm:w-auto text-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                            {{ copied ? 'Copied' : 'Copy email' }}
                        </button>
                    </div>

                    <DialogClose class="absolute right-3 top-3 sm:static sm:ml-2 rounded-md p-2 lg:mr-3 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 z-40" aria-label="Close preview">
                        <X class="w-4 h-4" />
                    </DialogClose>
                </div>
            </div>
        </header>

        <!-- Body: scrollable content -->
        <div class="p-6 max-h-[calc(100vh-64px)] sm:max-h-[70vh] overflow-y-auto grid gap-6 grid-cols-1 lg:grid-cols-5 bg-gray-50 dark:bg-gray-950">
            <main class="lg:col-span-3 space-y-6">
                <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Consultation Fee</p>
                            <p class="mt-1 text-2xl font-bold text-orange-600">₱{{ props.doctor.consultation_fee ?? '0.00' }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="props.doctor.bio" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">About</h3>
                    <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">{{ props.doctor.bio }}</p>
                </div>

                <div v-if="props.doctor.schedules && props.doctor.schedules.length" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Weekly Schedule</h3>
                    <div class="mt-3 space-y-1.5">
                        <div v-for="s in props.doctor.schedules.filter(s => s.is_active)" :key="s.id" class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 dark:text-gray-300">{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][s.day_of_week] }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ formatTime(s.start_time) }} – {{ formatTime(s.end_time) }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="props.doctor.latitude && props.doctor.longitude" class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Clinic Location</h3>
                    </div>
                    <div class="p-3">
                        <ClinicMapView :lat="props.doctor.latitude" :lng="props.doctor.longitude" :doctor-name="props.doctor.name" :address="props.doctor.location" />
                    </div>
                </div>
            </main>

            <aside class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Contact</p>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <div><strong class="text-gray-700 dark:text-gray-300">Email:</strong> <span class="text-gray-700 dark:text-gray-300">{{ props.doctor.email ?? '—' }}</span></div>
                        <div class="mt-1"><strong class="text-gray-700 dark:text-gray-300">Phone:</strong> <span class="text-gray-700 dark:text-gray-300">{{ props.doctor.phone ?? '—' }}</span></div>
                        <div v-if="props.doctor.location" class="mt-1"><strong class="text-gray-700 dark:text-gray-300">Address:</strong> {{ props.doctor.location }}</div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Accepted Insurance</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span v-if="!(props.doctor.insurance && props.doctor.insurance.length)" class="text-sm text-gray-400 dark:text-gray-500">—</span>
                        <span v-for="ins in props.doctor.insurance ?? []" :key="ins" class="inline-block rounded-full bg-gray-100 px-3 py-0.5 text-sm font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ ins }}</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</template>
