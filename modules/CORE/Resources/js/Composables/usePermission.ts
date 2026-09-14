import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

/**
 * Spatie permissions are shared with every Inertia response, so a single
 * composable is enough to drive `v-if="can('doctor.create')"` in templates.
 */

interface AclUser {
    id: number;
    name: string;
    email: string;
}

interface SharedProps {
    [key: string]: unknown;
    auth?: { user?: AclUser | null };
    acl?: {
        user?: AclUser | null;
        roles?: string[];
        permissions?: string[];
    };
}

export function usePermission() {
    const page = usePage<SharedProps>();

    /*
     * `acl` is shared by the CORE module; `auth` comes from the framework
     * middleware and only carries the user, so it is used as the fallback.
     */
    const acl = computed(() => page.props.acl);
    const permissions = computed<string[]>(() => acl.value?.permissions ?? []);
    const roles = computed<string[]>(() => acl.value?.roles ?? []);
    const user = computed<AclUser | null>(() => acl.value?.user ?? page.props.auth?.user ?? null);

    /** Wildcards are supported: `can('doctor.*')`. */
    function can(permission: string | undefined): boolean {
        if (!permission) {
            return true;
        }

        if (permissions.value.includes('*') || permissions.value.includes(permission)) {
            return true;
        }

        if (permission.endsWith('.*')) {
            const prefix = permission.slice(0, -1);

            return permissions.value.some((granted) => granted.startsWith(prefix));
        }

        return false;
    }

    function canAny(...list: Array<string | undefined>): boolean {
        return list.some((permission) => can(permission));
    }

    function hasRole(role: string): boolean {
        return roles.value.includes(role);
    }

    const isSuperAdmin = computed(() => roles.value.includes('super-admin'));

    return { can, canAny, hasRole, isSuperAdmin, permissions, roles, user };
}
