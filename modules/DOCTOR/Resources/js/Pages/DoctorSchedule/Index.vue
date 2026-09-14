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
import Select from '../../../../../CORE/Resources/js/Components/ui/Select.vue';
import Switch from '../../../../../CORE/Resources/js/Components/ui/Switch.vue';
import { usePermission } from '../../../../../CORE/Resources/js/Composables/usePermission';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarClock, MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated, SelectOption } from '../../../../../CORE/Resources/js/Types';

interface ScheduleRow {
    id: number;
    doctor_id: number;
    doctor_name: string | null;
    day_of_week: string;
    start_time: string;
    end_time: string;
    time_range: string;
    consultation_type: string;
    availability_status: string;
    max_patients: number;
    is_active: boolean;
    created_at_human: string | null;
}

const props = defineProps<{
    schedules: Paginated<ScheduleRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    doctorOptions: SelectOption[];
    dayOptions: SelectOption[];
    consultationTypeOptions: SelectOption[];
    availabilityOptions: SelectOption[];
}>();

const { can } = usePermission();

const columns: DataTableColumn[] = [
    { key: 'doctor.name', label: 'Doctor', sortable: true },
    { key: 'day_of_week', label: 'Day', sortable: true, width: '7rem' },
    { key: 'start_time', label: 'Time', sortable: true, width: '13rem' },
    { key: 'consultation_type', label: 'Type', sortable: true, align: 'center', width: '8rem', hideBelow: 'md' },
    { key: 'availability_status', label: 'Availability', sortable: true, align: 'center', width: '9rem', hideBelow: 'lg' },
    { key: 'max_patients', label: 'Capacity', sortable: true, align: 'center', width: '7rem', hideBelow: 'xl' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', width: '7rem' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    { key: 'doctor_id', label: 'Doctor', options: props.doctorOptions },
    { key: 'day_of_week', label: 'Day', options: props.dayOptions },
    { key: 'consultation_type', label: 'Type', options: props.consultationTypeOptions },
    { key: 'availability_status', label: 'Availability', options: props.availabilityOptions },
]);

const CONSULTATION_TONES: Record<string, 'brand' | 'violet' | 'neutral'> = {
    in_person: 'brand',
    online: 'violet',
    both: 'neutral',
};

const AVAILABILITY_TONES: Record<string, 'success' | 'warning' | 'danger'> = {
    available: 'success',
    limited: 'warning',
    booked_out: 'danger',
};

const label = (options: SelectOption[], value: string) =>
    options.find((option) => String(option.value) === value)?.label ?? value;

/* ------------------------------- form state ------------------------------ */

type ScheduleForm = {
    doctor_id: string | number;
    day_of_week: string;
    start_time: string;
    end_time: string;
    consultation_type: string;
    availability_status: string;
    max_patients: number;
    is_active: boolean;
};

const form = useForm<ScheduleForm>({
    doctor_id: '' as string | number,
    day_of_week: 'Sat',
    start_time: '09:00',
    end_time: '17:00',
    consultation_type: 'in_person',
    availability_status: 'available',
    max_patients: 20,
    is_active: true,
});

const modalOpen = ref(false);
const editing = ref<ScheduleRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: ScheduleRow): void {
    editing.value = row;
    form.doctor_id = row.doctor_id;
    form.day_of_week = row.day_of_week;
    form.start_time = row.start_time?.slice(0, 5) ?? '09:00';
    form.end_time = row.end_time?.slice(0, 5) ?? '17:00';
    form.consultation_type = row.consultation_type;
    form.availability_status = row.availability_status;
    form.max_patients = row.max_patients;
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
            toast.success({ title: isEditing.value ? 'Slot updated' : 'Slot created' });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/doctors/schedules/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/doctors/schedules', options);
}

const confirmOpen = ref(false);
const pendingDelete = ref<ScheduleRow | null>(null);
const deleting = ref(false);

function askDelete(row: ScheduleRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/schedules/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Slot deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That slot could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Schedules" />

        <PageHeader
            title="Doctor Schedules"
            description="Weekly consulting slots, shown on each doctor's public profile."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Schedules' }]"
        >
            <template #actions>
                <Button v-if="can('schedule.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New slot
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="schedules.data"
            :meta="schedules"
            url="/admin/doctors/schedules"
            :only="['schedules']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort || 'day_of_week'"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search by doctor or day…"
            empty-title="No schedule slots found"
            empty-description="Add the times each doctor is available for consultation."
        >
            <template #cell-doctor.name="{ row }">
                <span class="truncate font-medium text-foreground">{{ row.doctor_name }}</span>
            </template>

            <template #cell-day_of_week="{ row }">
                <Badge tone="neutral" outline>{{ label(dayOptions, row.day_of_week) }}</Badge>
            </template>

            <template #cell-start_time="{ row }">
                <span class="inline-flex items-center gap-1.5 text-sm text-foreground">
                    <CalendarClock class="size-3.5 text-muted-foreground" />
                    {{ row.time_range }}
                </span>
            </template>

            <template #cell-consultation_type="{ row }">
                <Badge :tone="CONSULTATION_TONES[row.consultation_type] ?? 'neutral'" outline>
                    {{ label(consultationTypeOptions, row.consultation_type) }}
                </Badge>
            </template>

            <template #cell-availability_status="{ row }">
                <Badge :tone="AVAILABILITY_TONES[row.availability_status] ?? 'neutral'" dot>
                    {{ label(availabilityOptions, row.availability_status) }}
                </Badge>
            </template>

            <template #cell-max_patients="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.max_patients }}</span>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :tone="row.is_active ? 'success' : 'neutral'" dot>{{ row.is_active ? 'Active' : 'Off' }}</Badge>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem v-if="can('schedule.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit
                    </DropdownItem>

                    <DropdownItem v-if="can('schedule.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit slot' : 'New slot'"
            description="A slot repeats weekly. The same doctor cannot have two slots starting at the same time on one day."
            size="lg"
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

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <FormField label="Day" required :error="form.errors.day_of_week">
                        <template #default="{ id, describedBy, invalid }">
                            <Select
                                :id="id"
                                v-model="form.day_of_week"
                                :options="dayOptions"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>

                    <FormField label="Starts" required :error="form.errors.start_time">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.start_time"
                                type="time"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>

                    <FormField label="Ends" required :error="form.errors.end_time">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.end_time"
                                type="time"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <FormField label="Consultation type">
                        <template #default="{ id }">
                            <Select :id="id" v-model="form.consultation_type" :options="consultationTypeOptions" />
                        </template>
                    </FormField>

                    <FormField label="Availability">
                        <template #default="{ id }">
                            <Select :id="id" v-model="form.availability_status" :options="availabilityOptions" />
                        </template>
                    </FormField>

                    <FormField label="Patient limit" :error="form.errors.max_patients">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.max_patients"
                                type="number"
                                min="1"
                                max="500"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>
                </div>

                <Switch v-model="form.is_active" label="Active" description="Inactive slots are hidden on the public profile." />
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create slot' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this slot?"
            message="Patients will no longer see this consulting time."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
