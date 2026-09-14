<script setup lang="ts" generic="T extends object">
import DataTableColumnHeader from './DataTableColumnHeader.vue';
import DataTableFilterMenu from './DataTableFilterMenu.vue';
import DataTablePagination from './DataTablePagination.vue';
import Button from '../ui/Button.vue';
import Checkbox from '../ui/Checkbox.vue';
import EmptyState from '../ui/EmptyState.vue';
import SearchInput from '../ui/SearchInput.vue';
import { useDataTable, type FilterValue } from '../../Composables/useDataTable';
import { cn } from '../../Utils/cn';
import { Loader2, RotateCcw, SearchX } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import type { DataTableColumn, DataTableFilter, Paginated } from '../../Types';

/**
 * The single table used by every listing in the panel.
 *
 * Server-side everything: searching, sorting, filtering and paging are query
 * parameters handled by `BaseRepository::paginate()`. The component owns no
 * data-fetching logic beyond translating user intent into those parameters.
 */
const props = withDefaults(
    defineProps<{
        columns: DataTableColumn[];
        rows: T[];
        meta: Paginated<T>;
        /** Current page URL — Inertia reloads this with new query params. */
        url: string;
        /** Props to partially reload; omit to refresh the whole response. */
        only?: string[];
        filterDefs?: DataTableFilter[];
        initialSearch?: string;
        initialFilters?: Record<string, unknown>;
        initialSort?: string;
        initialDirection?: 'asc' | 'desc';
        initialPerPage?: number;
        rowKey?: string;
        selectable?: boolean;
        searchable?: boolean;
        searchPlaceholder?: string;
        emptyTitle?: string;
        emptyDescription?: string;
    }>(),
    {
        only: undefined,
        filterDefs: () => [],
        initialSearch: '',
        initialFilters: () => ({}),
        initialSort: '',
        initialDirection: 'desc',
        initialPerPage: 15,
        rowKey: 'id',
        selectable: false,
        searchable: true,
        searchPlaceholder: 'Search…',
        emptyTitle: 'No records found',
        emptyDescription: 'Adjust your search or filters to see more results.',
    },
);

/** Read a column value off a row without requiring an index signature. */
const field = (row: T, key: string): unknown => (row as unknown as Record<string, unknown>)[key];

const keyOf = (row: T): string | number => field(row, props.rowKey) as string | number;

const table = useDataTable({
    url: props.url,
    only: props.only,
    search: props.initialSearch,
    filters: props.initialFilters as Record<string, FilterValue>,
    sort: props.initialSort,
    direction: props.initialDirection,
    perPage: props.initialPerPage,
});

const { search, filters, sort, direction, perPage, processing, activeFilterCount, perPageOptions } = table;

/* ------------------------------ selection ------------------------------- */

const selected = ref<Set<string | number>>(new Set());

watch(
    () => props.rows,
    (rows) => {
        const keys = new Set(rows.map(keyOf));
        selected.value = new Set([...selected.value].filter((key) => keys.has(key)));
    },
    { deep: false },
);

const pageKeys = computed(() => props.rows.map(keyOf));

const allSelected = computed(
    () => pageKeys.value.length > 0 && pageKeys.value.every((key) => selected.value.has(key)),
);

const someSelected = computed(
    () => !allSelected.value && pageKeys.value.some((key) => selected.value.has(key)),
);

const selectedRows = computed(() => props.rows.filter((row) => selected.value.has(keyOf(row))));

function toggleAll(): void {
    selected.value = allSelected.value ? new Set() : new Set(pageKeys.value);
}

function toggleRow(key: string | number): void {
    const next = new Set(selected.value);
    next.has(key) ? next.delete(key) : next.add(key);
    selected.value = next;
}

function clearSelection(): void {
    selected.value = new Set();
}

/**
 * Exposed so pages can add bespoke controls (date ranges, custom facets) to
 * the toolbar while still driving the same server query.
 */
defineExpose({
    clearSelection,
    selectedRows,
    setFilter: table.setFilter,
    setSort: table.setSort,
    reset: table.reset,
    filters,
    search,
});

/* ------------------------------- helpers -------------------------------- */

const RESPONSIVE = {
    sm: 'hidden sm:table-cell',
    md: 'hidden md:table-cell',
    lg: 'hidden lg:table-cell',
    xl: 'hidden xl:table-cell',
} as const;

const alignClass = (align?: string) =>
    align === 'right' ? 'text-right' : align === 'center' ? 'text-center' : 'text-left';

function selectedFor(key: string): Array<string | number> {
    const value = filters.value[key];

    return Array.isArray(value) ? value : [];
}

