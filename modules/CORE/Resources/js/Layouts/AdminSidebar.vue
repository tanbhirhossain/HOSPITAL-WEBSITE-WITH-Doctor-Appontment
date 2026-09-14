<script setup lang="ts">
import { usePermission } from '../Composables/usePermission';
import { Link } from '@inertiajs/vue3';
import { adminNav } from './adminNav';
import { cn } from '../Utils/cn';
import { ChevronRight, Hospital, PanelLeftClose, PanelLeftOpen } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ collapsed?: boolean }>();
const emit = defineEmits<{ 'toggle-collapse': [] }>();

const { can } = usePermission();

const page = usePage();

const isActive = (href: string): boolean =>
    page.url === href || page.url.startsWith(`${href}/`) || (href !== '/admin' && page.url === href);

const groups = computed(() =>
    adminNav
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => can(item.permission)),
        }))
        .filter((group) => group.items.length > 0),
);
</script>

<template>
    <aside
        :class="
            cn(
                'fixed inset-y-0 left-0 z-30 flex flex-col border-r border-border bg-card transition-[width] duration-200 ease-out',
                collapsed ? 'w-[4.25rem]' : 'w-64',
            )
        "
    >
        <!-- Brand -->
        <div class="flex h-16 shrink-0 items-center gap-3 border-b border-border px-4">
            <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-brand-600 text-white shadow-sm">
                <Hospital class="size-5" />
            </span>

            <Transition
                enter-active-class="transition duration-150 delay-75"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-75"
                leave-to-class="opacity-0"
            >
                <div v-if="!collapsed" class="min-w-0">
                    <p class="truncate text-sm font-semibold text-foreground">AMZ Hospital</p>
                    <p class="truncate text-xs text-muted-foreground">Administration</p>
                </div>
            </Transition>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-6 overflow-y-auto scrollbar-thin px-3 py-4">
            <div v-for="group in groups" :key="group.label">
                <p
                    v-if="!collapsed"
                    class="mb-1.5 px-2 text-[0.68rem] font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    {{ group.label }}
                </p>
                <div v-else class="mx-auto mb-2 h-px w-6 bg-border" />

                <ul class="space-y-0.5">
                    <li v-for="item in group.items" :key="item.href">
                        <Link
                            :href="item.href"
                            :title="collapsed ? item.title : undefined"
                            :class="
                                cn(
                                    'group relative flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors',
                                    isActive(item.href)
                                        ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300'
                                        : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                    collapsed && 'justify-center px-0',
                                )
                            "
                        >
                            <span
                                v-if="isActive(item.href)"
                                class="absolute inset-y-1.5 left-0 w-0.5 rounded-r-full bg-brand-600"
                            />

                            <component :is="item.icon" class="size-[1.15rem] shrink-0" />

                            <Transition
                                enter-active-class="transition duration-150 delay-75"
                                enter-from-class="opacity-0"
                                leave-active-class="transition duration-75"
                                leave-to-class="opacity-0"
                            >
                                <span v-if="!collapsed" class="min-w-0 flex-1 truncate">{{ item.title }}</span>
                            </Transition>

                            <ChevronRight
                                v-if="!collapsed"
                                class="size-3.5 shrink-0 opacity-0 transition group-hover:opacity-50"
                            />
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Collapse toggle -->
        <div class="shrink-0 border-t border-border p-3">
            <button
                type="button"
                :class="
                    cn(
                        'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm text-muted-foreground transition hover:bg-accent hover:text-foreground',
                        collapsed && 'justify-center px-0',
                    )
                "
                :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                @click="emit('toggle-collapse')"
            >
                <component :is="collapsed ? PanelLeftOpen : PanelLeftClose" class="size-[1.15rem] shrink-0" />
                <span v-if="!collapsed" class="truncate">Collapse</span>
            </button>
        </div>
    </aside>
</template>
