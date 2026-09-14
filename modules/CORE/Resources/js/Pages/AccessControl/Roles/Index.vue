<script setup lang="ts">
import DataTable from '../../../Components/DataTable/DataTable.vue';
import PermissionMatrix from '../../../Components/PermissionMatrix.vue';
import Badge from '../../../Components/ui/Badge.vue';
import Button from '../../../Components/ui/Button.vue';
import ConfirmDialog from '../../../Components/ui/ConfirmDialog.vue';
import Dropdown from '../../../Components/ui/Dropdown.vue';
import DropdownItem from '../../../Components/ui/DropdownItem.vue';
import FormField from '../../../Components/ui/FormField.vue';
import Input from '../../../Components/ui/Input.vue';
import Modal from '../../../Components/ui/Modal.vue';
import PageHeader from '../../../Components/ui/PageHeader.vue';
import { toast } from '../../../Composables/useToast';
import { usePermission } from '../../../Composables/usePermission';
import AdminLayout from '../../../Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { KeyRound, Lock, MoreHorizontal, Pencil, Plus, ShieldCheck, Trash2, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../Types';

interface RoleRow {
    id: number;
    name: string;
    guard_name: string;
    permissions_count: number;
    users_count: number;
    is_protected: boolean;
    permissions: string[];
    created_at: string | null;
    created_at_human: string | null;
}

const props = defineProps<{
    roles: Paginated<RoleRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    permissionMatrix: Record<string, string[]>;
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
    { key: 'name', label: 'Role', sortable: true },
    { key: 'permissions_count', label: 'Permissions', sortable: false, align: 'center', width: '8rem' },
    { key: 'users_count', label: 'Users', sortable: false, align: 'center', width: '6rem', hideBelow: 'md' },
    { key: 'guard_name', label: 'Guard', align: 'center', width: '6rem', hideBelow: 'lg' },
    { key: 'created_at', label: 'Created', sortable: true, align: 'right', width: '9rem', hideBelow: 'lg' },
];

/* ------------------------------- form state ------------------------------ */

type RoleForm = {
    name: string;
    permissions: string[];
};

const form = useForm<RoleForm>({ name: '', permissions: [] });

const modalOpen = ref(false);
const editing = ref<RoleRow | null>(null);

const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.name = '';
    form.permissions = [];
    modalOpen.value = true;
}

function openEdit(role: RoleRow): void {
    editing.value = role;
    form.name = role.name;
    form.permissions = [...role.permissions];
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
                title: isEditing.value ? 'Role updated' : 'Role created',
                description: isEditing.value ? `“${form.name}” was saved.` : `“${form.name}” is ready to use.`,
            });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/access-control/roles/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/access-control/roles', options);
}

/* -------------------------------- deletion ------------------------------- */

const confirmOpen = ref(false);
const pendingDelete = ref<RoleRow | null>(null);
const deleting = ref(false);

function askDelete(role: RoleRow): void {
    if (role.is_protected) {
        toast.error({ title: 'This role is protected', description: 'Guard roles cannot be removed.' });

        return;
    }

    pendingDelete.value = role;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/access-control/roles/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Role deleted', description: `“${pendingDelete.value?.name}” was removed.` });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That role could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}

const headline = (value: string) =>
    value
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
</script>

<template>
    <AdminLayout>
        <Head title="Roles" />

        <PageHeader
            title="Roles"
            description="Bundle permissions into roles, then assign roles to users."
            :breadcrumbs="[{ title: 'CORE' }, { title: 'Access control' }, { title: 'Roles' }]"
        >
            <template #actions>
                <Button v-if="can('role.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New role
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="roles.data"
            :meta="roles"
            url="/admin/access-control/roles"
            :only="['roles']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search roles…"
            empty-title="No roles found"
            empty-description="Create your first role to start delegating access."
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-50 dark:bg-brand-950/50">
                        <ShieldCheck class="size-4 text-brand-600 dark:text-brand-400" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="truncate font-medium text-foreground">{{ headline(row.name) }}</span>
                            <Lock v-if="row.is_protected" class="size-3.5 shrink-0 text-amber-500" title="Protected role" />
                        </div>
                        <span class="font-mono text-xs text-muted-foreground">{{ row.name }}</span>
                    </div>
                </div>
            </template>

            <template #cell-permissions_count="{ row }">
                <Badge :tone="row.permissions_count > 0 ? 'brand' : 'neutral'" dot>
                    {{ row.permissions_count }}
                </Badge>
            </template>

            <template #cell-users_count="{ row }">
                <span class="inline-flex items-center gap-1 text-sm text-muted-foreground">
                    <Users class="size-3.5" />
                    {{ row.users_count }}
                </span>
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

                    <DropdownItem v-if="can('role.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit permissions
                    </DropdownItem>

                    <DropdownItem
                        v-if="can('role.delete')"
                        tone="danger"
                        :disabled="row.is_protected"
                        @click="askDelete(row)"
                    >
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <!-- Create / edit -->
        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit role' : 'New role'"
            :description="
                isEditing
                    ? 'Update the role name and the capabilities it grants.'
                    : 'Give the role a name and choose what it can do.'
            "
            size="xl"
            @close="closeModal"
        >
            <form class="space-y-5 p-5" @submit.prevent="submit">
                <FormField label="Role name" required :error="form.errors.name" hint="Lowercase, e.g. “front-desk”.">
                    <template #default="{ id, describedBy, invalid }">
                        <Input
                            :id="id"
                            v-model="form.name"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                            placeholder="e.g. front-desk"
                            :disabled="Boolean(editing?.is_protected)"
                        />
                    </template>
                </FormField>

                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <KeyRound class="size-4 text-muted-foreground" />
                        <p class="text-sm font-medium text-foreground">Capabilities</p>
                    </div>

                    <PermissionMatrix v-model="form.permissions" :matrix="permissionMatrix" />
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create role' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this role?"
            :message="`Every user holding “${pendingDelete?.name}” will immediately lose its capabilities. This cannot be undone.`"
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
