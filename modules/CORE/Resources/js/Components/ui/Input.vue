<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { computed, useAttrs } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        invalid?: boolean;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    { modelValue: '', invalid: false, size: 'md' },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
const attrs: Record<string, unknown> = useAttrs();

const SIZES = {
    sm: 'h-8 text-xs',
    md: 'h-9 text-sm',
    lg: 'h-10 text-sm',
};

const classes = computed(() =>
    cn(
        'block w-full rounded-lg border bg-background px-3 text-foreground shadow-sm transition',
        'placeholder:text-muted-foreground/70',
        'focus:outline-none focus:ring-2 focus:ring-offset-0',
        props.invalid
            ? 'border-red-400 focus:border-red-500 focus:ring-red-500/25'
            : 'border-input focus:border-brand-500 focus:ring-brand-500/25',
        'disabled:cursor-not-allowed disabled:bg-muted disabled:opacity-60',
        SIZES[props.size],
        attrs.class as string,
    ),
);

function onInput(event: Event): void {
    emit('update:modelValue', (event.target as HTMLInputElement).value);
}
</script>

<template>
    <input :value="modelValue ?? ''" :class="classes" v-bind="{ ...attrs, class: undefined }" @input="onInput" />
</template>
