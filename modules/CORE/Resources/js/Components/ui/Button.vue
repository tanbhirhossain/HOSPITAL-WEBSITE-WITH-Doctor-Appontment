<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { cn } from '../../Utils/cn';
import { Loader2 } from 'lucide-vue-next';
import { computed, useAttrs } from 'vue';

type Variant = 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' | 'success' | 'subtle';
type Size = 'xs' | 'sm' | 'md' | 'lg' | 'icon' | 'icon-sm';

const props = withDefaults(
    defineProps<{
        variant?: Variant;
        size?: Size;
        loading?: boolean;
        disabled?: boolean;
        block?: boolean;
        type?: 'button' | 'submit' | 'reset';
        /** When set the button renders as an Inertia `<Link>` instead. */
        href?: string;
    }>(),
    {
        variant: 'primary',
        size: 'md',
        loading: false,
        disabled: false,
        block: false,
        type: 'button',
        href: undefined,
    },
);

const attrs: Record<string, unknown> = useAttrs();

const VARIANTS: Record<Variant, string> = {
    primary:
        'bg-brand-600 text-white shadow-sm hover:bg-brand-700 active:bg-brand-800 focus-visible:ring-brand-500/40 disabled:hover:bg-brand-600',
    secondary:
        'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80 focus-visible:ring-ring/40',
    outline:
        'border border-border bg-background shadow-sm hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/30',
    ghost: 'text-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/30',
    danger:
        'bg-red-600 text-white shadow-sm hover:bg-red-700 active:bg-red-800 focus-visible:ring-red-500/40 disabled:hover:bg-red-600',
    success:
        'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:bg-emerald-800 focus-visible:ring-emerald-500/40 disabled:hover:bg-emerald-600',
    subtle:
        'bg-brand-50 text-brand-700 hover:bg-brand-100 focus-visible:ring-brand-500/30 dark:bg-brand-950/40 dark:text-brand-300 dark:hover:bg-brand-950/60',
};

const SIZES: Record<Size, string> = {
    xs: 'h-7 gap-1 rounded-md px-2 text-xs',
    sm: 'h-8 gap-1.5 rounded-md px-3 text-xs',
    md: 'h-9 gap-2 rounded-lg px-3.5 text-sm',
    lg: 'h-10 gap-2 rounded-lg px-5 text-sm',
    icon: 'size-9 rounded-lg',
    'icon-sm': 'size-8 rounded-md',
};

const classes = computed(() =>
    cn(
        'inline-flex select-none items-center justify-center whitespace-nowrap font-medium transition-all duration-150',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 focus-visible:ring-offset-background',
        'disabled:pointer-events-none disabled:opacity-55',
        VARIANTS[props.variant],
        SIZES[props.size],
        props.block && 'w-full',
        attrs.class as string,
    ),
);
</script>

<template>
    <Link v-if="href" :href="href" :class="classes">
        <slot />
    </Link>

    <button v-else :type="type" :class="classes" :disabled="disabled || loading">
        <Loader2 v-if="loading" class="size-4 shrink-0 animate-spin" />
        <slot />
    </button>
</template>
