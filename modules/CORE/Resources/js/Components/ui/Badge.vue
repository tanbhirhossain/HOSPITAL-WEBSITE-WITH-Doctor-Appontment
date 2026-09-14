<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { computed } from 'vue';

type Tone = 'neutral' | 'brand' | 'success' | 'warning' | 'danger' | 'info' | 'violet';

const props = withDefaults(
    defineProps<{
        tone?: Tone;
        size?: 'sm' | 'md';
        dot?: boolean;
        outline?: boolean;
    }>(),
    { tone: 'neutral', size: 'sm', dot: false, outline: false },
);

const TONES: Record<Tone, { solid: string; outline: string; dot: string }> = {
    neutral: {
        solid: 'bg-muted text-muted-foreground',
        outline: 'border-border text-muted-foreground',
        dot: 'bg-muted-foreground',
    },
    brand: {
        solid: 'bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300',
        outline: 'border-brand-200 text-brand-700 dark:border-brand-900 dark:text-brand-300',
        dot: 'bg-brand-500',
    },
    success: {
        solid: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
        outline: 'border-emerald-200 text-emerald-700 dark:border-emerald-900 dark:text-emerald-300',
        dot: 'bg-emerald-500',
    },
    warning: {
        solid: 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
        outline: 'border-amber-200 text-amber-700 dark:border-amber-900 dark:text-amber-300',
        dot: 'bg-amber-500',
    },
    danger: {
        solid: 'bg-red-50 text-red-700 dark:bg-red-950/50 dark:text-red-300',
        outline: 'border-red-200 text-red-700 dark:border-red-900 dark:text-red-300',
        dot: 'bg-red-500',
    },
    info: {
        solid: 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300',
        outline: 'border-sky-200 text-sky-700 dark:border-sky-900 dark:text-sky-300',
        dot: 'bg-sky-500',
    },
    violet: {
        solid: 'bg-violet-50 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300',
        outline: 'border-violet-200 text-violet-700 dark:border-violet-900 dark:text-violet-300',
        dot: 'bg-violet-500',
    },
};

const classes = computed(() =>
    cn(
        'inline-flex items-center gap-1.5 font-medium whitespace-nowrap',
        props.size === 'sm' ? 'rounded-md px-2 py-0.5 text-xs' : 'rounded-md px-2.5 py-1 text-sm',
        props.outline
            ? cn('border bg-transparent', TONES[props.tone].outline)
            : TONES[props.tone].solid,
    ),
);
</script>

<template>
    <span :class="classes">
        <span v-if="dot" class="size-1.5 shrink-0 rounded-full" :class="TONES[tone].dot" />
        <slot />
    </span>
</template>
