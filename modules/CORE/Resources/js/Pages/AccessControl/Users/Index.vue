<script setup lang="ts">
import DataTable from '../../../Components/DataTable/DataTable.vue';
import PermissionMatrix from '../../../Components/PermissionMatrix.vue';
import Avatar from '../../../Components/ui/Avatar.vue';
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
import { CheckCircle2, KeyRound, MoreHorizontal, Pencil, Plus, ShieldCheck, Trash2, UserPlus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../Types';

interface UserRow {
    id: number;
    name: string;
    email: string;
    email_verified: boolean;
    roles: string[];
    direct_permissions: string[];
    initials: string;
    created_at_human: string | null;
}

const props = defineProps<{
    users: Paginated<UserRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    roleOptions: Record<string, string>;
    permissionMatrix: Record<string, string[]>;
}>();

const { can, user: currentUser } = usePermission();

const columns: DataTableColumn[] = [
    { key: 'name', label: 'User', sortable: true },
    { key: 'roles', label: 'Roles', sortable: false, hideBelow: 'md' },
    { key: 'email_verified', label: 'Status', sortable: false, align: 'center', width: '7.5rem' },
    { key: 'created_at', label: 'Created', sortable: true, align: 'right', width: '9rem', hideBelow: 'lg' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'role',
        label: 'Role',
        options: Object.entries(props.roleOptions).map(([value, label]) => ({ value, label })),
    },
    {
        key: 'email_verified',
        label: 'Verification',
        options: [
            { value: 'verified', label: 'Verified' },
            { value: 'unverified', label: 'Unverified' },
        ],
    },
]);

/* ------------------------------- form state ------------------------------ */

type UserForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    roles: string[];
    permissions: string[];
};

const blank = (): UserForm => ({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    permissions: [],
});

const form = useForm<UserForm>(blank());

const modalOpen = ref(false);
const editing = ref<UserRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: UserRow): void {
    editing.value = row;
    form.name = row.name;
    form.email = row.email;
    form.password = '';
    form.password_confirmation = '';
    form.roles = [...row.roles];
    form.permissions = [...row.direct_permissions];
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
                title: isEditing.value ? 'Access updated' : 'User created',
                description: isEditing.value ? `${form.name}'s roles were saved.` : `${form.name} can now sign in.`,
            });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/access-control/users/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/access-control/users', options);
}

/* -------------------------------- deletion ------------------------------- */

const confirmOpen = ref(false);
const pendingDelete = ref<UserRow | null>(null);
const deleting = ref(false);

function askDelete(row: UserRow): void {
    if (row.id === currentUser.value?.id) {
        toast.error({ title: 'You cannot remove your own account.' });

        return;
    }

    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/access-control/users/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'User removed', description: `${pendingDelete.value?.name} lost panel access.` });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That account could not be removed.' }),
        onFinish: () => (deleting.value = false),
    });
}

function toggleRole(role: string): void {
    form.roles = form.roles.includes(role) ? form.roles.filter((item) => item !== role) : [...form.roles, role];
}

const headline = (value: string) =>
    value
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
</script>

<template>
    <AdminLayout>
        <Head title="Users" />

        <PageHeader
            title="Users"
            description="Control who can reach the panel, and exactly what each person can do."
            :breadcrumbs="[{ title: 'CORE' }, { title: 'Access control' }, { title: 'Users' }]"
        >
            <template #actions>
                <Button v-if="can('user.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New user
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="users.data"
            :meta="users"
            url="/admin/access-control/users"
            :only="['users']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search by name or email…"
            empty-title="No users found"
            empty-description="Invite a colleague to give them access to the panel."
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-3">
                    <Avatar :name="row.name" size="md" />

                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">
                            {{ row.name }}
                            <span v-if="row.id === currentUser?.id" class="ml-1 text-xs font-normal text-muted-foreground">
                                (you)
                            </span>
                        </p>
                        <p class="truncate text-xs text-muted-foreground">{{ row.email }}</p>
                    </div>
                </div>
            </template>

            <template #cell-roles="{ row }">
                <div v-if="row.roles.length" class="flex flex-wrap gap-1.5">
                    <Badge v-for="role in row.roles" :key="role" tone="brand" outline>{{ headline(role) }}</Badge>
                </div>
                <span v-else class="text-sm text-muted-foreground">No role</span>
            </template>

            <template #cell-email_verified="{ row }">
                <Badge v-if="row.email_verified" tone="success" dot>Active</Badge>
                <Badge v-else tone="warning" dot>Pending</Badge>
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

                    <DropdownItem v-if="can('user.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit access
                    </DropdownItem>

                    <DropdownItem
                        v-if="can('user.delete')"
                        tone="danger"
                        :disabled="row.id === currentUser?.id"
                        @click="askDelete(row)"
                    >
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit access' : 'New user'"
            :description="
                isEditing ? 'Update roles, direct permissions and optionally the password.' : 'Create an account and grant access.'
            "
            size="xl"
            @close="closeModal"
        >
            <form class="space-y-5 p-5" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <FormField label="Full name" required :error="form.errors.name">
                        <template #default="{ id, describedBy, invalid }">
                            <Input :id="id" v-model="form.name" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </FormField>

                    <FormField label="Email address" required :error="form.errors.email">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.email"
                                type="email"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>

                    <FormField
                        label="Password"
                        :required="!isEditing"
                        :error="form.errors.password"
                        :hint="isEditing ? 'Leave blank to keep the current password.' : 'At least 8 characters, with letters and numbers.'"
                    >
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>

                    <FormField label="Confirm password" :required="!isEditing" :error="form.errors.password_confirmation">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="size-4 text-muted-foreground" />
                        <p class="text-sm font-medium text-foreground">Roles</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="(label, role) in roleOptions"
                            :key="role"
                            type="button"
                            :class="
                                form.roles.includes(String(role))
                                    ? 'rounded-lg border border-brand-600 bg-brand-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm transition'
                                    : 'rounded-lg border border-border bg-background px-3 py-1.5 text-sm text-foreground transition hover:bg-accent'
                            "
                            @click="toggleRole(String(role))"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <KeyRound class="size-4 text-muted-foreground" />
                        <p class="text-sm font-medium text-foreground">Direct permissions</p>
                    </div>
                    <p class="text-xs text-muted-foreground">
                        <CheckCircle2 class="mr-1 inline size-3.5" />
                        Optional — most access should come from a role.
                    </p>

                    <PermissionMatrix v-model="form.permissions" :matrix="permissionMatrix" />
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    <UserPlus class="size-4" />
                    {{ isEditing ? 'Save changes' : 'Create user' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this user?"
            :message="`${pendingDelete?.name} will immediately lose access to the administration panel.`"
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
