<script setup lang="ts">
import { cn } from '../../Utils/cn';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        icon?: unknown;
        compact?: boolean;
    }>(),
    {
        title: 'Nothing here yet',
        description: '',
        compact: false,
    },
);
</script>

<template>
    <div
        :class="
            cn(
                'flex flex-col items-center justify-center text-center',
                compact ? 'gap-2 px-4 py-10' : 'gap-3 px-6 py-16',
            )
        "
    >
        <span
            v-if="icon || $slots.icon"
            class="grid size-11 place-items-center rounded-xl bg-muted text-muted-foreground"
        >
            <slot name="icon">
                <component :is="icon" class="size-5" />
            </slot>
        </span>

        <div class="space-y-1">
            <p class="text-sm font-semibold text-foreground">{{ title }}</p>
            <p v-if="description || $slots.default" class="mx-auto max-w-sm text-sm text-muted-foreground">
                <slot>{{ description }}</slot>
            </p>
        </div>

        <div v-if="$slots.action" class="mt-1">
            <slot name="action" />
        </div>
    </div>
</template>
