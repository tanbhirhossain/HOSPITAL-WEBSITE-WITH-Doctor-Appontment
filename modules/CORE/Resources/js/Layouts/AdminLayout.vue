<script setup lang="ts">
import AdminHeader from './AdminHeader.vue';
import AdminSidebar from './AdminSidebar.vue';
import Toaster from '../Components/Toaster.vue';
import { toast } from '../Composables/useToast';
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

/**
 * Shell for every administration screen.
 *
 * Responsibilities kept deliberately narrow: chrome, navigation, and turning
 * server flash messages into toasts. Page content is entirely the slot's job.
 */

const collapsed = ref(localStorage.getItem('admin-sidebar-collapsed') === '1');
const mobileNavOpen = ref(false);

const sidebarWidth = ref(collapsed.value ? '4.25rem' : '16rem');

function toggleCollapse(): void {
    collapsed.value = !collapsed.value;
    localStorage.setItem('admin-sidebar-collapsed', collapsed.value ? '1' : '0');
    sidebarWidth.value = collapsed.value ? '4.25rem' : '16rem';
}

interface FlashProps {
    success?: string | null;
    error?: string | null;
    warning?: string | null;
    info?: string | null;
}

/**
 * Flash messages arrive as shared Inertia props. Watching the router rather
 * than the page object guarantees a toast even when the same message is
 * flashed twice in a row.
 */
function emitFlash(flash: FlashProps | undefined): void {
    if (!flash) {
        return;
    }

    if (flash.success) {
        toast.success({ title: flash.success });
    }
    if (flash.error) {
        toast.error({ title: flash.error });
    }
    if (flash.warning) {
        toast.warning({ title: flash.warning });
    }
    if (flash.info) {
        toast.info({ title: flash.info });
    }
}

const page = usePage<{ flash?: FlashProps; [key: string]: unknown }>();

onMounted(() => {
    emitFlash(page.props.flash);

    router.on('success', (event) => {
        emitFlash(event.detail.page.props.flash as FlashProps | undefined);
    });
});
</script>

<template>
    <div class="min-h-screen bg-muted/30">
        <AdminSidebar :collapsed="collapsed" @toggle-collapse="toggleCollapse" />

        <!-- Mobile nav drawer -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition duration-150 ease-in"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileNavOpen" class="fixed inset-0 z-40 lg:hidden">
                <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm" @click="mobileNavOpen = false" />
                <div class="animate-slide-in-right absolute inset-y-0 right-0 w-64 max-w-[80vw]">
                    <AdminSidebar :collapsed="false" @toggle-collapse="mobileNavOpen = false" />
                </div>
            </div>
        </Transition>

        <div
            class="flex min-h-screen flex-col transition-[padding] duration-200 ease-out"
            :style="{ paddingLeft: sidebarWidth }"
        >
            <AdminHeader @open-mobile-nav="mobileNavOpen = true">
                <template #title>
                    <slot name="header-title" />
                </template>
            </AdminHeader>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto w-full max-w-[90rem] space-y-6">
                    <slot />
                </div>
            </main>

            <footer class="border-t border-border px-6 py-4">
                <p class="text-xs text-muted-foreground">
                    © {{ new Date().getFullYear() }} AMZ Hospital Ltd. — Administration panel.
                </p>
            </footer>
        </div>

        <Toaster />
    </div>
</template>
