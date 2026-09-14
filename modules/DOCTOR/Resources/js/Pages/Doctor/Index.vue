<script setup lang="ts">
import DataTable from '../../../../../CORE/Resources/js/Components/DataTable/DataTable.vue';
import Avatar from '../../../../../CORE/Resources/js/Components/ui/Avatar.vue';
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
import {
    CalendarClock,
    GripVertical,
    MoreHorizontal,
    Pencil,
    Plus,
    Star,
    Stethoscope,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated, SelectOption } from '../../../../../CORE/Resources/js/Types';

type ExpertiseInput = {
    title: string;
    description: string;
    icon: string;
    sort_order: number;
};

interface DoctorRow {
    id: number;
    name: string;
    slug: string;
    initials: string;
    designation: string | null;
    specialty: string;
    qualification: string | null;
    experience: string | null;
    experience_years: number;
    hospital_name: string | null;
    location: string | null;
    profile_photo: string | null;
    photo_url: string;
    bio: string | null;
    rating: number;
    reviews_count: number;
    social_links: Record<string, string> | null;
    department_id: number;
    department_name: string | null;
    is_featured: boolean;
    is_active: boolean;
    expertises_count: number;
    schedules_count: number;
    expertises: ExpertiseInput[];
    created_at_human: string | null;
}

const props = defineProps<{
    doctors: Paginated<DoctorRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    departmentOptions: Record<number, string>;
}>();

const { can } = usePermission();

const columns: DataTableColumn[] = [
    { key: 'name', label: 'Doctor', sortable: true },
    { key: 'department.title', label: 'Department', sortable: true, width: '13rem', hideBelow: 'lg' },
    { key: 'experience_years', label: 'Experience', sortable: true, align: 'center', width: '7.5rem', hideBelow: 'xl' },
    { key: 'schedules_count', label: 'Slots', sortable: false, align: 'center', width: '6rem', hideBelow: 'md' },
    { key: 'is_featured', label: 'Featured', sortable: true, align: 'center', width: '7rem', hideBelow: 'md' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', width: '7rem' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'department_id',
        label: 'Department',
        options: Object.entries(props.departmentOptions).map(([id, title]) => ({ value: Number(id), label: title })),
    },
    {
        key: 'is_active',
        label: 'Status',
        options: [
            { value: 1, label: 'Active' },
            { value: 0, label: 'Inactive' },
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

/* ------------------------------- navigation ------------------------------ */

/**
 * Create and edit live on their own pages: the doctor form carries the
 * weekly timetable, expertises and SEO blocks, which is too much for a modal.
 */
function goToEdit(row: DoctorRow): void {
    router.visit(`/admin/doctors/${row.id}/edit`);
}

/* -------------------------------- actions -------------------------------- */

function toggleFlag(row: DoctorRow, endpoint: 'featured' | 'status'): void {
    router.patch(
        `/admin/doctors/${row.id}/${endpoint}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success({ title: endpoint === 'featured' ? 'Featured state updated' : 'Availability updated' }),
            onError: () => toast.error({ title: 'That change could not be saved.' }),
        },
    );
}

const confirmOpen = ref(false);
const pendingDelete = ref<DoctorRow | null>(null);
const deleting = ref(false);

function askDelete(row: DoctorRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Doctor removed', description: `${pendingDelete.value?.name} left the directory.` });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That doctor could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Doctors" />

        <PageHeader
            title="Doctors"
            description="Every consultant in the directory, with their department, expertise and availability."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Doctors' }]"
        >
            <template #actions>
                <Button v-if="can('doctor.create')" href="/admin/doctors/create">
                    <Plus class="size-4" />
                    New doctor
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="doctors.data"
            :meta="doctors"
            url="/admin/doctors"
            :only="['doctors']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search by name, specialty or qualification…"
            empty-title="No doctors found"
            empty-description="Add your first consultant to start building the directory."
        >
            <template #cell-name="{ row }">
                <div class="flex items-center gap-3">
                    <Avatar :name="row.name" :src="row.profile_photo ? row.photo_url : null" size="lg" />

                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-foreground">{{ row.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">
                            {{ row.designation ? `${row.designation} · ` : '' }}{{ row.specialty }}
                        </p>
                    </div>
                </div>
            </template>

            <template #cell-department.title="{ row }">
                <span class="truncate text-sm text-foreground">{{ row.department_name }}</span>
            </template>

            <template #cell-experience_years="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.experience_years }} yrs</span>
            </template>

            <template #cell-schedules_count="{ row }">
                <span class="inline-flex items-center gap-1 text-sm text-muted-foreground">
                    <CalendarClock class="size-3.5" />
                    {{ row.schedules_count }}
                </span>
            </template>

            <template #cell-is_featured="{ row }">
                <button
                    v-if="can('doctor.update')"
                    type="button"
                    class="cursor-pointer rounded p-1 transition hover:bg-accent"
                    :title="row.is_featured ? 'Remove from featured' : 'Mark as featured'"
                    @click="toggleFlag(row, 'featured')"
                >
                    <Star :class="row.is_featured ? 'size-4 text-amber-500' : 'size-4 text-muted-foreground/40'" />
                </button>
                <span v-else class="text-sm text-muted-foreground">{{ row.is_featured ? 'Yes' : 'No' }}</span>
            </template>

            <template #cell-is_active="{ row }">
                <button
                    v-if="can('doctor.update')"
                    type="button"
                    class="cursor-pointer"
                    @click="toggleFlag(row, 'status')"
                >
                    <Badge :tone="row.is_active ? 'success' : 'neutral'" dot>
                        {{ row.is_active ? 'Active' : 'Inactive' }}
                    </Badge>
                </button>
                <Badge v-else :tone="row.is_active ? 'success' : 'neutral'" dot>
                    {{ row.is_active ? 'Active' : 'Inactive' }}
                </Badge>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem v-if="can('doctor.update')" @click="goToEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit doctor
                    </DropdownItem>

                    <DropdownItem v-if="can('doctor.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

            :open="confirmOpen"
            title="Delete this doctor?"
            message="Their expertises and schedule slots are removed too. This cannot be undone."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
