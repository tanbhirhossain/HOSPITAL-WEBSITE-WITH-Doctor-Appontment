<script setup lang="ts">
import DataTable from '../../Components/DataTable/DataTable.vue';
import Badge from '../../Components/ui/Badge.vue';
import Button from '../../Components/ui/Button.vue';
import Card from '../../Components/ui/Card.vue';
import ConfirmDialog from '../../Components/ui/ConfirmDialog.vue';
import Dropdown from '../../Components/ui/Dropdown.vue';
import DropdownItem from '../../Components/ui/DropdownItem.vue';
import Input from '../../Components/ui/Input.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import StatCard from '../../Components/ui/StatCard.vue';
import { usePermission } from '../../Composables/usePermission';
import { toast } from '../../Composables/useToast';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Activity, CalendarClock, Eye, History, ListTree, Trash2, UserCheck } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../Types';

interface AuditRow {
    id: number;
    event: string;
    event_label: string;
    module: string | null;
    description: string | null;
    user_name: string | null;
    auditable_type: string | null;
    auditable_id: number | null;
    ip_address: string | null;
    created_at_human: string | null;
}

const props = defineProps<{
    entries: Paginated<AuditRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    filterOptions: { event: string[]; module: string[]; user: Record<number, string> };
    statistics: { total: number; by_event: Record<string, number>; today: number; last_7_days: number };
}>();

const { can } = usePermission();

/** DataTable is generic, so reference only the slice of API we need. */
const tableRef = ref<{ setFilter: (key: string, value: unknown) => void } | null>(null);

const columns: DataTableColumn[] = [
    { key: 'event', label: 'Event', sortable: true, width: '9rem' },
    { key: 'description', label: 'Description', sortable: false },
    { key: 'module', label: 'Module', sortable: true, align: 'center', width: '7rem', hideBelow: 'md' },
    { key: 'user_name', label: 'User', sortable: true, width: '11rem', hideBelow: 'lg' },
    { key: 'ip_address', label: 'IP address', sortable: true, width: '9rem', hideBelow: 'xl' },
    { key: 'created_at', label: 'When', sortable: true, align: 'right', width: '9rem' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'event',
        label: 'Event',
        options: props.filterOptions.event.map((event) => ({ value: event, label: event.replace('-', ' ') })),
    },
    {
        key: 'module',
        label: 'Module',
        options: props.filterOptions.module.map((module) => ({ value: module, label: module })),
    },
    {
        key: 'user_id',
        label: 'User',
        options: Object.entries(props.filterOptions.user).map(([id, name]) => ({ value: Number(id), label: name })),
    },
]);

const TONES: Record<string, 'success' | 'info' | 'danger' | 'warning' | 'neutral' | 'violet'> = {
    created: 'success',
    updated: 'info',
    deleted: 'danger',
    restored: 'warning',
    login: 'violet',
    logout: 'neutral',
    'failed-login': 'danger',
};

/* -------------------------------- date range ----------------------------- */

const initialRange = (props.filters.created_at ?? {}) as { from?: string; to?: string };

const dateFrom = ref(initialRange.from ?? '');
const dateTo = ref(initialRange.to ?? '');

function applyRange(): void {
    tableRef.value?.setFilter('created_at', {
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
    });
}

/* -------------------------------- deletion ------------------------------- */

const confirmOpen = ref(false);
const pendingDelete = ref<AuditRow | null>(null);
const deleting = ref(false);

function askDelete(row: AuditRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/audit-trail/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Log entry removed' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That entry could not be removed.' }),
        onFinish: () => (deleting.value = false),
    });
}

/* --------------------------------- prune --------------------------------- */

const pruneOpen = ref(false);
const pruning = ref(false);

