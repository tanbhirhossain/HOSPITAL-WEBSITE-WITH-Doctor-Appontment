<script setup lang="ts">
import { cn } from '../../Utils/cn';

withDefaults(
    defineProps<{
        padded?: boolean;
        class?: string;
    }>(),
    { padded: true },
);
</script>

<template>
    <div :class="cn('rounded-xl border border-border bg-card shadow-sm', $props.class)">
        <div
            v-if="$slots.title || $slots.description || $slots.actions"
            class="flex flex-col gap-1 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="min-w-0">
                <h3 v-if="$slots.title" class="text-sm font-semibold text-foreground">
                    <slot name="title" />
                </h3>
                <p v-if="$slots.description" class="mt-0.5 text-sm text-muted-foreground">
                    <slot name="description" />
                </p>
            </div>
            <div v-if="$slots.actions" class="shrink-0">
                <slot name="actions" />
            </div>
        </div>

        <div :class="padded && 'p-5'">
            <slot />
        </div>

        <div v-if="$slots.footer" class="border-t border-border px-5 py-3.5">
            <slot name="footer" />
        </div>
    </div>
</template>
