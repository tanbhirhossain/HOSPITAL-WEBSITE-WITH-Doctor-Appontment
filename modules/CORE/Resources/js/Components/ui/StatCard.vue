<script setup lang="ts">
import { cn } from '../../Utils/cn';
import { ArrowDownRight, ArrowUpRight } from 'lucide-vue-next';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        label: string;
        value: string | number;
        icon?: unknown;
        hint?: string;
        trend?: number;
        tone?: 'brand' | 'violet' | 'emerald' | 'amber';
    }>(),
    { tone: 'brand' },
);

const TONES = {
    brand: 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400',
    violet: 'bg-violet-50 text-violet-600 dark:bg-violet-950/50 dark:text-violet-400',
    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400',
};

const trendPositive = computed(() => (props.trend ?? 0) >= 0);
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-xl border border-border bg-card p-5 shadow-sm transition hover:border-brand-200 hover:shadow-md dark:hover:border-brand-900"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-foreground">{{ value }}</p>

                <div v-if="trend !== undefined || hint" class="mt-2 flex items-center gap-1.5 text-xs">
                    <span
                        v-if="trend !== undefined"
                        :class="cn('inline-flex items-center gap-0.5 font-medium', trendPositive ? 'text-emerald-600' : 'text-red-600')"
                    >
                        <component :is="trendPositive ? ArrowUpRight : ArrowDownRight" class="size-3" />
                        {{ Math.abs(trend) }}%
                    </span>
                    <span v-if="hint" class="text-muted-foreground">{{ hint }}</span>
                </div>
            </div>

            <span v-if="icon" :class="cn('grid size-10 shrink-0 place-items-center rounded-xl', TONES[tone])">
                <component :is="icon" class="size-5" />
            </span>
        </div>
    </div>
</template>
