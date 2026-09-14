<script setup lang="ts">
import DataTable from '../../../../../CORE/Resources/js/Components/DataTable/DataTable.vue';
import Badge from '../../../../../CORE/Resources/js/Components/ui/Badge.vue';
import Button from '../../../../../CORE/Resources/js/Components/ui/Button.vue';
import ConfirmDialog from '../../../../../CORE/Resources/js/Components/ui/ConfirmDialog.vue';
import Dropdown from '../../../../../CORE/Resources/js/Components/ui/Dropdown.vue';
import DropdownItem from '../../../../../CORE/Resources/js/Components/ui/DropdownItem.vue';
import PageHeader from '../../../../../CORE/Resources/js/Components/ui/PageHeader.vue';
import { usePermission } from '../../../../../CORE/Resources/js/Composables/usePermission';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Building2, MoreHorizontal, Pencil, Plus, Sparkles, Star, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../../../CORE/Resources/js/Types';

interface DepartmentRow {
    id: number;
    title: string;
    slug: string;
    short_description: string | null;
    icon: string | null;
    featured_image: string | null;
    category_id: number | null;
    category_name: string | null;
    sort_order: number;
    is_popular_search: boolean;
    is_featured: boolean;
    is_active: boolean;
    doctors_count: number;
    created_at_human: string | null;
}

const props = defineProps<{
    departments: Paginated<DepartmentRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    categoryOptions: Array<{ id: number; title: string }>;
}>();

const { can } = usePermission();

const columns: DataTableColumn[] = [
    { key: 'title', label: 'Department', sortable: true },
    { key: 'category_name', label: 'Category', sortable: true, width: '11rem', hideBelow: 'lg' },
    { key: 'doctors_count', label: 'Doctors', sortable: false, align: 'center', width: '7rem' },
    { key: 'page_sections_count', label: 'Sections', sortable: false, align: 'center', width: '7rem', hideBelow: 'lg' },
    { key: 'symptoms_count', label: 'Symptoms', sortable: false, align: 'center', width: '8rem', hideBelow: 'xl' },
    { key: 'is_featured', label: 'Featured', sortable: true, align: 'center', width: '7rem', hideBelow: 'md' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', width: '7rem' },
    { key: 'sort_order', label: 'Order', sortable: true, align: 'center', width: '6rem', hideBelow: 'xl' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'department_category_id',
        label: 'Category',
        options: props.categoryOptions.map((option) => ({ value: option.id, label: option.title })),
    },
    {
        key: 'is_active',
        label: 'Status',
        options: [
            { value: 1, label: 'Active' },
            { value: 0, label: 'Hidden' },
        ],
    },
    {
        key: 'is_featured',
        label: 'Featured',
        options: [
            { value: 1, label: 'Featured only' },
            { value: 0, label: 'Not featured' },
        ],
    },
]);

const selectableCategories = computed(() =>
    props.categoryOptions.map((option) => ({
        value: option.id,
        label: option.title,
    })),
);

/* ------------------------------- navigation ------------------------------ */

/**
 * Create and edit live on their own pages: the department form carries the
 * page sections, symptoms and SEO blocks, which is too much for a modal.
 */
function goToEdit(row: DepartmentRow): void {
    router.visit(`/admin/doctors/departments/${row.id}/edit`);
}

/* -------------------------------- actions -------------------------------- */

function toggleFeatured(row: DepartmentRow): void {
    router.patch(
        `/admin/doctors/departments/${row.id}/featured`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success({ title: row.is_featured ? 'Removed from featured' : 'Marked as featured' }),
            onError: () => toast.error({ title: 'That change could not be saved.' }),
        },
    );
}

const confirmOpen = ref(false);
const pendingDelete = ref<DepartmentRow | null>(null);
const deleting = ref(false);

function askDelete(row: DepartmentRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/departments/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Department deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'This department still has doctors assigned to it.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Departments" />

        <PageHeader
            title="Departments"
            description="The clinical specialities patients can browse and search against."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Departments' }]"
        >
            <template #actions>
                <Button v-if="can('department.create')" href="/admin/doctors/departments/create">
                    <Plus class="size-4" />
                    New department
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="departments.data"
            :meta="departments"
            url="/admin/doctors/departments"
            :only="['departments']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort || 'sort_order'"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search departments…"
            empty-title="No departments found"
            empty-description="Add a department so doctors can be grouped by speciality."
        >
            <template #cell-title="{ row }">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">{{ row.title }}</p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ row.short_description || `/departments/${row.slug}` }}
                    </p>
                </div>
            </template>

            <template #cell-category_name="{ row }">
                <Badge v-if="row.category_name" tone="violet" outline>{{ row.category_name }}</Badge>
                <span v-else class="text-sm text-muted-foreground">Uncategorised</span>
            </template>

            <template #cell-doctors_count="{ row }">
                <Badge :tone="row.doctors_count > 0 ? 'brand' : 'neutral'" dot>{{ row.doctors_count }}</Badge>
            </template>

            <template #cell-is_featured="{ row }">
                <button
                    v-if="can('department.update')"
                    type="button"
                    class="cursor-pointer rounded p-1 transition hover:bg-accent"
                    :title="row.is_featured ? 'Remove from featured' : 'Mark as featured'"
                    @click="toggleFeatured(row)"
                >
                    <Star :class="row.is_featured ? 'size-4 text-amber-500' : 'size-4 text-muted-foreground/40'" />
                </button>
                <span v-else class="text-sm text-muted-foreground">{{ row.is_featured ? 'Yes' : 'No' }}</span>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :tone="row.is_active ? 'success' : 'neutral'" dot>{{ row.is_active ? 'Active' : 'Hidden' }}</Badge>
            </template>

            <template #cell-sort_order="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.sort_order }}</span>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem v-if="can('department.update')" @click="goToEdit(row)">
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

            :open="confirmOpen"
            title="Delete this department?"
            message="Doctors assigned to it must be moved first. This cannot be undone."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
