import { router } from '@inertiajs/vue3';
import type { RequestPayload } from '@inertiajs/core';
import { computed, ref, watch, type Ref } from 'vue';
import type { DateRangeValue, SortDirection } from '../Types';

/**
 * Server-driven datatable state.
 *
 * Every interaction (search, sort, filter, page size) issues an Inertia GET to
 * the current URL with the query string the backend already understands.
 * `preserveState` keeps the Vue component alive so the input never loses focus
 * while the user types.
 */

export type FilterValue = string | number | boolean | null | Array<string | number> | DateRangeValue | undefined;

export interface UseDataTableOptions {
    /** Current page URL (query string excluded). */
    url: string;
    /** Props to partially reload. Omit to reload the whole page. */
    only?: string[];
    search?: string;
    filters?: Record<string, FilterValue>;
    sort?: string;
    direction?: SortDirection;
    perPage?: number;
    /** Debounce applied to the search box, in ms. */
    debounce?: number;
}

const PER_PAGE_OPTIONS = [10, 15, 25, 50, 100];

/** Strip empty values so the query string stays clean. */
function serialise(filters: Record<string, FilterValue>): Record<string, unknown> {
    const payload: Record<string, unknown> = {};

    for (const [key, value] of Object.entries(filters)) {
        if (value === null || value === undefined || value === '') {
            continue;
        }

        if (Array.isArray(value)) {
            if (value.length === 0) {
                continue;
            }
            payload[`filters[${key}]`] = value;
            continue;
        }

        if (typeof value === 'object') {
            const range = value as DateRangeValue;
            if (range.from) {
                payload[`filters[${key}][from]`] = range.from;
            }
            if (range.to) {
                payload[`filters[${key}][to]`] = range.to;
            }
            continue;
        }

        payload[`filters[${key}]`] = value;
    }

    return payload;
}

export function useDataTable(options: UseDataTableOptions) {
    const search: Ref<string> = ref(options.search ?? '');
    const filters = ref<Record<string, FilterValue>>({ ...(options.filters ?? {}) });
    const sort = ref<string>(options.sort ?? '');
    const direction = ref<SortDirection>(options.direction ?? 'desc');
    const perPage = ref<number>(options.perPage ?? 15);
    const processing = ref(false);
    const lastReloadedAt = ref<number>(Date.now());

    const activeFilterCount = computed(
        () =>
            Object.values(filters.value).filter((value) => {
                if (value === null || value === undefined || value === '') {
                    return false;
                }
                if (Array.isArray(value)) {
                    return value.length > 0;
                }
                if (typeof value === 'object') {
                    return Boolean((value as DateRangeValue).from || (value as DateRangeValue).to);
                }
                return true;
            }).length + (search.value ? 1 : 0),
    );

    const visitOptions = () => ({
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: options.only,
        onStart: () => {
            processing.value = true;
        },
        onFinish: () => {
            processing.value = false;
            lastReloadedAt.value = Date.now();
        },
    });

    function buildParams(page = 1): RequestPayload {
        const params: Record<string, unknown> = {
            page: page > 1 ? page : undefined,
            search: search.value.trim() || undefined,
            sort: sort.value || undefined,
            direction: sort.value ? direction.value : undefined,
            per_page: perPage.value,
        };

        return { ...params, ...serialise(filters.value) } as RequestPayload;
    }

    function apply(): void {
        router.get(options.url, buildParams(), visitOptions());
    }

    function setSort(key: string): void {
        if (sort.value === key) {
            direction.value = direction.value === 'asc' ? 'desc' : 'asc';
        } else {
            sort.value = key;
            direction.value = 'asc';
        }

        apply();
    }

    function setFilter(key: string, value: FilterValue): void {
        if (value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
            delete filters.value[key];
        } else {
            filters.value[key] = value;
        }

        apply();
    }

    function toggleFilterValue(key: string, option: string | number): void {
        const current = filters.value[key];
        const list: Array<string | number> = Array.isArray(current) ? [...current] : [];

        const index = list.indexOf(option);
        if (index === -1) {
            list.push(option);
        } else {
            list.splice(index, 1);
        }

        setFilter(key, list);
    }

    function setPerPage(value: number): void {
        perPage.value = value;
        apply();
    }

    function reset(): void {
        search.value = '';
        filters.value = {};
        sort.value = options.sort ?? '';
        direction.value = options.direction ?? 'desc';
        perPage.value = options.perPage ?? 15;
        apply();
    }

    /** Follow a pagination link produced by Laravel's paginator. */
    function visit(url: string | null): void {
        if (!url) {
            return;
        }

        router.get(url, {}, visitOptions());
    }

    let timer: ReturnType<typeof setTimeout> | undefined;

    watch(search, () => {
        clearTimeout(timer);
        timer = setTimeout(apply, options.debounce ?? 350);
    });

    return {
        search,
        filters,
        sort,
        direction,
        perPage,
        processing,
        activeFilterCount,
        lastReloadedAt,
        perPageOptions: PER_PAGE_OPTIONS,
        setSort,
        setFilter,
        toggleFilterValue,
        setPerPage,
        reset,
        apply,
        visit,
    };
}
