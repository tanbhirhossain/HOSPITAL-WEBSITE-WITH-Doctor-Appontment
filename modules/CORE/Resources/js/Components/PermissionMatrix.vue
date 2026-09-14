<script setup lang="ts">
import Checkbox from './ui/Checkbox.vue';
import SearchInput from './ui/SearchInput.vue';
import { cn } from '../Utils/cn';
import { computed, ref } from 'vue';

/**
 * Grouped permission picker (`resource.action` pairs).
 *
 * Each resource block gets a tri-state "select all" toggle, and the search box
 * filters resources so a 40-permission catalogue stays manageable.
 */
const props = defineProps<{
    matrix: Record<string, string[]>;
    modelValue: string[];
    disabled?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [value: string[]] }>();

const query = ref('');

const selected = computed(() => new Set(props.modelValue));

const groups = computed(() =>
    Object.entries(props.matrix)
        .filter(([resource]) => resource.toLowerCase().includes(query.value.trim().toLowerCase()))
        .sort(([a], [b]) => a.localeCompare(b)),
);

function isGroupFullySelected(actions: string[]): boolean {
    return actions.every((action) => selected.value.has(action));
}

function isGroupPartiallySelected(actions: string[]): boolean {
    return !isGroupFullySelected(actions) && actions.some((action) => selected.value.has(action));
}

function toggleGroup(actions: string[], checked: boolean): void {
    const next = new Set(selected.value);

    actions.forEach((action) => (checked ? next.add(action) : next.delete(action)));

    emit('update:modelValue', [...next]);
}

function toggleOne(action: string, checked: boolean): void {
    const next = new Set(selected.value);

    checked ? next.add(action) : next.delete(action);

    emit('update:modelValue', [...next]);
}

const headline = (value: string) =>
    value
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <SearchInput v-model="query" placeholder="Filter capabilities…" class="max-w-56" />
            <p class="text-xs text-muted-foreground">
                <span class="font-semibold text-foreground">{{ modelValue.length }}</span>
                selected
            </p>
        </div>

        <div class="max-h-80 space-y-2.5 overflow-y-auto scrollbar-thin pr-1">
            <div v-for="[resource, actions] in groups" :key="resource" class="rounded-lg border border-border">
                <div class="flex items-center justify-between gap-3 border-b border-border bg-muted/40 px-3 py-2">
                    <Checkbox
                        :model-value="isGroupFullySelected(actions)"
                        :indeterminate="isGroupPartiallySelected(actions)"
                        :disabled="disabled"
                        :label="headline(resource)"
                        @update:model-value="toggleGroup(actions, $event)"
                    />
                    <span class="text-xs text-muted-foreground">{{ actions.length }}</span>
                </div>

                <div class="grid grid-cols-1 gap-x-4 gap-y-2 p-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Checkbox
                        v-for="action in actions"
                        :key="action"
                        :model-value="selected.has(action)"
                        :disabled="disabled"
                        :label="headline(action.split('.').pop() ?? action)"
                        @update:model-value="toggleOne(action, $event)"
                    />
                </div>
            </div>

            <p v-if="groups.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                No capabilities match your filter.
            </p>
        </div>

        <p :class="cn('text-xs text-muted-foreground')">
            Grant only what this record needs. Roles are additive — a user also inherits permissions from any other role
            they hold.
        </p>
    </div>
</template>
