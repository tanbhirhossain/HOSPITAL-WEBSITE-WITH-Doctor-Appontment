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
import { LayoutPanelTop, MoreHorizontal, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../../../../CORE/Resources/js/Types';

interface SectionRow {
    id: number;
    section_key: string;
    badge: string | null;
    title: string;
    subtitle: string | null;
    primary_button_text: string | null;
    primary_button_url: string | null;
    secondary_button_text: string | null;
    secondary_button_url: string | null;
    image: string | null;
    is_active: boolean;
    updated_at_human: string | null;
}

const props = defineProps<{
    sections: Paginated<SectionRow>;
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
    { key: 'section_key', label: 'Section', sortable: true, width: '13rem' },
    { key: 'title', label: 'Headline', sortable: false },
    { key: 'is_active', label: 'Status', sortable: true, align: 'center', width: '7rem' },
    { key: 'updated_at', label: 'Updated', sortable: true, align: 'right', width: '9rem', hideBelow: 'lg' },
];

type SectionForm = {
    section_key: string;
    badge: string;
    title: string;
    subtitle: string;
    primary_button_text: string;
    primary_button_url: string;
    secondary_button_text: string;
    secondary_button_url: string;
    image: string;
    is_active: boolean;
};

const form = useForm<SectionForm>({
    section_key: '',
    badge: '',
    title: '',
    subtitle: '',
    primary_button_text: '',
    primary_button_url: '',
    secondary_button_text: '',
    secondary_button_url: '',
    image: '',
    is_active: true,
});

const modalOpen = ref(false);
const editing = ref<SectionRow | null>(null);
const isEditing = computed(() => editing.value !== null);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: SectionRow): void {
    editing.value = row;
    form.section_key = row.section_key;
    form.badge = row.badge ?? '';
    form.title = row.title;
    form.subtitle = row.subtitle ?? '';
    form.primary_button_text = row.primary_button_text ?? '';
    form.primary_button_url = row.primary_button_url ?? '';
    form.secondary_button_text = row.secondary_button_text ?? '';
    form.secondary_button_url = row.secondary_button_url ?? '';
    form.image = row.image ?? '';
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
            toast.success({ title: isEditing.value ? 'Section updated' : 'Section created' });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/doctors/page-sections/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/doctors/page-sections', options);
}

const confirmOpen = ref(false);
const pendingDelete = ref<SectionRow | null>(null);
const deleting = ref(false);

function askDelete(row: SectionRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/doctors/page-sections/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'Section deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That section could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Page Sections" />

        <PageHeader
            title="Page Sections"
            description="Editable marketing copy for the public site — no deploy required."
            :breadcrumbs="[{ title: 'DOCTOR' }, { title: 'Page sections' }]"
        >
            <template #actions>
                <Button v-if="can('page-section.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New section
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="sections.data"
            :meta="sections"
            url="/admin/doctors/page-sections"
            :only="['sections']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort || 'section_key'"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search sections…"
            empty-title="No page sections yet"
            empty-description="Create a section to make its copy editable from here."
        >
            <template #cell-section_key="{ row }">
                <div class="flex items-center gap-2.5">
                    <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-sky-50 dark:bg-sky-950/50">
                        <LayoutPanelTop class="size-4 text-sky-600 dark:text-sky-400" />
                    </span>
                    <code class="font-mono text-xs text-foreground">{{ row.section_key }}</code>
                </div>
            </template>

            <template #cell-title="{ row }">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">{{ row.title }}</p>
                    <p class="truncate text-xs text-muted-foreground">{{ row.badge || row.subtitle || '—' }}</p>
                </div>
            </template>

            <template #cell-is_active="{ row }">
                <Badge :tone="row.is_active ? 'success' : 'neutral'" dot>{{ row.is_active ? 'Live' : 'Hidden' }}</Badge>
            </template>

            <template #cell-updated_at="{ row }">
                <span class="text-sm text-muted-foreground">{{ row.updated_at_human ?? '—' }}</span>
            </template>

            <template #row-actions="{ row }">
                <Dropdown align="right">
                    <template #trigger="{ toggle }">
                        <Button variant="ghost" size="icon-sm" aria-label="Row actions" @click="toggle">
                            <MoreHorizontal class="size-4" />
                        </Button>
                    </template>

                    <DropdownItem v-if="can('page-section.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit copy
                    </DropdownItem>

                    <DropdownItem v-if="can('page-section.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit section' : 'New section'"
            description="The key identifies where this copy is rendered on the public site."
            size="xl"
            @close="closeModal"
        >
            <form class="space-y-4 p-5" @submit.prevent="submit">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <FormField label="Section key" required :error="form.errors.section_key" hint="e.g. hero, bottom_cta">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.section_key"
                                class="font-mono"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>

                    <FormField label="Badge" hint="Small label above the headline.">
                        <template #default="{ id, describedBy, invalid }">
                            <Input :id="id" v-model="form.badge" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </FormField>
                </div>

                <FormField label="Title" required :error="form.errors.title">
                    <template #default="{ id, describedBy, invalid }">
                        <Input :id="id" v-model="form.title" :aria-describedby="describedBy" :invalid="invalid" />
                    </template>
                </FormField>

                <FormField label="Subtitle">
                    <template #default="{ id }">
                        <Textarea :id="id" v-model="form.subtitle" :rows="3" />
                    </template>
                </FormField>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <FormField label="Primary button text">
                        <template #default="{ id }">
                            <Input :id="id" v-model="form.primary_button_text" />
                        </template>
                    </FormField>

                    <FormField label="Primary button URL">
                        <template #default="{ id }">
                            <Input :id="id" v-model="form.primary_button_url" />
                        </template>
                    </FormField>

                    <FormField label="Secondary button text">
                        <template #default="{ id }">
                            <Input :id="id" v-model="form.secondary_button_text" />
                        </template>
                    </FormField>

                    <FormField label="Secondary button URL">
                        <template #default="{ id }">
                            <Input :id="id" v-model="form.secondary_button_url" />
                        </template>
                    </FormField>
                </div>

                <FormField label="Image URL">
                    <template #default="{ id }">
                        <Input :id="id" v-model="form.image" />
                    </template>
                </FormField>

                <Switch v-model="form.is_active" label="Live" description="Inactive sections are not rendered publicly." />
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create section' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this section?"
            message="The public page will fall back to its built-in default copy."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
