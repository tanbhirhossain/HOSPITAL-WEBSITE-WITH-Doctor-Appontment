import {
    Building2,
    HeartPulse,
    History,
    KeyRound,
    LayoutDashboard,
    Search,
    ShieldCheck,
    Stethoscope,
    Tags,
    Users,
} from 'lucide-vue-next';
import type { NavGroup } from '../Types';

/**
 * Sidebar definition for the whole panel.
 *
 * Items are filtered at render time against the permissions shared by
 * `HandleInertiaRequests`, so a user only ever sees what they can open.
 */
export const adminNav: NavGroup[] = [
    {
        label: 'Overview',
        items: [{ title: 'Dashboard', href: '/admin', icon: LayoutDashboard, permission: 'dashboard.view' }],
    },
    {
        label: 'Doctor Management',
        items: [
            { title: 'Doctors', href: '/admin/doctors', icon: Stethoscope, permission: 'doctor.view' },
            { title: 'Departments', href: '/admin/doctors/departments', icon: Building2, permission: 'department.view' },
            { title: 'Categories', href: '/admin/doctors/categories', icon: Tags, permission: 'department.view' },
            { title: 'Symptoms', href: '/admin/doctors/symptoms', icon: HeartPulse, permission: 'symptom.view' },
        ],
    },
    {
        label: 'System',
        items: [
            { title: 'Roles', href: '/admin/access-control/roles', icon: ShieldCheck, permission: 'role.view' },
            { title: 'Permissions', href: '/admin/access-control/permissions', icon: KeyRound, permission: 'permission.view' },
            { title: 'Users', href: '/admin/access-control/users', icon: Users, permission: 'user.view' },
            { title: 'SEO Meta Data', href: '/admin/seo', icon: Search, permission: 'seo.view' },
            { title: 'Audit Trail', href: '/admin/audit-trail', icon: History, permission: 'audit.view' },
        ],
    },
];
