<script setup lang="ts">
import DataTable from '../../../../../CORE/Resources/js/Components/DataTable/DataTable.vue';
import Button from '../../../../../CORE/Resources/js/Components/ui/Button.vue';
import ConfirmDialog from '../../../../../CORE/Resources/js/Components/ui/ConfirmDialog.vue';
import Dropdown from '../../../../../CORE/Resources/js/Components/ui/Dropdown.vue';
import DropdownItem from '../../../../../CORE/Resources/js/Components/ui/DropdownItem.vue';
import FormField from '../../../../../CORE/Resources/js/Components/ui/FormField.vue';
import Input from '../../../../../CORE/Resources/js/Components/ui/Input.vue';
import Modal from '../../../../../CORE/Resources/js/Components/ui/Modal.vue';
import PageHeader from '../../../../../CORE/Resources/js/Components/ui/PageHeader.vue';
import Select from '../../../../../CORE/Resources/js/Components/ui/Select.vue';
import Textarea from '../../../../../CORE/Resources/js/Components/ui/Textarea.vue';
import { usePermission } from '../../../../../CORE/Resources/js/Composables/usePermission';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { MoreHorizontal, Pencil, Plus, Sparkles, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated, SelectOption } from '../../../../../CORE/Resources/js/Types';

interface ExpertiseRow {
    id: number;
    doctor_id: number;
    doctor_name: string | null;
    title: string;
    description: string | null;
    icon: string | null;
    sort_order: number;
    created_at_human: string | null;
}

const props = defineProps<{
    expertises: Paginated<ExpertiseRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    doctorOptions: SelectOption[];
}>();

const { can } = usePermission();

const columns: DataTableColumn[] = [
    { key: 'title', label: 'Focus area', sortable: true },
    { key: 'doctor.name', label: 'Doctor', sortable: true, width: '14rem', hideBelow: 'md' },
    { key: 'sort_order', label: 'Order', sortable: true, align: 'center', width: '6rem', hideBelow: 'lg' },
    { key: 'created_at', label: 'Added', sortable: true, align: 'right', width: '9rem', hideBelow: 'xl' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    { key: 'doctor_id', label: 'Doctor', options: props.doctorOptions },
]);

const form = useForm({
    doctor_id: '' as string | number,
    title: '',
    description: '',
    icon: '',
    sort_order: 0,
});

const modalOpen = ref(false);
const editing = ref<ExpertiseRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: ExpertiseRow): void {
    editing.value = row;
    form.doctor_id = row.doctor_id;
    form.title = row.title;
    form.description = row.description ?? '';
    form.icon = row.icon ?? '';
    form.sort_order = row.sort_order;
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
            toast.success({ title: isEditing.value ? 'Expertise updated' : 'Expertise added' });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/doctors/expertises/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/doctors/expertises', options);
}

const confirmOpen = ref(false);
const pendingDelete = ref<ExpertiseRow | null>(null);
const deleting = ref(false);

function askDelete(row: ExpertiseRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/expertises/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Expertise deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That expertise could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Doctor Expertises" />

        <PageHeader
            title="Doctor Expertises"
            description="The focus areas listed on each doctor's public profile."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Expertises' }]"
        >
            <template #actions>
                <Button v-if="can('doctor.update')" @click="openCreate">
                    <Plus class="size-4" />
                    New expertise
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="expertises.data"
            :meta="expertises"
            url="/admin/doctors/expertises"
            :only="['expertises']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search focus areas…"
            empty-title="No expertises found"
            empty-description="Add the conditions and procedures each doctor specialises in."
        >
            <template #cell-title="{ row }">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">{{ row.title }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ row.description || '—' }}</p>
                </div>
            </template>

            <template #cell-doctor.name="{ row }">
                <span class="truncate text-sm text-foreground">{{ row.doctor_name }}</span>
            </template>

            <template #cell-sort_order="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.sort_order }}</span>
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

                    <DropdownItem v-if="can('doctor.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit
                    </DropdownItem>

                    <DropdownItem v-if="can('doctor.update')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit expertise' : 'New expertise'"
            description="Keep titles short — they are rendered as cards on the profile page."
            @close="closeModal"
        >
            <form class="space-y-4 p-5" @submit.prevent="submit">
                <FormField label="Doctor" required :error="form.errors.doctor_id">
                    <template #default="{ id, describedBy, invalid }">
                        <Select
                            :id="id"
                            v-model="form.doctor_id"
                            :options="doctorOptions"
                            placeholder="Choose a doctor"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
                    </template>
                </FormField>

                <FormField label="Title" required :error="form.errors.title">
                    <template #default="{ id, describedBy, invalid }">
                        <Input :id="id" v-model="form.title" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </FormField>

                <FormField label="Description" :error="form.errors.description">
                    <template #default="{ id, describedBy, invalid }">
                        <Textarea
                            :id="id"
                            v-model="form.description"
                            :rows="3"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
                    </template>
                </FormField>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <FormField label="Icon" hint="Lucide name or class.">
                        <template #default="{ id, describedBy, invalid }">
                            <Input :id="id" v-model="form.icon" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </FormField>

                    <FormField label="Sort order">
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
                </div>
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    <Sparkles class="size-4" />
                    {{ isEditing ? 'Save changes' : 'Add expertise' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this expertise?"
            message="It will be removed from the doctor's public profile."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
