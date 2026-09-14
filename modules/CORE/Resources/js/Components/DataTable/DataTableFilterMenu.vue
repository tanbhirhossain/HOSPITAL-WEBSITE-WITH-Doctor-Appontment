<script setup lang="ts">
import Button from '../ui/Button.vue';
import Dropdown from '../ui/Dropdown.vue';
import { cn } from '../../Utils/cn';
import { Check, ChevronDown, ListFilter } from 'lucide-vue-next';
import { computed } from 'vue';
import type { FilterOption } from '../../Types';

/**
 * Faceted filter — a checkbox list inside a dropdown, the pattern users
 * already know from Linear / GitHub issue lists.
 */
const props = defineProps<{
    label: string;
    options: FilterOption[];
    selected: Array<string | number>;
}>();

const emit = defineEmits<{
    toggle: [value: string | number];
    clear: [];
}>();

const selectedSet = computed(() => new Set(props.selected));

const triggerLabel = computed(() => {
    if (props.selected.length === 0) {
        return props.label;
    }

    if (props.selected.length === 1) {
        const match = props.options.find((option) => option.value === props.selected[0]);

        return match?.label ?? props.label;
    }

    return `${props.label} · ${props.selected.length}`;
});
</script>

<template>
    <Dropdown align="left" width="w-56">
        <template #trigger="{ toggle, open }">
            <Button variant="outline" size="sm" @click="toggle">
                <ListFilter class="size-3.5" />
                {{ triggerLabel }}
                <ChevronDown v-if="selected.length === 0" class="size-3.5 opacity-60" />
                <span
                    v-else
                    class="ml-0.5 grid size-4 place-items-center rounded-full bg-brand-600 text-[10px] font-semibold text-white"
                >
                    {{ selected.length }}
                </span>
                <span class="sr-only" :data-open="open" />
            </Button>
        </template>

        <div class="max-h-64 overflow-y-auto scrollbar-thin">
            <button
                v-for="option in options"
                :key="option.value"
                type="button"
                class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-sm text-foreground transition hover:bg-accent"
                @click="emit('toggle', option.value)"
            >
                <span
                    :class="
                        cn(
                            'grid size-4 shrink-0 place-items-center rounded border transition',
                            selectedSet.has(option.value) ? 'border-brand-600 bg-brand-600 text-white' : 'border-input',
                        )
                    "
                >
                    <Check v-if="selectedSet.has(option.value)" class="size-3" />
                </span>

                <span class="min-w-0 flex-1 truncate">{{ option.label }}</span>

                <span v-if="option.count !== undefined" class="text-xs text-muted-foreground">{{ option.count }}</span>
            </button>

            <p v-if="options.length === 0" class="px-2.5 py-3 text-center text-xs text-muted-foreground">
                No options available
            </p>
        </div>

        <div v-if="selected.length" class="mt-1 border-t border-border pt-1">
            <button
                type="button"
                class="w-full cursor-pointer rounded-lg px-2.5 py-1.5 text-left text-sm text-muted-foreground transition hover:bg-accent hover:text-foreground"
                @click="emit('clear')"
            >
                Clear selection
            </button>
        </div>
    </Dropdown>
</template>
