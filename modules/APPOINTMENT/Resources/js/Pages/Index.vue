<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'APPOINTMENT',
        href: '#',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="APPOINTMENT" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 md:p-6">
            <h1 class="text-2xl font-semibold tracking-normal text-foreground">APPOINTMENT</h1>
        </div>
    </AppLayout>
</template>