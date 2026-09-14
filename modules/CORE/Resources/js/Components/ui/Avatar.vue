<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name?: string | null;
        src?: string | null;
        size?: 'xs' | 'sm' | 'md' | 'lg';
        square?: boolean;
    }>(),
    { name: null, src: null, size: 'md', square: false },
);

const SIZES = {
    xs: 'size-6 text-[10px]',
    sm: 'size-8 text-xs',
    md: 'size-9 text-xs',
    lg: 'size-12 text-base',
};

/** Deterministic colour so the same person always looks the same. */
const PALETTE = [
    'bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300',
    'bg-violet-100 text-violet-700 dark:bg-violet-950 dark:text-violet-300',
    'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
];

const initials = computed(() => {
    if (!props.name) {
        return '?';
    }

    return props.name
        .split(' ')
        .filter(Boolean)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .slice(0, 2)
        .join('');
});

const paletteClass = computed(() => {
    const seed = (props.name ?? '').split('').reduce((total, char) => total + char.charCodeAt(0), 0);

    return PALETTE[seed % PALETTE.length];
});
</script>

<template>
    <span
        :class="
            cn(
                'relative inline-grid shrink-0 place-items-center overflow-hidden font-semibold',
                square ? 'rounded-lg' : 'rounded-full',
                SIZES[size],
                paletteClass,
            )
        "
    >
        <img v-if="src" :src="src" :alt="name ?? ''" class="size-full object-cover" loading="lazy" />
        <span v-else>{{ initials }}</span>
    </span>
</template>
