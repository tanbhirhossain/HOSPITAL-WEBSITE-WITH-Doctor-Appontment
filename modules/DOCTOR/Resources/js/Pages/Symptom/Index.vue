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
import Textarea from '../../../../../CORE/Resources/js/Components/ui/Textarea.vue';
import { usePermission } from '../../../../../CORE/Resources/js/Composables/usePermission';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { HeartPulse, MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../../../CORE/Resources/js/Types';

interface SymptomRow {
    id: number;
    title: string;
    icon: string | null;
    link_url: string | null;
    sort_order: number;
    is_active: boolean;
    created_at_human: string | null;
}

const props = defineProps<{
    symptoms: Paginated<SymptomRow>;
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
    { key: 'title', label: 'Symptom', sortable: true },
    { key: 'link_url', label: 'Links to', sortable: false, width: '18rem', hideBelow: 'md' },
    { key: 'sort_order', label: 'Order', sortable: true, align: 'center', width: '6rem', hideBelow: 'md' },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', width: '7rem' },
];

type SymptomForm = {
    title: string;
    icon: string;
    link_url: string;
    sort_order: number;
    is_active: boolean;
};

const form = useForm<SymptomForm>({ title: '', icon: '', link_url: '', sort_order: 0, is_active: true });

const modalOpen = ref(false);
const editing = ref<SymptomRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: SymptomRow): void {
    editing.value = row;
    form.title = row.title;
    form.icon = row.icon ?? '';
    form.link_url = row.link_url ?? '';
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
            toast.success({ title: isEditing.value ? 'Symptom updated' : 'Symptom created' });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/doctors/symptoms/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/doctors/symptoms', options);
}

const confirmOpen = ref(false);
const pendingDelete = ref<SymptomRow | null>(null);
const deleting = ref(false);

function askDelete(row: SymptomRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/symptoms/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Symptom deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That symptom could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Symptoms" />

        <PageHeader
            title="Symptoms"
            description="The “what brings you here” tiles shown on the public homepage."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Symptoms' }]"
        >
            <template #actions>
                <Button v-if="can('symptom.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New symptom
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="symptoms.data"
            :meta="symptoms"
            url="/admin/doctors/symptoms"
            :only="['symptoms']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort || 'sort_order'"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search symptoms…"
            empty-title="No symptoms yet"
            empty-description="Add the common reasons patients visit so they can self-direct."
        >
            <template #cell-title="{ row }">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-rose-50 dark:bg-rose-950/50">
                        <HeartPulse class="size-4 text-rose-600 dark:text-rose-400" />
                    </span>
                    <span class="truncate font-medium text-foreground">{{ row.title }}</span>
                </div>
            </template>

            <template #cell-link_url="{ row }">
                <span class="font-mono text-xs text-muted-foreground">{{ row.link_url || '—' }}</span>
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

                    <DropdownItem v-if="can('symptom.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit
                    </DropdownItem>

                    <DropdownItem v-if="can('symptom.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit symptom' : 'New symptom'"
            description="Tiles appear in the order set below."
            @close="closeModal"
        >
            <form class="space-y-4 p-5" @submit.prevent="submit">
                <FormField label="Title" required :error="form.errors.title">
                    <template #default="{ id, describedBy, invalid }">
                        <Input :id="id" v-model="form.title" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </FormField>

                <FormField label="Link URL" :error="form.errors.link_url" hint="Where the tile sends the visitor.">
                    <template #default="{ id, describedBy, invalid }">
                        <Input :id="id" v-model="form.link_url" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </FormField>

                <FormField label="Icon" :error="form.errors.icon" hint="Paste inline SVG, or an icon class name.">
                    <template #default="{ id, describedBy, invalid }">
                        <Textarea
                            :id="id"
                            v-model="form.icon"
                            :rows="3"
                            class="font-mono text-xs"
                            :aria-describedby="describedBy"
                            :invalid="invalid"
                        />
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

                <Switch v-model="form.is_active" label="Active" description="Hidden tiles stay saved but are not rendered." />
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create symptom' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this symptom?"
            message="The tile will be removed from the public homepage."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
