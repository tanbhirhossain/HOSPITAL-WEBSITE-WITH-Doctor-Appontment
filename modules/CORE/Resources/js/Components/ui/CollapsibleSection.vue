<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { cn } from '../../Utils/cn';
import { computed, ref, watch } from 'vue';

/**
 * One collapsible block of a long form.
 *
 * The body is kept mounted while collapsed (v-show, not v-if) so Inertia
 * validation errors inside a folded section still scroll into view, and so
 * the editor never loses unsaved rows by folding a section away.
 */
const props = withDefaults(
    defineProps<{
        title: string;
        description?: string;
        icon?: string;
        count?: number | null;
        defaultOpen?: boolean;
        invalid?: boolean;
        class?: string;
    }>(),
    {
        description: undefined,
        icon: undefined,
        count: null,
        defaultOpen: false,
        invalid: false,
    },
);

const emit = defineEmits<{ toggle: [open: boolean] }>();

const open = ref(props.defaultOpen);

watch(
    () => props.defaultOpen,
    (value) => (open.value = value),
);

function toggle(): void {
    open.value = !open.value;
    emit('toggle', open.value);
}

/** Errors inside a folded section must not be invisible. */
watch(
    () => props.invalid,
    (value) => {
        if (value) {
            open.value = true;
        }
    },
    { immediate: true },
);

const panelId = `collapsible-${Math.random().toString(36).slice(2, 9)}`;

const chevron = computed(() => cn('size-4 shrink-0 text-muted-foreground transition-transform duration-200', open.value && 'rotate-180'));
</script>

<template>
    <section
        :class="
            cn(
                'overflow-hidden rounded-xl border bg-card shadow-sm transition-colors',
                invalid ? 'border-red-400 dark:border-red-500/60' : 'border-border',
                props.class,
            )
        "
    >
        <h3>
            <button
                type="button"
                class="flex w-full items-center gap-3 px-5 py-4 text-left transition-colors hover:bg-accent/40 focus-visible:bg-accent/40 focus-visible:outline-none"
                :aria-expanded="open"
                :aria-controls="panelId"
                @click="toggle"
            >
                <span
                    v-if="icon"
                    :class="
                        cn(
                            'grid size-9 shrink-0 place-items-center rounded-lg text-sm font-semibold',
                            invalid
                                ? 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400'
                                : 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400',
                        )
                    "
                >
                    {{ icon }}
                </span>

                <span class="min-w-0 flex-1">
                    <span class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold text-foreground">{{ title }}</span>

                        <span
                            v-if="count !== null"
                            class="rounded-full bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground"
                        >
                            {{ count }}
                        </span>

                        <span v-if="invalid" class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700 dark:bg-red-950/60 dark:text-red-300">
                            Needs attention
                        </span>
                    </span>

                    <span v-if="description" class="mt-0.5 block text-sm text-muted-foreground">
                        {{ description }}
                    </span>
                </span>

                <ChevronDown :class="chevron" />
            </button>
        </h3>

        <div :id="panelId" v-show="open" class="border-t border-border px-5 py-5">
            <slot />
        </div>
    </section>
</template>
