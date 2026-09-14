<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        description?: string;
        size?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
        /** Hide the built-in header (for fully custom layouts). */
        bare?: boolean;
    }>(),
    { open: false, size: 'md', bare: false },
);

const emit = defineEmits<{ close: [] }>();

const SIZES = {
    sm: 'max-w-sm',
    md: 'max-w-lg',
    lg: 'max-w-2xl',
    xl: 'max-w-4xl',
    '2xl': 'max-w-6xl',
};

const panelClass = computed(() => cn('w-full', SIZES[props.size]));

function close(): void {
    emit('close');
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape' && props.open) {
        close();
    }
}

watch(
    () => props.open,
    (open) => {
        const body = document.body;

        if (open) {
            body.style.overflow = 'hidden';
            window.addEventListener('keydown', onKeydown);
        } else {
            body.style.overflow = '';
            window.removeEventListener('keydown', onKeydown);
        }
    },
);

onBeforeUnmount(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center sm:items-center">
                <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="close" />

                <div
                    :class="panelClass"
                    class="animate-scale-in relative z-10 max-h-[92vh] overflow-hidden rounded-t-2xl bg-card shadow-2xl ring-1 ring-border sm:rounded-2xl"
                    role="dialog"
                    aria-modal="true"
                >
                    <div
                        v-if="!bare"
                        class="flex items-start justify-between gap-4 border-b border-border px-5 py-4"
                    >
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-foreground">
                                <slot name="title">{{ title }}</slot>
                            </h2>
                            <p v-if="description" class="mt-0.5 text-sm text-muted-foreground">
                                <slot name="description">{{ description }}</slot>
                            </p>
                        </div>

                        <button
                            type="button"
                            class="-m-1.5 rounded-lg p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                            aria-label="Close"
                            @click="close"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <div class="max-h-[calc(92vh-8rem)] overflow-y-auto scrollbar-thin">
                        <slot />
                    </div>

                    <div
                        v-if="$slots.footer"
                        class="flex items-center justify-end gap-2 border-t border-border bg-muted/40 px-5 py-3.5"
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
