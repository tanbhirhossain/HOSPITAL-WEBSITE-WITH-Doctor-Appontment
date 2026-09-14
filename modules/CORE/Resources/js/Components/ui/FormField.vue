<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { computed, useId } from 'vue';

/**
 * Label + control + hint/error wrapper. The control receives an `id` through
 * the default slot so every input in the panel is properly associated.
 */
const props = withDefaults(
    defineProps<{
        label?: string;
        hint?: string;
        error?: string;
        required?: boolean;
        class?: string;
    }>(),
    { required: false },
);

const id = useId();

const describedBy = computed(() => (props.error || props.hint ? `${id}-description` : undefined));
</script>

<template>
    <div :class="cn('space-y-1.5', props.class)">
        <label v-if="label" :for="id" class="block text-sm font-medium text-foreground">
            {{ label }}
            <span v-if="required" class="text-red-500">*</span>
        </label>

        <slot :id="id" :described-by="describedBy" :invalid="Boolean(error)" />

        <p
            v-if="error || hint"
            :id="`${id}-description`"
            :class="cn('text-xs leading-snug', error ? 'text-red-600 dark:text-red-400' : 'text-muted-foreground')"
        >
            {{ error || hint }}
        </p>
    </div>
</template>
