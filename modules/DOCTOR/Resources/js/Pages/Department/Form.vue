<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Layers, Plus, Search, ShieldCheck, Sparkles, Stethoscope, Trash2 } from 'lucide-vue-next';

import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import PageHeader from '../../../../../CORE/Resources/js/Components/ui/PageHeader.vue';
import Card from '../../../../../CORE/Resources/js/Components/ui/Card.vue';
import Button from '../../../../../CORE/Resources/js/Components/ui/Button.vue';
import Input from '../../../../../CORE/Resources/js/Components/ui/Input.vue';
import Textarea from '../../../../../CORE/Resources/js/Components/ui/Textarea.vue';
import Select from '../../../../../CORE/Resources/js/Components/ui/Select.vue';
import Switch from '../../../../../CORE/Resources/js/Components/ui/Switch.vue';
import Checkbox from '../../../../../CORE/Resources/js/Components/ui/Checkbox.vue';
import FormField from '../../../../../CORE/Resources/js/Components/ui/FormField.vue';
import CollapsibleSection from '../../../../../CORE/Resources/js/Components/ui/CollapsibleSection.vue';
import EmptyState from '../../../../../CORE/Resources/js/Components/ui/EmptyState.vue';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import type { SelectOption } from '../../../../../CORE/Resources/js/Types';

interface PageSectionRow {
    id: number | null;
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
}

interface DepartmentPayload {
    id: number | null;
    department_category_id: number | null;
    title: string;
    slug: string;
    short_description: string;
    icon: string;
    featured_image: string;
    sort_order: number;
    is_popular_search: boolean;
    is_featured: boolean;
    is_active: boolean;
    page_sections: PageSectionRow[];
    symptoms: number[];
    seo: SeoPayload;
}

interface SeoPayload {
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
}

const props = defineProps<{
    department: DepartmentPayload | null;
    categoryOptions: Record<number, string>;
    symptomOptions: SelectOption[];
    sectionKeyOptions: SelectOption[];
}>();

const isEditing = computed(() => props.department !== null);

