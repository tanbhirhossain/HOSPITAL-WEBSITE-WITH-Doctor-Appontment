<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { ArrowDown, ArrowUp, ChevronsUpDown } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ColumnAlign, SortDirection } from '../../Types';

const props = defineProps<{
    label: string;
    columnKey: string;
    sortable?: boolean;
    align?: ColumnAlign;
    activeSort?: string;
    direction?: SortDirection;
}>();

const emit = defineEmits<{ sort: [key: string] }>();

const isActive = computed(() => props.activeSort === props.columnKey);

const Icon = computed(() => {
    if (!isActive.value) {
        return ChevronsUpDown;
    }

    return props.direction === 'asc' ? ArrowUp : ArrowDown;
});

const alignClass = computed(() =>
    cn(props.align === 'right' ? 'justify-end text-right' : props.align === 'center' ? 'justify-center text-center' : 'justify-start'),
);
</script>

<template>
    <button
        v-if="sortable"
        type="button"
        :class="
            cn(
                'group -mx-1 flex w-full items-center gap-1.5 rounded px-1 py-0.5 text-xs font-semibold tracking-wide uppercase transition',
                isActive ? 'text-foreground' : 'text-muted-foreground hover:text-foreground',
                alignClass,
            )
        "
        @click="emit('sort', columnKey)"
    >
        <span class="truncate">{{ label }}</span>
        <component
            :is="Icon"
            :class="
                cn(
                    'size-3.5 shrink-0 transition',
                    isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-60',
                )
            "
        />
    </button>

    <span
        v-else
        :class="cn('flex items-center text-xs font-semibold tracking-wide uppercase text-muted-foreground', alignClass)"
    >
        {{ label }}
    </span>
</template>
