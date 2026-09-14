<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { Search, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        class?: string;
    }>(),
    { modelValue: '', placeholder: 'Search…' },
);

const emit = defineEmits<{ 'update:modelValue': [value: string] }>();
const input = ref<HTMLInputElement | null>(null);

function clear(): void {
    emit('update:modelValue', '');
    input.value?.focus();
}

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div :class="cn('relative', props.class)">
        <Search class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />

        <input
            ref="input"
            type="search"
            :value="modelValue"
            :placeholder="placeholder"
            class="h-9 w-full rounded-lg border border-input bg-background pr-9 pl-9 text-sm text-foreground shadow-sm transition placeholder:text-muted-foreground/70 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25 focus:outline-none [&::-webkit-search-cancel-button]:hidden"
            @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />

        <button
            v-if="modelValue"
            type="button"
            class="absolute top-1/2 right-2.5 -translate-y-1/2 rounded p-0.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
            aria-label="Clear search"
            @click="clear"
        >
            <X class="size-3.5" />
        </button>
    </div>
</template>
