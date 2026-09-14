<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, CalendarClock, Plus, ShieldCheck, Trash2 } from 'lucide-vue-next';

import AdminLayout from '../../../../../CORE/Resources/js/Layouts/AdminLayout.vue';
import PageHeader from '../../../../../CORE/Resources/js/Components/ui/PageHeader.vue';
import Button from '../../../../../CORE/Resources/js/Components/ui/Button.vue';
import Input from '../../../../../CORE/Resources/js/Components/ui/Input.vue';
import Textarea from '../../../../../CORE/Resources/js/Components/ui/Textarea.vue';
import Select from '../../../../../CORE/Resources/js/Components/ui/Select.vue';
import Switch from '../../../../../CORE/Resources/js/Components/ui/Switch.vue';
import FormField from '../../../../../CORE/Resources/js/Components/ui/FormField.vue';
import CollapsibleSection from '../../../../../CORE/Resources/js/Components/ui/CollapsibleSection.vue';
import EmptyState from '../../../../../CORE/Resources/js/Components/ui/EmptyState.vue';
import { toast } from '../../../../../CORE/Resources/js/Composables/useToast';
import type { SelectOption } from '../../../../../CORE/Resources/js/Types';

interface ScheduleRow {
    day_of_week: string;
    start_time: string;
    end_time: string;
    consultation_type: string;
    availability_status: string;
    max_patients: number;
    is_active: boolean;
}

interface ExpertiseRow {
    title: string;
    description: string;
    icon: string;
    sort_order: number;
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

interface DoctorPayload {
    id: number | null;
    department_id: number | null;
    name: string;
    slug: string;
    designation: string;
    specialty: string;
    qualification: string;
    experience: string;
    experience_years: number;
    hospital_name: string;
    location: string;
    profile_photo: string;
    bio: string;
    rating: number;
    reviews_count: number;
    social_links: Record<string, string>;
    is_featured: boolean;
    is_active: boolean;
    expertises: ExpertiseRow[];
    schedules: ScheduleRow[];
    seo: SeoPayload;
}

const props = defineProps<{
    doctor: DoctorPayload | null;
    departmentOptions: Record<number, string>;
    dayOptions: SelectOption[];
    consultationTypeOptions: SelectOption[];
    availabilityOptions: SelectOption[];
}>();

const isEditing = computed(() => props.doctor !== null);

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

const emptySchedule = (): ScheduleRow => ({
    day_of_week: props.dayOptions[0] ? String(props.dayOptions[0].value) : 'Sat',
    start_time: '09:00',
    end_time: '17:00',
    consultation_type: 'in_person',
    availability_status: 'available',
    max_patients: 20,
    is_active: true,
});

const emptyExpertise = (): ExpertiseRow => ({ title: '', description: '', icon: '', sort_order: 0 });

const form = useForm({
    department_id: props.doctor?.department_id ?? null,
    name: props.doctor?.name ?? '',
    slug: props.doctor?.slug ?? '',
    designation: props.doctor?.designation ?? '',
    specialty: props.doctor?.specialty ?? '',
    qualification: props.doctor?.qualification ?? '',
    experience: props.doctor?.experience ?? '',
    experience_years: props.doctor?.experience_years ?? 0,
    hospital_name: props.doctor?.hospital_name ?? '',
    location: props.doctor?.location ?? '',
    profile_photo: props.doctor?.profile_photo ?? '',
    bio: props.doctor?.bio ?? '',
    rating: props.doctor?.rating ?? 5,
    reviews_count: props.doctor?.reviews_count ?? 0,
    social_links: { ...(props.doctor?.social_links ?? {}) },
    is_featured: props.doctor?.is_featured ?? false,
    is_active: props.doctor?.is_active ?? true,
    expertises: (props.doctor?.expertises ?? []).map((row) => ({ ...emptyExpertise(), ...row })),
    schedules: (props.doctor?.schedules ?? []).map((row) => ({ ...emptySchedule(), ...row })),
    seo: { ...emptySeo(), ...(props.doctor?.seo ?? {}) },
});

/* ------------------------------ options ------------------------------- */

const departments: SelectOption[] = Object.entries(props.departmentOptions ?? {}).map(([id, name]) => ({
    value: Number(id),
    label: name,
}));

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

/* ---------------------------- timetable ------------------------------- */

const formErrors = computed(() => form.errors as Record<string, string | undefined>);

/** Inertia types `form.errors` to top-level keys only; nested errors need a cast. */
const seoError = (field: string): string | undefined => formErrors.value[`seo.${field}`];

const scheduleError = (index: number, field: string): string | undefined => formErrors.value[`schedules.${index}.${field}`];

const expertiseError = (index: number, field: string): string | undefined =>
    formErrors.value[`expertises.${index}.${field}`];

function hasErrorsWith(prefix: string): boolean {
    return Object.keys(form.errors).some((key) => key.startsWith(prefix));
}

function addSchedule(): void {
    const last = form.schedules[form.schedules.length - 1];

    form.schedules.push(last ? { ...last } : emptySchedule());
}

function removeSchedule(index: number): void {
    form.schedules.splice(index, 1);
}

function addExpertise(): void {
    form.expertises.push({ ...emptyExpertise(), sort_order: form.expertises.length });
}

function removeExpertise(index: number): void {
    form.expertises.splice(index, 1);
}

/** Overlapping slots for the same day are rejected server-side; flag them early. */
const overlapping = computed(() => {
    const seen = new Map<string, number[]>();

    form.schedules.forEach((row, index) => {
        if (!row.day_of_week || !row.start_time || !row.end_time) {
            return;
        }

        const key = `${row.day_of_week}`;
        const bucket = seen.get(key) ?? [];

        if (
            bucket.some((other) => {
                const a = form.schedules[other];

                return row.start_time < a.end_time && a.start_time < row.end_time;
            })
        ) {
            bucket.push(index);
            seen.set(key, bucket);

            return;
        }

        bucket.push(index);
        seen.set(key, bucket);
    });

    return seen;
});

function isOverlapping(index: number): boolean {
    return [...overlapping.value.values()].some((bucket) => bucket.includes(index));
}

/* -------------------------------- submit ------------------------------ */

function submit(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success({ title: isEditing.value ? 'Doctor updated' : 'Doctor created' }),
        onError: () => toast.error({ title: 'Please review the highlighted fields.' }),
    };

