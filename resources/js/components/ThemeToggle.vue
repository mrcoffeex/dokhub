<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { computed } from 'vue';

const { appearance, updateAppearance } = useAppearance();

const isDark = computed(() => appearance.value === 'dark');

function toggle() {
    updateAppearance(isDark.value ? 'light' : 'dark');
}
</script>

<template>
    <button
        @click="toggle"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 dark:text-gray-500 dark:hover:bg-gray-800 dark:hover:text-gray-300"
    >
        <!-- Sun icon (shown in dark mode) -->
        <svg v-if="isDark" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8.66-9h-1M4.34 12h-1m15.07-6.07l-.71.71M6.34 17.66l-.71.71m12.73 0l-.71-.71M6.34 6.34l-.71-.71M12 6a6 6 0 100 12A6 6 0 0012 6z" />
        </svg>
        <!-- Moon icon (shown in light mode) -->
        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>
</template>
