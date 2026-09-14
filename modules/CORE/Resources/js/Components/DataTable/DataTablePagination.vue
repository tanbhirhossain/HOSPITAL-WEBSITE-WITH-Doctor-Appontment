<script setup lang="ts" generic="T extends object">
import Button from '../ui/Button.vue';
import { cn } from '../../Utils/cn';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { computed } from 'vue';
import type { Paginated } from '../../Types';

const props = defineProps<{
    meta: Paginated<T>;
    perPage: number;
    perPageOptions: number[];
}>();

const emit = defineEmits<{
    visit: [url: string | null];
    'update:perPage': [value: number];
}>();

const range = computed(() => {
    if (props.meta.total === 0) {
        return 'No results';
    }

    return `${props.meta.from ?? 0}–${props.meta.to ?? 0} of ${props.meta.total.toLocaleString()}`;
});

/** Page numbers with ellipses, so a 200-page table stays compact. */
const pageLinks = computed(() => props.meta.links.filter((link) => /^\d+$/.test(link.label.trim())));
</script>

<template>
    <div class="flex flex-col gap-3 border-t border-border px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <p class="text-xs text-muted-foreground">{{ range }}</p>

            <label class="flex items-center gap-1.5 text-xs text-muted-foreground">
                <span class="hidden sm:inline">Rows</span>
                <select
                    :value="perPage"
                    class="h-7 cursor-pointer rounded-md border border-input bg-background px-1.5 text-xs text-foreground focus:border-brand-500 focus:ring-1 focus:ring-brand-500/30 focus:outline-none"
                    @change="emit('update:perPage', Number(($event.target as HTMLSelectElement).value))"
                >
                    <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
                </select>
            </label>
        </div>

        <nav class="flex items-center gap-1" aria-label="Pagination">
            <Button
                variant="outline"
                size="icon-sm"
                :disabled="!meta.prev_page_url"
                aria-label="Previous page"
                @click="emit('visit', meta.prev_page_url)"
            >
                <ChevronLeft class="size-4" />
            </Button>

            <Button
                v-for="link in pageLinks"
                :key="link.label"
                :variant="link.active ? 'primary' : 'ghost'"
                size="icon-sm"
                @click="emit('visit', link.url)"
            >
                <span :class="cn('text-xs', link.active && 'font-semibold')">{{ link.label.trim() }}</span>
            </Button>

            <Button
                variant="outline"
                size="icon-sm"
                :disabled="!meta.next_page_url"
                aria-label="Next page"
                @click="emit('visit', meta.next_page_url)"
            >
                <ChevronRight class="size-4" />
            </Button>
        </nav>
    </div>
</template>
