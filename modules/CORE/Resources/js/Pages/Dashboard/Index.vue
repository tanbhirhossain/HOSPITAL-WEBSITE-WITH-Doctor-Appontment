<script setup lang="ts">
import Badge from '../../Components/ui/Badge.vue';
import Card from '../../Components/ui/Card.vue';
import EmptyState from '../../Components/ui/EmptyState.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import StatCard from '../../Components/ui/StatCard.vue';
import { usePermission } from '../../Composables/usePermission';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Activity, Building2, CalendarDays, ShieldCheck, Stethoscope, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface Stats {
    doctors: number;
    departments: number;
    users: number;
    roles: number;
}

interface ActivityEntry {
    id: number;
    event: string;
    event_label: string;
    description: string | null;
    module: string | null;
    user_name: string | null;
    created_at_human: string | null;
}

const props = defineProps<{
    stats: Stats;
    recentActivity: ActivityEntry[];
    activityByEvent: Record<string, number>;
}>();

const { user } = usePermission();

const totalEvents = computed(() => Object.values(props.activityByEvent).reduce((sum, value) => sum + value, 0));

const eventBreakdown = computed(() =>
    Object.entries(props.activityByEvent)
        .sort(([, a], [, b]) => b - a)
        .slice(0, 6)
        .map(([event, count]) => ({
            event,
            count,
            percent: totalEvents.value ? Math.round((count / totalEvents.value) * 100) : 0,
        })),
);
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />

        <template #header-title>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-foreground">Dashboard</p>
                <p class="truncate text-xs text-muted-foreground">Welcome back, {{ user?.name?.split(' ')[0] }}</p>
            </div>
        </template>

        <PageHeader
            title="Dashboard"
            description="A live snapshot of your hospital directory and recent administrative activity."
            :breadcrumbs="[{ title: 'CORE' }, { title: 'Dashboard' }]"
        >
            <template #actions>
                <Link
                    href="/admin/doctors"
                    class="inline-flex h-9 items-center gap-2 rounded-lg bg-brand-600 px-3.5 text-sm font-medium text-white shadow-sm transition hover:bg-brand-700"
                >
                    <Stethoscope class="size-4" />
                    Manage doctors
                </Link>
            </template>
        </PageHeader>

        <!-- Stats -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Doctors" :value="stats.doctors" :icon="Stethoscope" tone="brand" hint="in the directory" />
            <StatCard label="Departments" :value="stats.departments" :icon="Building2" tone="violet" hint="configured" />
            <StatCard label="System users" :value="stats.users" :icon="Users" tone="emerald" hint="with panel access" />
            <StatCard label="Roles" :value="stats.roles" :icon="ShieldCheck" tone="amber" hint="permission groups" />
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Recent activity -->
            <Card class="lg:col-span-2">
                <template #title>
                    <span class="flex items-center gap-2">
                        <Activity class="size-4 text-muted-foreground" />
                        Recent activity
                    </span>
                </template>

                <template #actions>
                    <Link
                        href="/admin/audit-trail"
                        class="text-xs font-medium text-brand-600 transition hover:text-brand-700 dark:text-brand-400"
                    >
                        View audit trail →
                    </Link>
                </template>

                <ol v-if="recentActivity.length" class="space-y-0">
                    <li
                        v-for="entry in recentActivity"
                        :key="entry.id"
                        class="flex items-start gap-3 border-b border-border py-3 last:border-0 last:pb-0 first:pt-0"
                    >
                        <span
                            class="mt-0.5 size-2 shrink-0 rounded-full"
                            :class="{
                                'bg-emerald-500': entry.event === 'created',
                                'bg-sky-500': entry.event === 'updated',
                                'bg-red-500': entry.event === 'deleted',
                                'bg-amber-500': entry.event === 'login',
                                'bg-muted-foreground': entry.event === 'logout',
                            }"
                        />

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm text-foreground">
                                {{ entry.description ?? entry.event_label }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-muted-foreground">
                                {{ entry.user_name ?? 'System' }}
                                <span v-if="entry.module"> · {{ entry.module }}</span>
                            </p>
                        </div>

                        <span class="shrink-0 text-xs whitespace-nowrap text-muted-foreground">
                            {{ entry.created_at_human }}
                        </span>
                    </li>
                </ol>

                <EmptyState
                    v-else
                    compact
                    title="No activity recorded yet"
                    description="Changes made across the panel will appear here."
                />
            </Card>

            <!-- Event breakdown -->
            <Card>
                <template #title>
                    <span class="flex items-center gap-2">
                        <CalendarDays class="size-4 text-muted-foreground" />
                        Log breakdown
                    </span>
                </template>

                <ul v-if="eventBreakdown.length" class="space-y-3.5">
                    <li v-for="row in eventBreakdown" :key="row.event" class="space-y-1.5">
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <span class="truncate capitalize text-foreground">{{ row.event.replace('-', ' ') }}</span>
                            <span class="shrink-0 font-medium text-muted-foreground">{{ row.count }}</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-brand-500" :style="{ width: `${row.percent}%` }" />
                        </div>
                    </li>
                </ul>

                <EmptyState v-else compact title="Nothing logged yet" />

                <template #footer>
                    <div class="flex w-full items-center justify-between text-xs text-muted-foreground">
                        <span>Total events</span>
                        <Badge tone="brand">{{ totalEvents }}</Badge>
                    </div>
                </template>
            </Card>
        </div>
    </AdminLayout>
</template>
