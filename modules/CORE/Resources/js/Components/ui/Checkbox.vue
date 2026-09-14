<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: boolean;
        indeterminate?: boolean;
        disabled?: boolean;
        invalid?: boolean;
        label?: string;
        description?: string;
        value?: string | number | boolean;
    }>(),
    { modelValue: false, indeterminate: false, disabled: false, invalid: false },
);

const emit = defineEmits<{ 'update:modelValue': [value: boolean] }>();

/** Distinguish "checked" from the DOM indeterminate state. */
const state = computed(() => (props.indeterminate ? 'indeterminate' : props.modelValue ? 'checked' : 'unchecked'));

const classes = computed(() =>
    cn(
        'grid size-4 shrink-0 place-items-center rounded border transition',
        props.disabled && 'cursor-not-allowed opacity-50',
        props.invalid ? 'border-red-400' : 'border-input',
        state.value === 'unchecked' ? 'bg-background' : 'border-brand-600 bg-brand-600 text-white',
    ),
);
</script>

<template>
    <label class="inline-flex cursor-pointer items-start gap-2.5" :class="disabled && 'cursor-not-allowed'">
        <span class="relative grid size-4 shrink-0 place-items-center" :class="label || description ? 'mt-0.5' : ''">
            <input
                type="checkbox"
                class="peer absolute size-full cursor-pointer opacity-0"
                :checked="modelValue"
                :disabled="disabled"
                @change="emit('update:modelValue', ($event.target as HTMLInputElement).checked)"
            />
            <span :class="classes">
                <svg v-if="state === 'checked'" viewBox="0 0 16 16" class="size-3" fill="none" aria-hidden="true">
                    <path d="M3.5 8.5l3 3 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span v-else-if="state === 'indeterminate'" class="h-0.5 w-2.5 rounded-full bg-current" />
            </span>
        </span>

        <span v-if="label || description" class="min-w-0">
            <span v-if="label" class="block text-sm leading-tight font-medium text-foreground">{{ label }}</span>
            <span v-if="description" class="mt-0.5 block text-xs leading-snug text-muted-foreground">{{ description }}</span>
        </span>
    </label>
</template>