function prune(): void {
    pruning.value = true;

    router.delete('/admin/audit-trail/prune', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Old entries pruned' });
            pruneOpen.value = false;
        },
        onError: () => toast.error({ title: 'Pruning failed. Please try again.' }),
        onFinish: () => (pruning.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Audit Trail" />

        <PageHeader
            title="Audit Trail"
            description="An immutable record of every change made across the panel."
            :breadcrumbs="[{ title: 'CORE' }, { title: 'Audit trail' }]"
        >
            <template #actions>
                <Button v-if="can('audit.delete')" variant="outline" @click="pruneOpen = true">
                    <Trash2 class="size-4" />
                    Prune old entries
                </Button>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Total entries" :value="statistics.total" :icon="History" tone="brand" />
            <StatCard label="Today" :value="statistics.today" :icon="Activity" tone="violet" />
            <StatCard label="Last 7 days" :value="statistics.last_7_days" :icon="CalendarClock" tone="emerald" />
            <StatCard
                label="Recorded events"
                :value="Object.keys(statistics.by_event).length"
                :icon="ListTree"
                tone="amber"
            />
        </div>

        <DataTable
            ref="tableRef"
            :columns="columns"
            :rows="entries.data"
            :meta="entries"
            url="/admin/audit-trail"
            :only="['entries', 'statistics']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort || 'created_at'"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search descriptions, URLs and IPs…"
            empty-title="No activity recorded"
            empty-description="Once someone changes something in the panel it will show up here."
        >
            <template #toolbar>
                <div class="flex items-center gap-1.5">
                    <Input v-model="dateFrom" type="date" class="w-36" aria-label="From date" @change="applyRange" />
                    <span class="text-xs text-muted-foreground">to</span>
                    <Input v-model="dateTo" type="date" class="w-36" aria-label="To date" @change="applyRange" />
                </div>
            </template>

            <template #cell-event="{ row }">
                <Badge :tone="TONES[row.event] ?? 'neutral'" dot>{{ row.event_label }}</Badge>
            </template>

            <template #cell-description="{ row }">
                <div class="min-w-0">
                    <p class="truncate text-sm text-foreground">{{ row.description ?? '—' }}</p>
                    <p v-if="row.auditable_type" class="truncate text-xs text-muted-foreground">
                        {{ row.auditable_type }} #{{ row.auditable_id }}
                    </p>
                </div>
            </template>

            <template #cell-module="{ row }">
                <Badge v-if="row.module" tone="brand" outline>{{ row.module }}</Badge>
                <span v-else class="text-muted-foreground">—</span>
            </template>

            <template #cell-user_name="{ row }">
                <span class="inline-flex items-center gap-1.5 text-sm text-foreground">
                    <UserCheck class="size-3.5 text-muted-foreground" />
                    {{ row.user_name ?? 'System' }}
                </span>
            </template>

            <template #cell-ip_address="{ row }">
                <span class="font-mono text-xs text-muted-foreground">{{ row.ip_address ?? '—' }}</span>
            </template>

            <template #cell-created_at="{ row }">
                <span class="text-sm whitespace-nowrap text-muted-foreground">{{ row.created_at_human }}</span>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <Eye class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem as="a" :href="`/admin/audit-trail/${row.id}`">
                        <Eye class="size-4 text-muted-foreground" />
                        View details
                    </DropdownItem>

                    <DropdownItem v-if="can('audit.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Card>
            <template #title>How the audit trail works</template>

            <div class="space-y-2 text-sm text-muted-foreground">
                <p>
                    Every create, update and delete on an audited model is captured with the changed attributes, the
                    acting user, their IP address and the request URL. Authentication events (sign in, sign out, failed
                    sign in) are recorded too.
                </p>
                <p>
                    Sensitive columns such as passwords and remember tokens are stripped before anything is written.
                    Entries are append-only — they can be pruned, never edited.
                </p>
            </div>

            <template #footer>
                <Link href="/admin/audit-trail" class="text-xs font-medium text-brand-600 hover:text-brand-700">
                    Refresh log →
                </Link>
            </template>
        </Card>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this log entry?"
            message="The entry will be permanently removed from the audit trail."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />

        <ConfirmDialog
            :open="pruneOpen"
            title="Prune old entries?"
            message="Entries older than the configured retention window will be permanently deleted."
            confirm-label="Prune now"
            :processing="pruning"
            @close="pruneOpen = false"
            @confirm="prune"
        />
    </AdminLayout>
</template>
