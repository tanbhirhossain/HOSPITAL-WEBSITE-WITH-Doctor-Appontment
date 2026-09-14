<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { CheckCircle2, Info, TriangleAlert, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(defineProps<{ tone?: 'info' | 'success' | 'warning' | 'danger' }>(), { tone: 'info' });

const TONES = {
    info: { icon: Info, class: 'border-sky-200 bg-sky-50 text-sky-900 dark:border-sky-900 dark:bg-sky-950/50 dark:text-sky-200' },
    success: {
        icon: CheckCircle2,
        class: 'border-emerald-200 bg-emerald-50 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200',
    },
    warning: {
        icon: TriangleAlert,
        class: 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-200',
    },
    danger: { icon: XCircle, class: 'border-red-200 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950/50 dark:text-red-200' },
};

const icon = computed(() => TONES[props.tone].icon);
</script>

<template>
    <div :class="cn('flex items-start gap-2.5 rounded-lg border px-3.5 py-3 text-sm', TONES[tone].class)" role="alert">
        <component :is="icon" class="mt-0.5 size-4 shrink-0" />
        <div class="min-w-0 flex-1">
            <slot />
        </div>
    </div>
</template>
