<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
// Registration is disabled — redirect to login
router.visit('/login');
</script>

<template>
    <Head title="Register" />
</template>
