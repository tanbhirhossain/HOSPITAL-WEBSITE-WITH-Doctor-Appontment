<script setup lang="ts">
import Avatar from '../Components/ui/Avatar.vue';
import Dropdown from '../Components/ui/Dropdown.vue';
import DropdownItem from '../Components/ui/DropdownItem.vue';
import { usePermission } from '../Composables/usePermission';
import { router, usePage } from '@inertiajs/vue3';
import { LogOut, Menu, Moon, Settings, Sun, UserCog } from 'lucide-vue-next';
import { computed } from 'vue';

const emit = defineEmits<{ 'open-mobile-nav': [] }>();

const { user, roles } = usePermission();

const page = usePage<{ name?: string; [key: string]: unknown }>();

/** Follow the system when no explicit preference is stored. */
const isDark = computed(() => document.documentElement.classList.contains('dark'));

function toggleTheme(): void {
    const next = isDark.value ? 'light' : 'dark';
    localStorage.setItem('appearance', next);
    document.documentElement.classList.toggle('dark', next === 'dark');
}

function logout(): void {
    router.post('/logout');
}
</script>

<template>
    <header
        class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-border bg-card/85 px-4 backdrop-blur-md sm:px-6"
    >
        <button
            type="button"
            class="-ml-1 grid size-9 shrink-0 cursor-pointer place-items-center rounded-lg text-muted-foreground transition hover:bg-accent hover:text-foreground lg:hidden"
            aria-label="Open navigation"
            @click="emit('open-mobile-nav')"
        >
            <Menu class="size-5" />
        </button>

        <div class="min-w-0 flex-1">
            <slot name="title">
                <p class="truncate text-sm font-semibold text-foreground">
                    {{ page.props.name ?? 'AMZ Hospital' }}
                </p>
            </slot>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <button
                type="button"
                class="grid size-9 cursor-pointer place-items-center rounded-lg text-muted-foreground transition hover:bg-accent hover:text-foreground"
                :aria-label="isDark ? 'Switch to light theme' : 'Switch to dark theme'"
                @click="toggleTheme"
            >
                <component :is="isDark ? Sun : Moon" class="size-[1.15rem]" />
            </button>

            <Dropdown align="right" width="w-60">
                <template #trigger="{ toggle }">
                    <button
                        type="button"
                        class="flex cursor-pointer items-center gap-2.5 rounded-lg py-1.5 pr-2 pl-1.5 transition hover:bg-accent"
                        @click="toggle"
                    >
                        <Avatar :name="user?.name ?? 'Unknown'" size="sm" />
                        <span class="hidden text-left sm:block">
                            <span class="block max-w-32 truncate text-sm leading-tight font-medium text-foreground">
                                {{ user?.name ?? 'Guest' }}
                            </span>
                            <span class="block max-w-32 truncate text-xs leading-tight text-muted-foreground">
                                {{ roles[0] ?? 'no role' }}
                            </span>
                        </span>
                    </button>
                </template>

                <div class="border-b border-border px-3 py-2.5">
                    <p class="truncate text-sm font-medium text-foreground">{{ user?.name }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ user?.email }}</p>
                </div>

                <div class="py-1">
                    <DropdownItem as="a" href="/settings/profile">
                        <UserCog class="size-4 text-muted-foreground" />
                        Profile settings
                    </DropdownItem>
                    <DropdownItem as="a" href="/settings/password">
                        <Settings class="size-4 text-muted-foreground" />
                        Password
                    </DropdownItem>
                </div>

                <div class="border-t border-border pt-1">
                    <DropdownItem tone="danger" @click="logout">
                        <LogOut class="size-4" />
                        Sign out
                    </DropdownItem>
                </div>
            </Dropdown>
        </div>
    </header>
</template>
