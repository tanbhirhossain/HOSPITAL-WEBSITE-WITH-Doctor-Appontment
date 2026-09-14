import type { Component } from 'vue';

/**
 * Shared types for the administration panel.
 *
 * These mirror the payloads produced by `BaseService::toDataTable()` and the
 * datatable components, keeping the PHP and Vue halves in step.
 */

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T = Record<string, unknown>> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export type SortDirection = 'asc' | 'desc';

export type ColumnAlign = 'left' | 'center' | 'right';

export interface DataTableColumn {
    /** Field name on the row object. Also used as the sort key. */
    key: string;
    label: string;
    sortable?: boolean;
    align?: ColumnAlign;
    width?: string;
    /** Extra classes for body cells. */
    class?: string;
    /** Extra classes for the header cell. */
    headerClass?: string;
    /** Hide below the given Tailwind breakpoint, e.g. `md`. */
    hideBelow?: 'sm' | 'md' | 'lg' | 'xl';
}

export interface FilterOption {
    value: string | number;
    label: string;
    count?: number;
}

export interface DataTableFilter {
    /** Must match a key accepted by the repository's filter map. */
    key: string;
    label: string;
    options: FilterOption[];
    /** Allow several values at once (default true). */
    multiple?: boolean;
    /** Render as a compact pill row instead of a checkbox list. */
    display?: 'list' | 'pills';
}

export interface DateRangeValue {
    from?: string;
    to?: string;
}

export type ToastVariant = 'success' | 'error' | 'warning' | 'info' | 'default';

export interface Toast {
    id: number;
    title: string;
    description?: string;
    variant: ToastVariant;
    duration: number;
}

export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: Component;
    permission?: string;
    badge?: string | number;
    children?: NavItem[];
}

export interface NavGroup {
    label: string;
    items: NavItem[];
}

export interface Breadcrumb {
    title: string;
    href?: string;
}

/** Row shape used by every CRUD screen in the panel. */
export interface Identifiable {
    id: number | string;
}
