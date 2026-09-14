<script setup lang="ts">
import type { Breadcrumb } from '../../Types';

defineProps<{
    title: string;
    description?: string;
    breadcrumbs?: Breadcrumb[];
}>();
</script>

<template>
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <nav v-if="breadcrumbs?.length" aria-label="Breadcrumb" class="mb-1.5">
                <ol class="flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                    <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center gap-1.5">
                        <span v-if="index > 0" aria-hidden="true">/</span>
                        <span :class="index === breadcrumbs.length - 1 && 'font-medium text-foreground'">
                            {{ crumb.title }}
                        </span>
                    </li>
                </ol>
            </nav>

            <h1 class="text-xl font-semibold tracking-tight text-foreground sm:text-2xl">{{ title }}</h1>
            <p v-if="description" class="mt-1 text-sm text-muted-foreground">{{ description }}</p>
        </div>

        <div v-if="$slots.actions" class="flex shrink-0 flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </header>
</template>
