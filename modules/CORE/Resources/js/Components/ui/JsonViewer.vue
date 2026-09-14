<script setup lang="ts">
import { computed } from 'vue';

/**
 * Read-only JSON diff viewer for the audit trail. Sorted keys keep the two
 * panes aligned when comparing before/after payloads.
 */
const props = defineProps<{
    data?: Record<string, unknown> | null;
    emptyLabel?: string;
}>();

const entries = computed<Array<{ key: string; value: string }>>(() => {
    if (!props.data) {
        return [];
    }

    return Object.entries(props.data)
        .sort(([a], [b]) => a.localeCompare(b))
        .map(([key, value]) => ({
            key,
            value: typeof value === 'object' && value !== null ? JSON.stringify(value) : String(value ?? ''),
        }));
});
</script>

<template>
    <dl v-if="entries.length" class="divide-y divide-border rounded-lg border border-border bg-muted/30">
        <div v-for="entry in entries" :key="entry.key" class="grid grid-cols-1 gap-1 px-3 py-2 sm:grid-cols-[11rem_1fr] sm:gap-3">
            <dt class="font-mono text-xs break-all text-muted-foreground">{{ entry.key }}</dt>
            <dd class="font-mono text-xs break-all text-foreground">{{ entry.value || '—' }}</dd>
        </div>
    </dl>

    <p v-else class="px-3 py-2 text-xs text-muted-foreground italic">{{ emptyLabel ?? 'No recorded values' }}</p>
</template>