function isFilterActive(key: string, option: string | number): boolean {
    return selectedFor(key).includes(option);
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
        <!-- Toolbar -->
        <div class="flex flex-col gap-3 border-b border-border p-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-2">
                <SearchInput
                    v-if="searchable"
                    v-model="search"
                    :placeholder="searchPlaceholder"
                    class="w-full sm:w-72"
                />

                <DataTableFilterMenu
                    v-for="filter in filterDefs"
                    :key="filter.key"
                    :label="filter.label"
                    :options="filter.options"
                    :selected="selectedFor(filter.key)"
                    @toggle="table.toggleFilterValue(filter.key, $event)"
                    @clear="table.setFilter(filter.key, null)"
                />

                <Button v-if="activeFilterCount > 0" variant="ghost" size="sm" @click="table.reset()">
                    <RotateCcw class="size-3.5" />
                    Reset
                </Button>
            </div>

            <div class="flex items-center gap-2">
                <slot name="toolbar" :processing="processing" />

                <span v-if="processing" class="inline-flex items-center gap-1.5 text-xs text-muted-foreground">
                    <Loader2 class="size-3.5 animate-spin" />
                    Updating…
                </span>
            </div>
        </div>

        <!-- Bulk actions -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 -translate-y-1"
            leave-active-class="transition duration-100 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="selectable && selected.size > 0"
                class="flex flex-wrap items-center justify-between gap-3 border-b border-brand-200 bg-brand-50 px-4 py-2.5 dark:border-brand-900 dark:bg-brand-950/40"
            >
                <p class="text-sm text-brand-900 dark:text-brand-200">
                    <span class="font-semibold">{{ selected.size }}</span>
                    {{ selected.size === 1 ? 'row' : 'rows' }} selected
                </p>

                <div class="flex items-center gap-2">
                    <slot name="bulk-actions" :selected="selectedRows" :clear="clearSelection" />
                    <Button variant="ghost" size="sm" @click="clearSelection">Clear</Button>
                </div>
            </div>
        </Transition>

        <!-- Table -->
        <div class="relative">
            <div class="overflow-x-auto scrollbar-thin">
                <table class="w-full min-w-[42rem] border-collapse text-sm">
                    <thead class="bg-muted/50">
                        <tr>
                            <th v-if="selectable" scope="col" class="w-10 px-4 py-2.5">
                                <Checkbox
                                    :model-value="allSelected"
                                    :indeterminate="someSelected"
                                    @update:model-value="toggleAll"
                                />
                                <span class="sr-only">Select all rows</span>
                            </th>

                            <th
                                v-for="column in columns"
                                :key="column.key"
                                scope="col"
                                :style="column.width ? { width: column.width } : undefined"
                                :class="[
                                    'px-4 py-2.5',
                                    alignClass(column.align),
                                    column.headerClass,
                                    column.hideBelow ? RESPONSIVE[column.hideBelow] : '',
                                ]"
                            >
                                <DataTableColumnHeader
                                    :label="column.label"
                                    :column-key="column.key"
                                    :sortable="column.sortable"
                                    :align="column.align"
                                    :active-sort="sort"
                                    :direction="direction"
                                    @sort="table.setSort"
                                />
                            </th>

                            <th v-if="$slots['row-actions']" scope="col" class="w-14 px-4 py-2.5 text-right">
                                <span class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    Actions
                                </span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="row in rows"
                            :key="String(keyOf(row))"
                            class="group transition-colors hover:bg-muted/40"
                            :class="selected.has(keyOf(row)) && 'bg-brand-50/60 dark:bg-brand-950/20'"
                        >
                            <td v-if="selectable" class="px-4 py-3">
                                <Checkbox
                                    :model-value="selected.has(keyOf(row))"
                                    @update:model-value="toggleRow(keyOf(row))"
                                />
                            </td>

                            <td
                                v-for="column in columns"
                                :key="column.key"
                                :class="[
                                    'px-4 py-3 align-middle',
                                    alignClass(column.align),
                                    column.class,
                                    column.hideBelow ? RESPONSIVE[column.hideBelow] : '',
                                ]"
                            >
                                <slot :name="`cell-${column.key}`" :row="row" :value="field(row, column.key)">
                                    <span class="text-foreground">
                                        {{ field(row, column.key) ?? '—' }}
                                    </span>
                                </slot>
                            </td>

                            <td v-if="$slots['row-actions']" class="px-4 py-3 text-right align-middle">
                                <slot name="row-actions" :row="row" />
                            </td>
                        </tr>

                        <tr v-if="rows.length === 0">
                            <td :colspan="columns.length + (selectable ? 1 : 0) + ($slots['row-actions'] ? 1 : 0)">
                                <slot name="empty">
                                    <EmptyState
                                        :icon="activeFilterCount > 0 ? SearchX : undefined"
                                        :title="emptyTitle"
                                        :description="emptyDescription"
                                        compact
                                    >
                                        <Button v-if="activeFilterCount > 0" variant="outline" size="sm" @click="table.reset()">
                                            <RotateCcw class="size-3.5" />
                                            Clear filters
                                        </Button>
                                    </EmptyState>
                                </slot>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Loading veil -->
            <Transition
                enter-active-class="transition duration-150"
                enter-from-class="opacity-0"
                leave-active-class="transition duration-300"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="processing"
                    class="pointer-events-none absolute inset-0 flex items-start justify-center bg-card/50 pt-10 backdrop-blur-[1px]"
                >
                    <span class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1.5 text-xs font-medium text-foreground shadow-sm">
                        <Loader2 class="size-3.5 animate-spin text-brand-600" />
                        Loading
                    </span>
                </div>
            </Transition>
        </div>

        <!-- Pagination -->
        <DataTablePagination
            :meta="meta"
            :per-page="perPage"
            :per-page-options="perPageOptions"
            @visit="table.visit"
            @update:per-page="table.setPerPage"
        />
    </div>
</template>
