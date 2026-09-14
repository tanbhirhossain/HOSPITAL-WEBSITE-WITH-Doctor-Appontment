<script setup lang="ts">
import { pauseToast, resumeToast, toast, useToasts } from '../Composables/useToast';
import { CheckCircle2, Info, TriangleAlert, X, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const { items } = useToasts();

const VARIANTS = {
    success: {
        icon: CheckCircle2,
        ring: 'ring-emerald-500/20',
        bar: 'bg-emerald-500',
        iconClass: 'text-emerald-600 dark:text-emerald-400',
        glow: 'shadow-emerald-500/10',
    },
    error: {
        icon: XCircle,
        ring: 'ring-red-500/20',
        bar: 'bg-red-500',
        iconClass: 'text-red-600 dark:text-red-400',
        glow: 'shadow-red-500/10',
    },
    warning: {
        icon: TriangleAlert,
        ring: 'ring-amber-500/20',
        bar: 'bg-amber-500',
        iconClass: 'text-amber-600 dark:text-amber-400',
        glow: 'shadow-amber-500/10',
    },
    info: {
        icon: Info,
        ring: 'ring-sky-500/20',
        bar: 'bg-sky-500',
        iconClass: 'text-sky-600 dark:text-sky-400',
        glow: 'shadow-sky-500/10',
    },
    default: {
        icon: Info,
        ring: 'ring-border',
        bar: 'bg-foreground/40',
        iconClass: 'text-muted-foreground',
        glow: 'shadow-black/5',
    },
} as const;

const variantOf = (key: keyof typeof VARIANTS) => computed(() => VARIANTS[key]);
</script>

<template>
    <div
        class="pointer-events-none fixed inset-x-0 bottom-0 z-[100] flex flex-col items-center gap-2 p-4 sm:inset-x-auto sm:right-0 sm:top-0 sm:items-end sm:p-6"
        role="region"
        aria-label="Notifications"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-3 opacity-0 sm:translate-x-6"
            leave-active-class="transition duration-200 ease-in absolute"
            leave-to-class="scale-95 opacity-0"
            move-class="transition duration-200"
        >
            <div
                v-for="item in items"
                :key="item.id"
                class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl border border-border/60 bg-card/95 shadow-lg ring-1 backdrop-blur-md"
                :class="[variantOf(item.variant).value.ring, variantOf(item.variant).value.glow]"
                role="status"
                aria-live="polite"
                @mouseenter="pauseToast(item.id)"
                @mouseleave="resumeToast(item.id)"
            >
                <div class="flex items-start gap-3 p-4">
                    <component
                        :is="variantOf(item.variant).value.icon"
                        class="mt-0.5 size-5 shrink-0"
                        :class="variantOf(item.variant).value.iconClass"
                    />

                    <div class="min-w-0 flex-1">
                        <p class="text-sm leading-snug font-medium text-foreground">{{ item.title }}</p>
                        <p v-if="item.description" class="mt-1 text-sm leading-snug text-muted-foreground">
                            {{ item.description }}
                        </p>
                    </div>

                    <button
                        type="button"
                        class="-m-1 shrink-0 rounded-md p-1 text-muted-foreground transition hover:bg-muted hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        aria-label="Dismiss notification"
                        @click="toast.dismiss(item.id)"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <div
                    class="h-0.5 origin-left"
                    :class="variantOf(item.variant).value.bar"
                    :style="{
                        animation: `toast-out ${item.duration}ms linear forwards`,
                        transformOrigin: 'left',
                        width: '100%',
                    }"
                />
            </div>
        </TransitionGroup>
    </div>
</template>
