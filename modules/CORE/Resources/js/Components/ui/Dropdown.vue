<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { onClickOutside, onKeyStroke } from '@vueuse/core';
import { ref, useSlots } from 'vue';

/**
 * Lightweight anchored menu. Closes on outside click, Escape, or route change
 * (the wrapper unmounts with the row that owns it).
 */
const props = withDefaults(
    defineProps<{
        align?: 'left' | 'right';
        width?: string;
    }>(),
    { align: 'right', width: 'w-44' },
);

const open = ref(false);
const root = ref<HTMLElement | null>(null);
const slots = useSlots();

onClickOutside(root, () => (open.value = false));
onKeyStroke('Escape', () => (open.value = false));

function toggle(): void {
    open.value = !open.value;
}

defineExpose({ close: () => (open.value = false) });
</script>

<template>
    <div ref="root" class="relative inline-flex">
        <slot name="trigger" :open="open" :toggle="toggle" />

        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="scale-95 opacity-0"
            leave-active-class="transition duration-100 ease-in"
            leave-to-class="scale-95 opacity-0"
        >
            <div
                v-if="open"
                :class="
                    cn(
                        'absolute z-40 mt-1 overflow-hidden rounded-xl border border-border bg-popover p-1 shadow-xl',
                        width,
                        align === 'right' ? 'right-0 origin-top-right' : 'left-0 origin-top-left',
                    )
                "
                style="top: 100%"
                role="menu"
                @click="open = false"
            >
                <slot />
            </div>
        </Transition>
    </div>
</template>
