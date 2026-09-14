<script setup lang="ts">
import { cn } from '../../Utils/cn';

const props = withDefaults(
    defineProps<{
        modelValue?: boolean;
        disabled?: boolean;
        label?: string;
        description?: string;
        size?: 'sm' | 'md';
    }>(),
    { modelValue: false, disabled: false, size: 'md' },
);

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>();

const SIZES = {
    sm: { track: 'h-4 w-7', knob: 'size-3', travel: 'translate-x-3' },
    md: { track: 'h-5 w-9', knob: 'size-4', travel: 'translate-x-4' },
};
</script>

<template>
    <label class="inline-flex items-center gap-2.5" :class="disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'">
        <button
            type="button"
            role="switch"
            :aria-checked="modelValue"
            :disabled="disabled"
            :class="
                cn(
                    'relative shrink-0 rounded-full transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-brand-500/40 focus-visible:outline-none',
                    SIZES[size].track,
                    modelValue ? 'bg-brand-600' : 'bg-input',
                )
            "
            @click="emit('update:modelValue', !modelValue)"
        >
            <span
                :class="
                    cn(
                        'absolute top-0.5 left-0.5 rounded-full bg-white shadow-sm transition-transform duration-200',
                        SIZES[size].knob,
                        modelValue ? SIZES[size].travel : 'translate-x-0',
                    )
                "
            />
        </button>

        <span v-if="label || description" class="min-w-0">
            <span v-if="label" class="block text-sm leading-tight font-medium text-foreground">{{ label }}</span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-muted-foreground">{{ description }}</span>
        </span>
    </label>
</template>
