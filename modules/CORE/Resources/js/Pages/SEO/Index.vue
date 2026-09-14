<script setup lang="ts">
import DataTable from '../../Components/DataTable/DataTable.vue';
import Badge from '../../Components/ui/Badge.vue';
import Button from '../../Components/ui/Button.vue';
import ConfirmDialog from '../../Components/ui/ConfirmDialog.vue';
import Dropdown from '../../Components/ui/Dropdown.vue';
import DropdownItem from '../../Components/ui/DropdownItem.vue';
import FormField from '../../Components/ui/FormField.vue';
import Input from '../../Components/ui/Input.vue';
import Modal from '../../Components/ui/Modal.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import Select from '../../Components/ui/Select.vue';
import Textarea from '../../Components/ui/Textarea.vue';
import { usePermission } from '../../Composables/usePermission';
import { toast } from '../../Composables/useToast';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { FileSearch, MoreHorizontal, Pencil, Plus, Search, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated, SelectOption } from '../../Types';

interface SeoRow {
    id: number;
    meta_title: string | null;
    meta_description: string | null;
    canonical_url: string | null;
    robots: string | null;
    seoable_type: string | null;
    seoable_type_label: string;
    seoable_id: number | null;
    owner_label: string;
    is_complete: boolean;
    updated_at_human: string | null;
}

const props = defineProps<{
    records: Paginated<SeoRow>;
    filters: Record<string, unknown>;
    query: { search: string; sort: string; direction: 'asc' | 'desc'; per_page: number };
    seoableTypes: SelectOption[];
}>();

const { can } = usePermission();

const columns: DataTableColumn[] = [
    { key: 'meta_title', label: 'Meta title', sortable: true },
    { key: 'owner_label', label: 'Attached to', sortable: false, width: '14rem', hideBelow: 'lg' },
    { key: 'robots', label: 'Indexing', sortable: false, align: 'center', width: '9rem', hideBelow: 'md' },
    { key: 'updated_at', label: 'Updated', sortable: true, align: 'right', width: '9rem', hideBelow: 'lg' },
];

const filterDefs = computed<DataTableFilter[]>(() => [
    {
        key: 'seoable_type',
        label: 'Content type',
        options: props.seoableTypes.map((option) => ({ value: String(option.value), label: option.label })),
    },
]);

/* ------------------------------- form state ------------------------------ */

type SeoForm = {
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
    canonical_url: string;
    robots: string;
    og_title: string;
    og_description: string;
    og_image: string;
    og_type: string;
    twitter_card: string;
    twitter_title: string;
    twitter_description: string;
    twitter_image: string;
    schema_json: string;
};

const blank = (): SeoForm => ({
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    canonical_url: '',
    robots: 'index, follow',
    og_title: '',
    og_description: '',
    og_image: '',
    og_type: 'website',
    twitter_card: 'summary_large_image',
    twitter_title: '',
    twitter_description: '',
    twitter_image: '',
    schema_json: '',
});

const form = useForm<SeoForm>(blank());

const modalOpen = ref(false);
const editing = ref<SeoRow | null>(null);
const isEditing = computed(() => editing.value !== null);

const ROBOTS_OPTIONS: SelectOption[] = [
    { value: 'index, follow', label: 'Index, follow (default)' },
    { value: 'noindex, follow', label: 'No index, follow' },
    { value: 'index, nofollow', label: 'Index, no follow' },
    { value: 'noindex, nofollow', label: 'No index, no follow' },
];

const OG_TYPES: SelectOption[] = [
    { value: 'website', label: 'Website' },
    { value: 'article', label: 'Article' },
    { value: 'product', label: 'Product' },
    { value: 'profile', label: 'Profile' },
];

const TWITTER_CARDS: SelectOption[] = [
    { value: 'summary', label: 'Summary' },
    { value: 'summary_large_image', label: 'Summary with large image' },
];

const characterCount = computed(() => form.meta_description.length);

