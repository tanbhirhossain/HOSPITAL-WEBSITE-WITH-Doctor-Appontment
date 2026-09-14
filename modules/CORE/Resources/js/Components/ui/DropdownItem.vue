<script setup lang="ts">
import { cn } from '../../Utils/cn';

withDefaults(
    defineProps<{
        tone?: 'default' | 'danger';
        disabled?: boolean;
        /** Render as an anchor (Inertia <Link>) instead of a button. */
        href?: string;
        as?: 'button' | 'a';
    }>(),
    { tone: 'default', disabled: false, as: 'button' },
);
</script>

<template>
    <component
        :is="as === 'a' ? 'a' : 'button'"
        :type="as === 'a' ? undefined : 'button'"
        :href="href"
        :disabled="disabled"
        role="menuitem"
        :class="
            cn(
                'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-sm transition',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring',
                tone === 'danger'
                    ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40'
                    : 'text-foreground hover:bg-accent',
                disabled && 'pointer-events-none opacity-50',
            )
        "
    >
        <slot />
    </component>
</template>
