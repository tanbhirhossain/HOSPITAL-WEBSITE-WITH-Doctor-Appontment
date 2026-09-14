<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { ChevronDown } from 'lucide-vue-next';
import { computed, useAttrs } from 'vue';
import type { SelectOption } from '../../Types';

const props = withDefaults(
    defineProps<{
        modelValue?: string | number | null;
        options?: SelectOption[];
        invalid?: boolean;
        placeholder?: string;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    {
        modelValue: null,
        options: () => [],
        invalid: false,
        placeholder: 'Select…',
        size: 'md',
    },
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
        'block w-full appearance-none rounded-lg border bg-background pl-3 pr-9 text-foreground shadow-sm transition',
        'focus:outline-none focus:ring-2',
        props.invalid
            ? 'border-red-400 focus:border-red-500 focus:ring-red-500/25'
            : 'border-input focus:border-brand-500 focus:ring-brand-500/25',
        'disabled:cursor-not-allowed disabled:bg-muted disabled:opacity-60',
        SIZES[props.size],
        attrs.class as string,
    ),
);
</script>

<template>
    <div class="relative">
        <select
            :value="modelValue ?? ''"
            :class="classes"
            v-bind="{ ...attrs, class: undefined }"
            @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        >
            <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
            <option v-for="option in options" :key="option.value" :value="option.value" :disabled="option.disabled">
                {{ option.label }}
            </option>
            <slot />
        </select>

        <ChevronDown class="pointer-events-none absolute top-1/2 right-3 size-4 -translate-y-1/2 text-muted-foreground" />
    </div>
</template>