    if (isEditing.value && props.doctor?.id) {
        form.put(`/admin/doctors/${props.doctor.id}`, options);

        return;
    }

    form.post('/admin/doctors', options);
}

const breadcrumbs = [
    { title: 'Admin' },
    { title: 'Doctors' },
    { title: isEditing.value ? 'Edit' : 'New' },
];

const activeScheduleCount = computed(() => form.schedules.filter((row) => row.is_active).length);
</script>

<template>
    <AdminLayout>
        <Head :title="isEditing ? `Edit ${doctor?.name ?? ''}` : 'New doctor'" />

        <div class="mx-auto w-full max-w-5xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
            <PageHeader
                :title="isEditing ? 'Edit doctor' : 'New doctor'"
                :description="
                    isEditing
                        ? 'Update the profile, the weekly timetable and this doctor’s SEO metadata.'
                        : 'Create a doctor profile, add the weekly timetable and publish.'
                "
                :breadcrumbs="breadcrumbs"
            >
                <template #actions>
                    <Button variant="outline" href="/admin/doctors">
                        <ArrowLeft class="size-4" />
                        Back to doctors
                    </Button>

                    <Button :loading="form.processing" @click="submit">
                        {{ isEditing ? 'Save changes' : 'Create doctor' }}
                    </Button>
                </template>
            </PageHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <!-- 1. Doctor info -->
                <CollapsibleSection
                    title="Doctor info"
                    description="Profile details shown on the doctor's public page and in search results."
                    icon="1"
                    default-open
                    :invalid="hasErrorsWith('expertises.') || Boolean(form.errors.name || form.errors.slug || form.errors.department_id || form.errors.specialty || form.errors.designation || form.errors.qualification || form.errors.experience || form.errors.experience_years || form.errors.hospital_name || form.errors.location || form.errors.profile_photo || form.errors.bio || form.errors.rating || form.errors.reviews_count)"
                >
                    <div class="grid gap-5 sm:grid-cols-2">
                        <FormField label="Full name" required :error="form.errors.name" class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.name" :invalid="invalid" :aria-describedby="describedBy" placeholder="Dr. Jane Doe" />
                            </template>
                        </FormField>

                        <FormField label="Department" required :error="form.errors.department_id">
                            <template #default="{ id, describedBy, invalid }">
                                <Select :id="id" v-model="form.department_id" :options="departments" :invalid="invalid" :aria-describedby="describedBy" placeholder="Select a department" />
                            </template>
                        </FormField>

                        <FormField label="Specialty" required :error="form.errors.specialty">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.specialty" :invalid="invalid" :aria-describedby="describedBy" placeholder="Interventional cardiology" />
                            </template>
                        </FormField>

                        <FormField label="Designation" :error="form.errors.designation">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.designation" :invalid="invalid" :aria-describedby="describedBy" placeholder="Senior Consultant" />
                            </template>
                        </FormField>

                        <FormField label="Qualification" :error="form.errors.qualification">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.qualification" :invalid="invalid" :aria-describedby="describedBy" placeholder="MBBS, MD, FACC" />
                            </template>
                        </FormField>

                        <FormField label="Slug" :error="form.errors.slug" hint="Leave empty to generate it from the name.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.slug" :invalid="invalid" :aria-describedby="describedBy" placeholder="dr-jane-doe" />
                            </template>
                        </FormField>

                        <FormField label="Profile photo" :error="form.errors.profile_photo" hint="URL of the doctor's photograph.">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.profile_photo" :invalid="invalid" :aria-describedby="describedBy" placeholder="https://…" />
                            </template>
                        </FormField>

                        <FormField label="Experience (label)" :error="form.errors.experience">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.experience" :invalid="invalid" :aria-describedby="describedBy" placeholder="15 years" />
                            </template>
                        </FormField>

                        <FormField label="Experience (years)" :error="form.errors.experience_years">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.experience_years" type="number" min="0" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Hospital" :error="form.errors.hospital_name">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.hospital_name" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Location" :error="form.errors.location">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.location" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Rating" :error="form.errors.rating">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.rating" type="number" step="0.1" min="0" max="5" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Reviews count" :error="form.errors.reviews_count">
                            <template #default="{ id, describedBy, invalid }">
                                <Input :id="id" v-model="form.reviews_count" type="number" min="0" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>

                        <FormField label="Biography" :error="form.errors.bio" class="sm:col-span-2">
                            <template #default="{ id, describedBy, invalid }">
                                <Textarea :id="id" v-model="form.bio" :rows="4" :invalid="invalid" :aria-describedby="describedBy" />
                            </template>
                        </FormField>
                    </div>

                    <div class="mt-5 space-y-4 border-t border-border pt-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">Social links</p>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <FormField
                                v-for="network in ['facebook', 'linkedin', 'twitter', 'youtube']"
                                :key="network"
                                :label="network.charAt(0).toUpperCase() + network.slice(1)"
                            >
                                <template #default="{ id }">
                                    <Input :id="id" v-model="form.social_links[network]" :placeholder="`https://${network}.com/…`" />
                                </template>
                            </FormField>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-3 border-t border-border pt-4 sm:grid-cols-2">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2.5">
                            <div>
                                <p class="text-sm font-medium text-foreground">Active</p>
                                <p class="text-xs text-muted-foreground">Accepting appointments.</p>
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
                    </div>
                </CollapsibleSection>

                <!-- 1b. Expertises (part of the profile) -->
                <CollapsibleSection
                    title="Areas of expertise"
                    description="Short highlight cards shown under the doctor's biography."
                    icon="★"
                    :count="form.expertises.length"
                    :invalid="hasErrorsWith('expertises.')"
                >
                    <div v-if="form.expertises.length === 0" class="mb-4">
                        <EmptyState title="No expertise entries" description="Add the procedures or focus areas this doctor is known for." compact>
                            <template #action>
                                <Button size="sm" @click="addExpertise">
                                    <Plus class="size-4" />
                                    Add expertise
                                </Button>
                            </template>
                        </EmptyState>
                    </div>

                    <div v-else class="space-y-2">
                        <div
                            v-for="(expertise, index) in form.expertises"
                            :key="index"
                            class="grid gap-3 rounded-lg border border-border bg-muted/20 p-3 sm:grid-cols-[1fr_1fr_auto_auto]"
                        >
                            <FormField label="Title" :error="expertiseError(index, 'title')">
                                <template #default="{ id, describedBy, invalid }">
                                    <Input :id="id" v-model="expertise.title" :invalid="invalid" :aria-describedby="describedBy" size="sm" />
                                </template>
                            </FormField>

                            <FormField label="Description" :error="expertiseError(index, 'description')">
                                <template #default="{ id, describedBy, invalid }">
                                    <Input :id="id" v-model="expertise.description" :invalid="invalid" :aria-describedby="describedBy" size="sm" />
                                </template>
                            </FormField>

                            <FormField label="Icon" :error="expertiseError(index, 'icon')">
                                <template #default="{ id, describedBy, invalid }">
                                    <Input :id="id" v-model="expertise.icon" :invalid="invalid" :aria-describedby="describedBy" size="sm" class="w-32" />
                                </template>
                            </FormField>

                            <div class="flex items-end pb-1">
                                <Button variant="ghost" size="icon-sm" @click="removeExpertise(index)">
                                    <Trash2 class="size-4 text-red-500" />
                                </Button>
                            </div>
                        </div>
                    </div>

                    <div v-if="form.expertises.length > 0" class="mt-3">
                        <Button variant="outline" size="sm" @click="addExpertise">
                            <Plus class="size-4" />
                            Add expertise
                        </Button>
                    </div>
                </CollapsibleSection>

                <!-- 2. Timetable -->
                <CollapsibleSection
                    title="Timetable"
                    description="The doctor's weekly schedule. Add one row per time slot — rows can be removed at any time."
                    icon="2"
                    :count="activeScheduleCount"
                    :invalid="hasErrorsWith('schedules.')"
                >
                    <div v-if="form.schedules.length === 0" class="mb-4">
                        <EmptyState title="No timetable yet" description="Add the days and hours this doctor is available for appointments." compact>
                            <template #action>
                                <Button size="sm" @click="addSchedule">
                                    <CalendarClock class="size-4" />
                                    Add first slot
                                </Button>
                            </template>
                        </EmptyState>
                    </div>

                    <div v-else class="space-y-2">
                        <!-- header row -->
                        <div class="hidden grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.1fr)_minmax(0,1fr)_auto_auto] gap-3 px-1 pb-1 text-xs font-medium uppercase tracking-wide text-muted-foreground lg:grid">
                            <span>Day</span>
                            <span>Start</span>
                            <span>End</span>
                            <span>Consultation</span>
                            <span>Availability</span>
                            <span>Active</span>
                            <span />
                        </div>

                        <div
                            v-for="(schedule, index) in form.schedules"
                            :key="index"
                            class="grid gap-3 rounded-lg border p-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_minmax(0,1.1fr)_minmax(0,1fr)_auto_auto] lg:items-start"
                            :class="isOverlapping(index) ? 'border-amber-400 bg-amber-50/50 dark:bg-amber-950/20' : 'border-border bg-muted/20'"
                        >
                            <FormField label="Day" class="lg:hidden" :error="scheduleError(index, 'day_of_week')">
                                <template #default="{ id, describedBy, invalid }">
                                    <Select :id="id" v-model="schedule.day_of_week" :options="dayOptions" :invalid="invalid" :aria-describedby="describedBy" size="sm" />
                                </template>
                            </FormField>
                            <Select v-model="schedule.day_of_week" :options="dayOptions" size="sm" class="hidden lg:block" :aria-label="`Day for slot ${index + 1}`" />

                            <FormField label="Start" class="lg:hidden" :error="scheduleError(index, 'start_time')">
                                <template #default="{ id, describedBy, invalid }">
                                    <Input :id="id" v-model="schedule.start_time" type="time" :invalid="invalid" :aria-describedby="describedBy" size="sm" />
                                </template>
                            </FormField>
                            <Input v-model="schedule.start_time" type="time" size="sm" class="hidden lg:block" :aria-label="`Start time for slot ${index + 1}`" />

                            <FormField label="End" class="lg:hidden" :error="scheduleError(index, 'end_time')">
                                <template #default="{ id, describedBy, invalid }">
                                    <Input :id="id" v-model="schedule.end_time" type="time" :invalid="invalid" :aria-describedby="describedBy" size="sm" />
                                </template>
                            </FormField>
                            <Input v-model="schedule.end_time" type="time" size="sm" class="hidden lg:block" :aria-label="`End time for slot ${index + 1}`" />

                            <Select v-model="schedule.consultation_type" :options="consultationTypeOptions" size="sm" :aria-label="`Consultation type for slot ${index + 1}`" />

                            <Select v-model="schedule.availability_status" :options="availabilityOptions" size="sm" :aria-label="`Availability for slot ${index + 1}`" />

                            <div class="flex items-center gap-2 lg:pt-1.5">
                                <Switch v-model="schedule.is_active" size="sm" />
                            </div>

                            <div class="flex items-center gap-2 lg:pt-1">
                                <Button variant="ghost" size="icon-sm" @click="removeSchedule(index)">
                                    <Trash2 class="size-4 text-red-500" />
                                </Button>
                            </div>

                            <p
                                v-if="scheduleError(index, 'day_of_week') || scheduleError(index, 'start_time') || scheduleError(index, 'end_time') || scheduleError(index, 'consultation_type') || scheduleError(index, 'availability_status')"
                                class="text-xs text-red-600 lg:col-span-7 dark:text-red-400"
                            >
                                {{ scheduleError(index, 'day_of_week') ?? scheduleError(index, 'start_time') ?? scheduleError(index, 'end_time') ?? scheduleError(index, 'consultation_type') ?? scheduleError(index, 'availability_status') }}
                            </p>

                            <p v-else-if="isOverlapping(index)" class="text-xs text-amber-700 lg:col-span-7 dark:text-amber-400">
                                This slot overlaps another entry for the same day.
                            </p>
                        </div>
                    </div>

                    <div v-if="form.schedules.length > 0" class="mt-4 flex flex-wrap items-center gap-2">
                        <Button variant="outline" size="sm" @click="addSchedule">
                            <Plus class="size-4" />
                            Add slot
                        </Button>
                        <span class="text-xs text-muted-foreground">
                            {{ form.schedules.length }} row{{ form.schedules.length === 1 ? '' : 's' }} — {{ activeScheduleCount }} active
                        </span>
                    </div>
                </CollapsibleSection>

                <!-- 3. SEO metadata -->
                <CollapsibleSection
                    title="SEO metadata"
                    description="Search engine and social sharing metadata for the doctor's public profile."
                    icon="3"
                    :invalid="hasErrorsWith('seo.')"
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

                <!-- Sticky action bar -->
                <div class="sticky bottom-0 -mx-4 border-t border-border bg-background/85 px-4 py-3 backdrop-blur sm:-mx-6 sm:px-6">
                    <div class="flex items-center justify-between gap-3">
                        <p v-if="form.isDirty" class="text-xs text-amber-600 dark:text-amber-400">You have unsaved changes.</p>
                        <p v-else class="text-xs text-muted-foreground">All changes saved.</p>

                        <div class="flex items-center gap-2">
                            <Button variant="ghost" href="/admin/doctors">Cancel</Button>
                            <Button :loading="form.processing" @click="submit">
                                <ShieldCheck class="size-4" />
                                {{ isEditing ? 'Save changes' : 'Create doctor' }}
                            </Button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