function openCreate(): void {
    editing.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEdit(row: SeoRow): void {
    editing.value = row;

    form.meta_title = row.meta_title ?? '';
    form.meta_description = row.meta_description ?? '';
    form.canonical_url = row.canonical_url ?? '';
    form.robots = row.robots ?? 'index, follow';
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
                title: isEditing.value ? 'SEO metadata updated' : 'SEO metadata created',
            });
        },
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (editing.value) {
        form.put(`/admin/seo/${editing.value.id}`, options);

        return;
    }

    form.post('/admin/seo', options);
}

/* -------------------------------- deletion ------------------------------- */

const confirmOpen = ref(false);
const pendingDelete = ref<SeoRow | null>(null);
const deleting = ref(false);

function askDelete(row: SeoRow): void {
    pendingDelete.value = row;
    confirmOpen.value = true;
}

function confirmDelete(): void {
    if (!pendingDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/seo/${pendingDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success({ title: 'SEO metadata deleted' });
            confirmOpen.value = false;
            pendingDelete.value = null;
        },
        onError: () => toast.error({ title: 'That record could not be deleted.' }),
        onFinish: () => (deleting.value = false),
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="SEO Meta Data" />

        <PageHeader
            title="SEO Meta Data"
            description="Control how every public page appears in search results and social shares."
            :breadcrumbs="[{ title: 'CORE' }, { title: 'SEO' }]"
        >
            <template #actions>
                <Button v-if="can('seo.create')" @click="openCreate">
                    <Plus class="size-4" />
                    New record
                </Button>
            </template>
        </PageHeader>

        <DataTable
            :columns="columns"
            :rows="records.data"
            :meta="records"
            url="/admin/seo"
            :only="['records']"
            :filter-defs="filterDefs"
            :initial-filters="filters"
            :initial-search="query.search"
            :initial-sort="query.sort"
            :initial-direction="query.direction"
            :initial-per-page="query.per_page"
            search-placeholder="Search meta titles…"
            empty-title="No SEO records found"
            empty-description="Add metadata to control how a page is indexed and shared."
        >
            <template #cell-meta_title="{ row }">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-foreground">{{ row.meta_title ?? 'Untitled' }}</p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ row.meta_description || 'No description provided' }}
                    </p>
                </div>
            </template>

            <template #cell-owner_label="{ row }">
                <Badge tone="violet" outline>{{ row.seoable_type_label }}</Badge>
                <span class="ml-1.5 text-xs text-muted-foreground">#{{ row.seoable_id ?? '—' }}</span>
            </template>

            <template #cell-robots="{ row }">
                <Badge v-if="row.robots?.startsWith('index')" tone="success" dot>Indexed</Badge>
                <Badge v-else tone="warning" dot>Hidden</Badge>
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

                    <DropdownItem v-if="can('seo.update')" @click="openEdit(row)">
                        <Pencil class="size-4 text-muted-foreground" />
                        Edit
                    </DropdownItem>

                    <DropdownItem v-if="can('seo.delete')" tone="danger" @click="askDelete(row)">
                        <Trash2 class="size-4" />
                        Delete
                    </DropdownItem>
                </Dropdown>
            </template>
        </DataTable>

        <Modal
            :open="modalOpen"
            :title="isEditing ? 'Edit SEO metadata' : 'New SEO record'"
            description="Fill in the tags search engines and social networks read for this page."
            size="lg"
            @close="closeModal"
        >
            <form class="space-y-6 p-5" @submit.prevent="submit">
                <!-- Basic -->
                <section class="space-y-4">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                        <Search class="size-4 text-brand-600" />
                        Search engines
                    </h3>

                    <FormField label="Meta title" required :error="form.errors.meta_title">
                        <template #default="{ id, describedBy, invalid }">
                            <Input :id="id" v-model="form.meta_title" :aria-describedby="describedBy" :invalid="invalid" />
                        </template>
                    </FormField>

                    <FormField
                        label="Meta description"
                        :error="form.errors.meta_description"
                        :hint="`${characterCount} characters — around 155 is ideal.`"
                    >
                        <template #default="{ id, describedBy, invalid }">
                            <Textarea
                                :id="id"
                                v-model="form.meta_description"
                                :rows="3"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <FormField label="Keywords" hint="Comma separated.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input
                                    :id="id"
                                    v-model="form.meta_keywords"
                                    :aria-describedby="describedBy"
                                    :invalid="invalid"
                                />
                            </template>
                        </FormField>

                        <FormField label="Robots directive" :error="form.errors.robots">
                            <template #default="{ id, describedBy, invalid }">
                                <Select
                                    :id="id"
                                    v-model="form.robots"
                                    :options="ROBOTS_OPTIONS"
                                    :aria-describedby="describedBy"
                                    :invalid="invalid"
                                />
                            </template>
                        </FormField>
                    </div>

                    <FormField label="Canonical URL" :error="form.errors.canonical_url">
                        <template #default="{ id, describedBy, invalid }">
                            <Input
                                :id="id"
                                v-model="form.canonical_url"
                                type="url"
                                placeholder="https://"
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>
                </section>

                <!-- Social -->
                <section class="space-y-4 border-t border-border pt-5">
                    <h3 class="text-sm font-semibold text-foreground">Social sharing</h3>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <FormField label="Open Graph type">
                            <template #default="{ id }">
                                <Select :id="id" v-model="form.og_type" :options="OG_TYPES" />
                            </template>
                        </FormField>

                        <FormField label="Twitter card">
                            <template #default="{ id }">
                                <Select :id="id" v-model="form.twitter_card" :options="TWITTER_CARDS" />
                            </template>
                        </FormField>

                        <FormField label="OG title">
                            <template #default="{ id }">
                                <Input :id="id" v-model="form.og_title" />
                            </template>
                        </FormField>

                        <FormField label="Twitter title">
                            <template #default="{ id }">
                                <Input :id="id" v-model="form.twitter_title" />
                            </template>
                        </FormField>

                        <FormField label="OG image URL">
                            <template #default="{ id }">
                                <Input :id="id" v-model="form.og_image" placeholder="https://" />
                            </template>
                        </FormField>

                        <FormField label="Twitter image URL">
                            <template #default="{ id }">
                                <Input :id="id" v-model="form.twitter_image" placeholder="https://" />
                            </template>
                        </FormField>
                    </div>

                    <FormField label="OG description">
                        <template #default="{ id }">
                            <Textarea :id="id" v-model="form.og_description" :rows="2" />
                        </template>
                    </FormField>
                </section>

                <!-- Structured data -->
                <section class="space-y-4 border-t border-border pt-5">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-foreground">
                        <FileSearch class="size-4 text-brand-600" />
                        Structured data
                    </h3>

                    <FormField
                        label="Schema.org JSON-LD"
                        :error="form.errors.schema_json"
                        hint="Must be valid JSON. Leave empty to omit."
                    >
                        <template #default="{ id, describedBy, invalid }">
                            <Textarea
                                :id="id"
                                v-model="form.schema_json"
                                :rows="5"
                                class="font-mono text-xs"
                                placeholder='{ "@context": "https://schema.org", "@type": "Hospital" }'
                                :aria-describedby="describedBy"
                                :invalid="invalid"
                            />
                        </template>
                    </FormField>
                </section>
            </form>

            <template #footer>
                <Button variant="ghost" :disabled="form.processing" @click="closeModal">Cancel</Button>
                <Button :loading="form.processing" @click="submit">
                    {{ isEditing ? 'Save changes' : 'Create record' }}
                </Button>
            </template>
        </Modal>

        <ConfirmDialog
            :open="confirmOpen"
            title="Delete this metadata?"
            message="The page will fall back to the site-wide defaults."
            :processing="deleting"
            @close="confirmOpen = false"
            @confirm="confirmDelete"
        />
    </AdminLayout>
</template>
