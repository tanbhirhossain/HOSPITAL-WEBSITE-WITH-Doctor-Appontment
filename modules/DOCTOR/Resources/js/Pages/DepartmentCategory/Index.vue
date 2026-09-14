<script setup lang="ts">
import DataTable from '../../../../../CORE/Resources/js/Components/DataTable/DataTable.vue';
import Badge from '../../../../../CORE/Resources/js/Components/ui/Badge.vue';
import Button from '../../../../../CORE/Resources/js/Components/ui/Button.vue';
import ConfirmDialog from '../../../../../CORE/Resources/js/Components/ui/ConfirmDialog.vue';
import Dropdown from '../../../../../CORE/Resources/js/Components/ui/Dropdown.vue';
import DropdownItem from '../../../../../CORE/Resources/js/Components/ui/DropdownItem.vue';
import FormField from '../../../../../CORE/Resources/js/Components/ui/FormField.vue';
import Input from '../../../../../CORE/Resources/js/Components/ui/Input.vue';
import Modal from '../../../../../CORE/Resources/js/Components/ui/Modal.vue';
import PageHeader from '../../../../../CORE/Resources/js/Components/ui/PageHeader.vue';
import Switch from '../../../../../CORE/Resources/js/Components/ui/Switch.vue';
import { usePermission } from '../../../../../CORE/Resources/js/Composables/usePermission';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Building2, MoreHorizontal, Pencil, Plus, Tags, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../../../CORE/Resources/js/Types';

interface CategoryRow {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
    departments_count: number;
    created_at_human: string | null;
}

const props = defineProps<{
    categories: Paginated<CategoryRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
}>();

const { can } = usePermission();

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'is_active',
        label: 'Status',
        options: [
            { value: 1, label: 'Active' },
            { value: 0, label: 'Hidden' },
        ],
    },
]);

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Category', sortable: true },
    { key: 'slug', label: 'Slug', sortable: false, width: '12rem', hideBelow: 'lg' },
    { key: 'departments_count', label: 'Departments', sortable: false, align: 'center', width: '8rem' },
    { key: 'sort_order', label: 'Order', sortable: true, align: 'center', width: '6rem', hideBelow: 'md' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', width: '7rem' },
];

type CategoryForm = {
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
};

const form = useForm<CategoryForm>({ name: '', slug: '', sort_order: 0, is_active: true });

const modalOpen = ref(false);
const editing = ref<CategoryRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: CategoryRow): void {
    editing.value = row;
    form.name = row.name;
    form.slug = row.slug;
    form.sort_order = row.sort_order;
    form.is_active = row.is_active;
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
            toast.success({ title: isEditing.value ? 'Category updated' : 'Category created' });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/doctors/categories/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/doctors/categories', options);
}

const confirmOpen = ref(false);
const pendingDelete = ref<CategoryRow | null>(null);
const deleting = ref(false);

function askDelete(row: CategoryRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/categories/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Category deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'This category still has departments assigned.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Department Categories" />

        <PageHeader
            title="Department Categories"
            description="Group departments so visitors can browse by speciality area."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Categories' }]"
        >
            <template #actions>
                <Button v-if="can('department.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New category
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="categories.data"
            :meta="categories"
            url="/admin/doctors/categories"
            :only="['categories']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort || 'sort_order'"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search categories…"
            empty-title="No categories yet"
            empty-description="Create a category to start organising your departments."
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-violet-50 dark:bg-violet-950/50">
                        <Tags class="size-4 text-violet-600 dark:text-violet-400" />
                    </span>
                    <span class="truncate font-medium text-foreground">{{ row.name }}</span>
                </div>
            </template>

            <template #cell-slug="{ row }">
                <span class="font-mono text-xs text-muted-foreground">{{ row.slug }}</span>
            </template>

            <template #cell-departments_count="{ row }">
                <span class="inline-flex items-center gap-1.5 text-sm text-muted-foreground">
                    <Building2 class="size-3.5" />
                    {{ row.departments_count }}
                </span>
            </template>

            <template #cell-sort_order="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.sort_order }}</span>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :tone="row.is_active ? 'success' : 'neutral'" dot>{{ row.is_active ? 'Active' : 'Hidden' }}</Badge>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem v-if="can('department.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit
                    </DropdownItem>

                    <DropdownItem v-if="can('department.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit category' : 'New category'"
            :description="isEditing ? 'Update how this group is labelled and ordered.' : 'Add a new grouping for departments.'"
            @close="closeModal"
        >
            <form class="space-y-4 p-5" @submit.prevent="submit">
                <FormField label="Name" required :error="form.errors.name">
                    <template #default="{ id, describedBy, invalid }">
                        <Input :id="id" v-model="form.name" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </FormField>

                <FormField label="Slug" :error="form.errors.slug" hint="Leave blank to generate it from the name.">
                    <template #default="{ id, describedBy, invalid }">
                        <Input :id="id" v-model="form.slug" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </FormField>

                <FormField label="Sort order" hint="Lower numbers appear first.">
                    <template #default="{ id, describedBy, invalid }">
                        <Input
                            :id="id"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
                    </template>
                </FormField>

                <Switch v-model="form.is_active" label="Visible on the public site" description="Hidden categories keep their departments but are not shown." />
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create category' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this category?"
            message="Departments inside it will become uncategorised. This cannot be undone."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
