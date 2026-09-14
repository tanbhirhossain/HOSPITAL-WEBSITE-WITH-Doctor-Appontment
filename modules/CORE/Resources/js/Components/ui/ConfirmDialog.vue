<script setup lang="ts">
import Button from './Button.vue';
import Modal from './Modal.vue';
import { AlertTriangle } from 'lucide-vue-next';

/**
 * Destructive-action confirmation. Always explicit about what will happen,
 * and disables the button while the request is in flight.
 */
const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        message?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        tone?: 'danger' | 'primary';
        processing?: boolean;
    }>(),
    {
        open: false,
        title: 'Are you sure?',
        message: 'This action cannot be undone.',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        tone: 'danger',
        processing: false,
    },
);

const emit = defineEmits<{ close: []; confirm: [] }>();
</script>

<template>
    <Modal :open="open" size="sm" @close="emit('close')">
        <template #title>
            <span class="flex items-center gap-2.5">
                <span
                    class="grid size-8 shrink-0 place-items-center rounded-full"
                    :class="tone === 'danger' ? 'bg-red-50 dark:bg-red-950/50' : 'bg-brand-50 dark:bg-brand-950/50'"
                >
                    <AlertTriangle
                        class="size-4"
                        :class="tone === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-brand-600 dark:text-brand-400'"
                    />
                </span>
                {{ title }}
            </span>
        </template>

        <template #description>
            {{ message }}
        </template>

        <div class="px-5 py-4 text-sm text-muted-foreground">
            <slot />
        </div>

        <template #footer>
            <Button variant="ghost" :disabled="processing" @click="emit('close')">{{ cancelLabel }}</Button>
            <Button :variant="tone === 'danger' ? 'danger' : 'primary'" :loading="processing" @click="emit('confirm')">
                {{ confirmLabel }}
            </Button>
        </template>
    </Modal>
</template>
