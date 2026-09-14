<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { computed, useAttrs } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string | null;
        invalid?: boolean;
        rows?: number;
    }>(),
    { modelValue: '', invalid: false, rows: 3 },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
const attrs: Record<string, unknown> = useAttrs();

const classes = computed(() =>
    cn(
        'block w-full resize-y rounded-lg border bg-background px-3 py-2 text-sm text-foreground shadow-sm transition',
        'placeholder:text-muted-foreground/70 focus:outline-none focus:ring-2',
        props.invalid
            ? 'border-red-400 focus:border-red-500 focus:ring-red-500/25'
            : 'border-input focus:border-brand-500 focus:ring-brand-500/25',
        'disabled:cursor-not-allowed disabled:bg-muted disabled:opacity-60',
        attrs.class as string,
    ),
);
</script>

<template>
    <textarea
        :value="modelValue ?? ''"
        :rows="rows"
        :class="classes"
        v-bind="{ ...attrs, class: undefined }"
        @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
</template>
