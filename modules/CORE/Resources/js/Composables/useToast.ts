import { readonly, reactive } from 'vue';
import type { Toast, ToastVariant } from '../Types';

/**
 * A tiny, dependency-free toast store.
 *
 * One module-level reactive array keeps the queue; `useToasts()` exposes it
 * read-only and the `toast` helpers are the only way to mutate it.
 */

const MAX_VISIBLE = 4;

const state = reactive<{ items: Toast[] }>({ items: [] });

let nextId = 1;
const timers = new Map<number, ReturnType<typeof setTimeout>>();

export interface ToastInput {
    title: string;
    description?: string;
    duration?: number;
}

function dismiss(id: number): void {
    state.items = state.items.filter((item) => item.id !== id);

    const timer = timers.get(id);
    if (timer) {
        clearTimeout(timer);
        timers.delete(id);
    }
}

function push(input: ToastInput, variant: ToastVariant): number {
    const id = nextId++;

    const toast: Toast = {
        id,
        title: input.title,
        description: input.description,
        variant,
        duration: input.duration ?? (variant === 'error' ? 6000 : 4000),
    };

    state.items = [toast, ...state.items].slice(0, MAX_VISIBLE);

    if (toast.duration > 0) {
        timers.set(
            id,
            setTimeout(() => dismiss(id), toast.duration),
        );
    }

    return id;
}

export const toast = {
    success: (input: ToastInput): number => push(input, 'success'),
    error: (input: ToastInput): number => push(input, 'error'),
    warning: (input: ToastInput): number => push(input, 'warning'),
    info: (input: ToastInput): number => push(input, 'info'),
    message: (input: ToastInput): number => push(input, 'default'),

    /**
     * Convenience for Inertia form callbacks: shows the first validation
     * message, or a generic failure notice.
     */
    fromErrors: (errors: Record<string, string> | null | undefined, fallback = 'Please check the form and try again.'): number => {
        const first = errors ? Object.values(errors)[0] : null;

        return push({ title: first ?? fallback }, 'error');
    },

    dismiss,
    clear: (): void => {
        state.items.forEach((item) => dismiss(item.id));
    },
};

export function useToasts() {
    return readonly(state);
}

/** Pause the auto-dismiss timer while the pointer hovers a toast. */
export function pauseToast(id: number): void {
    const timer = timers.get(id);
    if (timer) {
        clearTimeout(timer);
        timers.delete(id);
    }
}

export function resumeToast(id: number, duration = 2500): void {
    if (timers.has(id)) {
        return;
    }

    timers.set(
        id,
        setTimeout(() => dismiss(id), duration),
    );
}