const emptySeo = (): SeoPayload => ({
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

const emptySection = (): PageSectionRow => ({
    id: null,
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

const form = useForm({
    department_category_id: props.department?.department_category_id ?? null,
    title: props.department?.title ?? '',
    slug: props.department?.slug ?? '',
    short_description: props.department?.short_description ?? '',
    icon: props.department?.icon ?? '',
    featured_image: props.department?.featured_image ?? '',
    sort_order: props.department?.sort_order ?? 0,
    is_popular_search: props.department?.is_popular_search ?? false,
    is_featured: props.department?.is_featured ?? false,
    is_active: props.department?.is_active ?? true,
    page_sections: (props.department?.page_sections ?? []).map((row) => ({ ...emptySection(), ...row })),
    symptoms: props.department?.symptoms ?? [],
    seo: { ...emptySeo(), ...(props.department?.seo ?? {}) },
});

/* ------------------------------ options ------------------------------- */

const categories: SelectOption[] = [
    { value: '', label: 'Uncategorised' },
    ...Object.entries(props.categoryOptions ?? {}).map(([id, name]) => ({ value: Number(id), label: name })),
];

const robotsOptions: SelectOption[] = [
    { value: 'index, follow', label: 'index, follow' },
    { value: 'noindex, follow', label: 'noindex, follow' },
    { value: 'index, nofollow', label: 'index, nofollow' },
    { value: 'noindex, nofollow', label: 'noindex, nofollow' },
];

const ogTypeOptions: SelectOption[] = ['website', 'article', 'product', 'profile'].map((value) => ({
    value,
    label: value.charAt(0).toUpperCase() + value.slice(1),
}));

const twitterCardOptions: SelectOption[] = ['summary', 'summary_large_image', 'app', 'player'].map((value) => ({
    value,
    label: value.replace(/_/g, ' '),
}));

/* --------------------------- page sections ---------------------------- */

const formErrors = computed(() => form.errors as Record<string, string | undefined>);

/** Inertia types `form.errors` to top-level keys only; nested errors need a cast. */
const seoError = (field: string): string | undefined => formErrors.value[`seo.${field}`];

const sectionError = (index: number, field: string): string | undefined => formErrors.value[`page_sections.${index}.${field}`];

function hasSectionErrors(): boolean {
    return Object.keys(form.errors).some((key) => key.startsWith('page_sections.'));
}

function addSection(): void {
    form.page_sections.push(emptySection());
}

function addPresetSection(): void {
    const used = new Set(form.page_sections.map((row) => row.section_key));
    const preset = props.sectionKeyOptions.find((option) => !used.has(String(option.value)));

    form.page_sections.push({
        ...emptySection(),
        section_key: preset ? String(preset.value) : '',
        title: preset ? preset.label : '',
    });
}

function removeSection(index: number): void {
    form.page_sections.splice(index, 1);
}

/** Free-text keys are allowed; the preset dropdown just shortens typing. */
function applyPreset(index: number, value: string): void {
    const row = form.page_sections[index];
    row.section_key = value;

    const preset = props.sectionKeyOptions.find((option) => String(option.value) === value);

    if (preset && !row.title.trim()) {
        row.title = preset.label;
    }
}

/* ------------------------------ symptoms ------------------------------ */

const symptomSearch = ref('');

const filteredSymptoms = computed(() => {
    const needle = symptomSearch.value.trim().toLowerCase();

    if (!needle) {
        return props.symptomOptions;
    }

    return props.symptomOptions.filter((option) => option.label.toLowerCase().includes(needle));
});

const selectedSymptoms = computed(() =>
    form.symptoms
        .map((id) => props.symptomOptions.find((option) => Number(option.value) === Number(id)))
        .filter((option): option is SelectOption => Boolean(option)),
);

function toggleSymptom(value: string | number): void {
    const id = Number(value);
    const at = form.symptoms.indexOf(id);

    if (at === -1) {
        form.symptoms.push(id);
    } else {
        form.symptoms.splice(at, 1);
    }
}

function isSymptomSelected(value: string | number): boolean {
    return form.symptoms.includes(Number(value));
}

function clearSymptoms(): void {
    form.symptoms = [];
}

/* -------------------------------- submit ------------------------------ */

function submit(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success({ title: isEditing.value ? 'Department updated' : 'Department created' }),
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (isEditing.value && props.department?.id) {
        form.put(`/admin/doctors/departments/${props.department.id}`, options);

        return;
    }

    form.post('/admin/doctors/departments', options);
}

const breadcrumbs = [
    { title: 'Admin' },
    { title: 'Doctors' },
    { title: 'Departments' },
    { title: isEditing.value ? 'Edit' : 'New' },
];
</script>

<template>
    <AdminLayout>
        <Head :title="isEditing ? `Edit ${department?.title ?? ''}` : 'New department'" />

        <div class="mx-auto w-full max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <PageHeader
                :title="isEditing ? 'Edit department' : 'New department'"
                :description="
                    isEditing
                        ? 'Update the department, its page content and the symptoms it treats.'
                        : 'Departments own their page content — fill in the sections below to publish a complete page.'
                "
                :breadcrumbs="breadcrumbs"
            >
                <template #actions>
                    <Button variant="outline" href="/admin/doctors/departments">
                        <ArrowLeft class="size-4" />
                        Back to departments
                    </Button>

                    <Button :loading="form.processing" @click="submit">
                        {{ isEditing ? 'Save changes' : 'Create department' }}
                    </Button>
                </template>
            </PageHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <!-- 1. Department info -->
                <CollapsibleSection
                    title="Department info"
                    description="The identity of the department: name, category and how it appears in listings."
                    icon="1"
                    default-open
                    :invalid="Boolean(form.errors.title || form.errors.slug || form.errors.department_category_id || form.errors.short_description || form.errors.icon || form.errors.featured_image || form.errors.sort_order)"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <FormField label="Department name" required :error="form.errors.title" class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.title" :invalid="invalid" :aria-describedby="describedBy" placeholder="Cardiology" />
                            </template>
                        </FormField>

                        <FormField label="Category" :error="form.errors.department_category_id" hint="Groups related departments together.">
                            <template #default="{ id, describedBy, invalid }">
                                <Select :id="id" v-model="form.department_category_id" :options="categories" :invalid="invalid" :aria-describedby="describedBy" placeholder="Select a category" />
                            </template>
                        </FormField>

                        <FormField label="Slug" :error="form.errors.slug" hint="Leave empty to generate it from the name.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.slug" :invalid="invalid" :aria-describedby="describedBy" placeholder="cardiology" />
                            </template>
                        </FormField>

                        <FormField label="Short description" :error="form.errors.short_description" class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Textarea :id="id" v-model="form.short_description" :rows="3" :invalid="invalid" :aria-describedby="describedBy" placeholder="One or two sentences shown across the site." />
                            </template>
                        </FormField>

                        <FormField label="Icon" :error="form.errors.icon" hint="An icon class name or inline SVG.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.icon" :invalid="invalid" :aria-describedby="describedBy" placeholder="heroicons:heart" />
                            </template>
                        </FormField>

                        <FormField label="Featured image" :error="form.errors.featured_image" hint="URL of the image used in cards and banners.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.featured_image" :invalid="invalid" :aria-describedby="describedBy" placeholder="https://…" />
                            </template>
                        </FormField>

                        <FormField label="Sort order" :error="form.errors.sort_order" hint="Lower numbers appear first.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.sort_order" type="number" min="0" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>
                    </div>

                    <div class="mt-5 grid gap-3 border-t border-border pt-4 sm:grid-cols-3">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5">
                            <div>
                                <p class="text-sm font-medium text-foreground">Active</p>
                                <p class="text-xs text-muted-foreground">Visible on the public site.</p>
                            </div>
                            <Switch v-model="form.is_active" />
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5">
                            <div>
                                <p class="text-sm font-medium text-foreground">Featured</p>
                                <p class="text-xs text-muted-foreground">Highlighted on the homepage.</p>
                            </div>
                            <Switch v-model="form.is_featured" />
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5">
                            <div>
                                <p class="text-sm font-medium text-foreground">Popular search</p>
                                <p class="text-xs text-muted-foreground">Shown in the quick-search row.</p>
                            </div>
                            <Switch v-model="form.is_popular_search" />
                        </div>
                    </div>
                </CollapsibleSection>

                <!-- 2. Page sections -->
                <CollapsibleSection
                    title="Page sections"
                    description="The content blocks rendered on this department's public page."
                    icon="2"
                    :count="form.page_sections.length"
                    :invalid="hasSectionErrors() || Boolean(form.errors['page_sections'] as string | undefined)"
                >
                    <div v-if="form.page_sections.length === 0" class="mb-4">
                        <EmptyState
                            title="No page sections yet"
                            description="Add a hero, an intro block or a call to action to build out the department page."
                        >
                            <template #action>
                                <Button size="sm" @click="addPresetSection">
                                    <Plus class="size-4" />
                                    Add first section
                                </Button>
                            </template>
                        </EmptyState>
                    </div>

                    <div v-else class="space-y-3">
                        <Card
                            v-for="(section, index) in form.page_sections"
                            :key="index"
                            padded
                            class="relative border-border/80 bg-muted/20"
                        >
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <Layers class="size-4 text-muted-foreground" />
                                    <span class="text-sm font-medium text-foreground">
                                        {{ section.title || section.section_key || `Section ${index + 1}` }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <Switch v-model="section.is_active" size="sm" />
                                    <Button variant="ghost" size="icon-sm" @click="removeSection(index)">
                                        <Trash2 class="size-4 text-red-500" />
                                    </Button>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <FormField label="Section key" required :error="sectionError(index, 'section_key')" hint="Unique within this department.">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" :model-value="section.section_key" :invalid="invalid" :aria-describedby="describedBy" placeholder="hero" @update:model-value="(value) => (section.section_key = value)" />
                                    </template>
                                </FormField>

                                <FormField label="Preset" hint="Fills the key and title for a standard block.">
                                    <template #default="{ id }">
                                        <Select
                                            :id="id"
                                            :model-value="sectionKeyOptions.some((o) => String(o.value) === section.section_key) ? section.section_key : ''"
                                            :options="sectionKeyOptions"
                                            @update:model-value="(value) => applyPreset(index, value)"
                                        />
                                    </template>
                                </FormField>

                                <FormField label="Title" :error="sectionError(index, 'title')" class="sm:col-span-2">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.title" :invalid="invalid" :aria-describedby="describedBy" />
                                    </template>
                                </FormField>

                                <FormField label="Badge" :error="sectionError(index, 'badge')" class="sm:col-span-2">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.badge" :invalid="invalid" :aria-describedby="describedBy" placeholder="Trusted care" />
                                    </template>
                                </FormField>

                                <FormField label="Subtitle / body" :error="sectionError(index, 'subtitle')" class="sm:col-span-2">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Textarea :id="id" v-model="section.subtitle" :rows="3" :invalid="invalid" :aria-describedby="describedBy" />
                                    </template>
                                </FormField>

                                <FormField label="Primary button text" :error="sectionError(index, 'primary_button_text')">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.primary_button_text" :invalid="invalid" :aria-describedby="describedBy" placeholder="Book an appointment" />
                                    </template>
                                </FormField>

                                <FormField label="Primary button URL" :error="sectionError(index, 'primary_button_url')">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.primary_button_url" :invalid="invalid" :aria-describedby="describedBy" placeholder="/appointments" />
                                    </template>
                                </FormField>

                                <FormField label="Secondary button text" :error="sectionError(index, 'secondary_button_text')">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.secondary_button_text" :invalid="invalid" :aria-describedby="describedBy" placeholder="Find a doctor" />
                                    </template>
                                </FormField>

                                <FormField label="Secondary button URL" :error="sectionError(index, 'secondary_button_url')">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.secondary_button_url" :invalid="invalid" :aria-describedby="describedBy" placeholder="/doctors" />
                                    </template>
                                </FormField>

                                <FormField label="Image" :error="sectionError(index, 'image')" class="sm:col-span-2">
                                    <template #default="{ id, describedBy, invalid }">
                                        <Input :id="id" v-model="section.image" :invalid="invalid" :aria-describedby="describedBy" placeholder="https://…" />
                                    </template>
                                </FormField>
                            </div>
                        </Card>
                    </div>

                    <div v-if="form.page_sections.length > 0" class="mt-4 flex flex-wrap gap-2">
                        <Button variant="outline" size="sm" @click="addPresetSection">
                            <Sparkles class="size-4" />
                            Add preset section
                        </Button>
                        <Button variant="ghost" size="sm" @click="addSection">
                            <Plus class="size-4" />
                            Add blank section
                        </Button>
                    </div>
                </CollapsibleSection>

                <!-- 3. Symptoms -->
                <CollapsibleSection
                    title="Symptoms"
                    description="Conditions treated by this department. Order is saved as you select them."
                    icon="3"
                    :count="form.symptoms.length"
                    :invalid="Boolean(form.errors.symptoms)"
                >
                    <div class="relative mb-4">
                        <Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="symptomSearch" placeholder="Search symptoms…" class="pl-9" />
                    </div>

                    <p v-if="form.errors.symptoms" class="mb-3 text-xs text-red-600 dark:text-red-400">
                        {{ form.errors.symptoms }}
                    </p>

                    <div v-if="filteredSymptoms.length === 0" class="py-6 text-center text-sm text-muted-foreground">
                        No symptoms match that search.
                    </div>

                    <div v-else class="grid max-h-72 gap-2 overflow-y-auto pr-1 sm:grid-cols-2 lg:grid-cols-3">
                        <label
                            v-for="option in filteredSymptoms"
                            :key="option.value"
                            class="flex cursor-pointer items-center gap-2.5 rounded-lg border px-3 py-2 text-sm transition-colors"
                            :class="
                                isSymptomSelected(option.value)
                                    ? 'border-brand-500 bg-brand-50 text-brand-800 dark:bg-brand-950/40 dark:text-brand-200'
                                    : 'border-border hover:bg-accent/50'
                            "
                        >
                            <Checkbox :model-value="isSymptomSelected(option.value)" @update:model-value="() => toggleSymptom(option.value)" />
                            <span class="min-w-0 truncate">{{ option.label }}</span>
                        </label>
                    </div>

                    <div v-if="selectedSymptoms.length > 0" class="mt-4 border-t border-border pt-4">
                        <div class="mb-2 flex items-center justify-between">
                            <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                                Selected ({{ selectedSymptoms.length }}) — in saved order
                            </p>
                            <Button variant="ghost" size="xs" @click="clearSymptoms">Clear all</Button>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="option in selectedSymptoms"
                                :key="option.value"
                                class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 dark:bg-brand-950/40 dark:text-brand-300"
                            >
                                <Stethoscope class="size-3" />
                                {{ option.label }}
                            </span>
                        </div>
                    </div>
                </CollapsibleSection>

                <!-- 4. SEO -->
                <CollapsibleSection
                    title="SEO"
                    description="Search engine and social sharing metadata for this department's page."
                    icon="4"
                    :invalid="Object.keys(form.errors).some((key) => key.startsWith('seo.'))"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <FormField label="Meta title" :error="seoError('meta_title')" hint="Recommended: under 60 characters." class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.meta_title" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Meta description" :error="seoError('meta_description')" class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Textarea :id="id" v-model="form.seo.meta_description" :rows="2" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Meta keywords" :error="seoError('meta_keywords')" hint="Comma separated.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.meta_keywords" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Canonical URL" :error="seoError('canonical_url')">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.canonical_url" :invalid="invalid" :aria-describedby="describedBy" placeholder="https://…" />
                            </template>
                        </FormField>

                        <FormField label="Robots" :error="seoError('robots')">
                            <template #default="{ id, describedBy, invalid }">
                                <Select :id="id" v-model="form.seo.robots" :options="robotsOptions" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Open Graph type" :error="seoError('og_type')">
                            <template #default="{ id, describedBy, invalid }">
                                <Select :id="id" v-model="form.seo.og_type" :options="ogTypeOptions" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="OG title" :error="seoError('og_title')">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.og_title" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="OG image" :error="seoError('og_image')">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.og_image" :invalid="invalid" :aria-describedby="describedBy" placeholder="https://…" />
                            </template>
                        </FormField>

                        <FormField label="OG description" :error="seoError('og_description')" class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Textarea :id="id" v-model="form.seo.og_description" :rows="2" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Twitter card" :error="seoError('twitter_card')">
                            <template #default="{ id, describedBy, invalid }">
                                <Select :id="id" v-model="form.seo.twitter_card" :options="twitterCardOptions" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Twitter image" :error="seoError('twitter_image')">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.twitter_image" :invalid="invalid" :aria-describedby="describedBy" placeholder="https://…" />
                            </template>
                        </FormField>

                        <FormField label="Twitter title" :error="seoError('twitter_title')">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.twitter_title" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Twitter description" :error="seoError('twitter_description')">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.seo.twitter_description" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Structured data (JSON-LD)" :error="seoError('schema_json')" hint="Must be valid JSON." class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Textarea :id="id" v-model="form.seo.schema_json" :rows="4" :invalid="invalid" :aria-describedby="describedBy" class="font-mono text-xs" />
                            </template>
                        </FormField>
                    </div>
                </CollapsibleSection>

                <!-- 5. Metadata -->
                <CollapsibleSection
                    v-if="department"
                    title="Metadata"
                    description="Read-only record information."
                    icon="5"
                >
                    <dl class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Department ID</dt>
                            <dd class="mt-1 text-sm text-foreground">#{{ department.id }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted-foreground">URL slug</dt>
                            <dd class="mt-1 truncate text-sm text-foreground">{{ department.slug || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Status</dt>
                            <dd class="mt-1 text-sm text-foreground">
                                {{ form.is_active ? 'Active' : 'Hidden' }}
                            </dd>
                        </div>
                    </dl>
                </CollapsibleSection>

                <!-- Sticky action bar -->
                <div class="sticky bottom-0 -mx-4 border-t border-border bg-background/85 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
                    <div class="flex items-center justify-between gap-3">
                        <p v-if="form.isDirty" class="text-xs text-amber-600 dark:text-amber-400">
                            You have unsaved changes.
                        </p>
                        <p v-else class="text-xs text-muted-foreground">All changes saved.</p>

                        <div class="flex items-center gap-2">
                            <Button variant="ghost" href="/admin/doctors/departments">Cancel</Button>
                            <Button :loading="form.processing" @click="submit">
                                <ShieldCheck class="size-4" />
                                {{ isEditing ? 'Save changes' : 'Create department' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
