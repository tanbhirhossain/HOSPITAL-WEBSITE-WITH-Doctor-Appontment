<script setup lang="ts">
import DataTable from '../../../Components/DataTable/DataTable.vue';
import Badge from '../../../Components/ui/Badge.vue';
import Button from '../../../Components/ui/Button.vue';
import ConfirmDialog from '../../../Components/ui/ConfirmDialog.vue';
import Dropdown from '../../../Components/ui/Dropdown.vue';
import DropdownItem from '../../../Components/ui/DropdownItem.vue';
import FormField from '../../../Components/ui/FormField.vue';
import Input from '../../../Components/ui/Input.vue';
import Modal from '../../../Components/ui/Modal.vue';
import PageHeader from '../../../Components/ui/PageHeader.vue';
import { usePermission } from '../../../Composables/usePermission';
import { toast } from '../../../Composables/useToast';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { KeyRound, MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../Types';

interface PermissionRow {
    id: number;
    name: string;
    resource: string;
    action: string;
    guard_name: string;
    roles_count: number;
    created_at: string | null;
    created_at_human: string | null;
}

const props = defineProps<{
    permissions: Paginated<PermissionRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    grouped: Record<string, string[]>;
    guards: string[];
}>();

const { can } = usePermission();

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'guard_name',
        label: 'Guard',
        options: props.guards.map((guard) => ({ value: guard, label: guard })),
    },
]);

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Permission', sortable: true },
    { key: 'resource', label: 'Resource', sortable: false, width: '10rem', hideBelow: 'md' },
    { key: 'roles_count', label: 'Roles', sortable: false, align: 'center', width: '6rem' },
    { key: 'guard_name', label: 'Guard', align: 'center', width: '6rem', hideBelow: 'lg' },
    { key: 'created_at', label: 'Created', sortable: true, align: 'right', width: '9rem', hideBelow: 'lg' },
];

const TONE_BY_ACTION: Record<string, 'success' | 'info' | 'warning' | 'danger' | 'neutral'> = {
    view: 'info',
    create: 'success',
    update: 'warning',
    delete: 'danger',
};

/* ------------------------------- form state ------------------------------ */

const form = useForm({ name: '' });

const modalOpen = ref(false);
const editing = ref<PermissionRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(permission: PermissionRow): void {
    editing.value = permission;
    form.name = permission.name;
    form.clearErrors();
    modalOpen.value = true;
}

function closeModal(): void {
    modalOpen.value = false;
    editing.value = null;
    form.reset();
    form.clearErrors();
}

function submit(): void {
    const options = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            closeModal();
            toast.success({
                title: isEditing.value ? 'Permission updated' : 'Permission created',
                description: `“${form.name}” saved.`,
            });
        },
        onError: () => toast.error({ title: 'Please check the permission name.' }),
    };

    if (editing.value) {
        form.put(`/admin/access-control/permissions/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/access-control/permissions', options);
}

/* -------------------------------- deletion ------------------------------- */

const confirmOpen = ref(false);
const pendingDelete = ref<PermissionRow | null>(null);
const deleting = ref(false);

function askDelete(permission: PermissionRow): void {
    pendingDelete.value = permission;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/access-control/permissions/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Permission deleted', description: `“${pendingDelete.value?.name}” was removed.` });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That permission is still in use by a role.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Permissions" />

        <PageHeader
            title="Permissions"
            description="Every capability in the panel, expressed as resource.action."
            :breadcrumbs="[{ title: 'CORE' }, { title: 'Access control' }, { title: 'Permissions' }]"
        >
            <template #actions>
                <Button v-if="can('permission.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New permission
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="permissions.data"
            :meta="permissions"
            url="/admin/access-control/permissions"
            :only="['permissions']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search permissions…"
            empty-title="No permissions found"
            empty-description="Permissions are seeded automatically on deploy."
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-muted">
                        <KeyRound class="size-4 text-muted-foreground" />
                    </span>
                    <span class="font-mono text-sm text-foreground">{{ row.name }}</span>
                </div>
            </template>

            <template #cell-resource="{ row }">
                <Badge tone="violet" outline class="capitalize">{{ row.resource }}</Badge>
            </template>

            <template #cell-roles_count="{ row }">
                <Badge :tone="row.roles_count > 0 ? 'brand' : 'neutral'" dot>{{ row.roles_count }}</Badge>
            </template>

            <template #cell-guard_name="{ row }">
                <Badge tone="neutral" outline>{{ row.guard_name }}</Badge>
            </template>

            <template #cell-created_at="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.created_at_human ?? '—' }}</span>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem v-if="can('permission.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Rename
                    </DropdownItem>

                    <DropdownItem v-if="can('permission.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Rename permission' : 'New permission'"
            :description="'Use the resource.action format — for example “doctor.create”.'"
            @close="closeModal"
        >
            <form class="space-y-4 p-5" @submit.prevent="submit">
                <FormField
                    label="Permission name"
                    required
                    :error="form.errors.name"
                    hint="Allowed: lowercase letters, numbers and hyphens, joined with dots."
                >
                    <template #default="{ id, describedBy, invalid }">
                        <Input
                            :id="id"
                            v-model="form.name"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                            placeholder="doctor.create"
                        />
                    </template>
                </FormField>

                <div class="flex flex-wrap gap-1.5">
                    <Badge v-for="action in ['view', 'create', 'update', 'delete']" :key="action" :tone="TONE_BY_ACTION[action]" outline>
                        {{ action }}
                    </Badge>
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create permission' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this permission?"
            :message="`“${pendingDelete?.name}” will be revoked from every role that currently grants it.`"
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
