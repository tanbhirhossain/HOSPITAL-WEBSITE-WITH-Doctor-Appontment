<script setup lang="ts">
import Badge from '../../Components/ui/Badge.vue';
import Button from '../../Components/ui/Button.vue';
import Card from '../../Components/ui/Card.vue';
import JsonViewer from '../../Components/ui/JsonViewer.vue';
import PageHeader from '../../Components/ui/PageHeader.vue';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Clock, Globe, Layers, MousePointerClick, UserCheck } from 'lucide-vue-next';

interface AuditEntry {
    id: number;
    event: string;
    event_label: string;
    module: string | null;
    description: string | null;
    user_name: string | null;
    auditable_type: string | null;
    auditable_id: number | null;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    user_agent: string | null;
    url: string | null;
    method: string | null;
    created_at_human: string | null;
}

const props = defineProps<{ entry: AuditEntry }>();

const TONES: Record<string, 'success' | 'info' | 'danger' | 'warning' | 'neutral' | 'violet'> = {
    created: 'success',
    updated: 'info',
    deleted: 'danger',
    restored: 'warning',
    login: 'violet',
    logout: 'neutral',
    'failed-login': 'danger',
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Audit entry #${entry.id}`" />

        <PageHeader
            :title="entry.description ?? entry.event_label"
            :description="`Recorded ${entry.created_at_human ?? '—'}`"
            :breadcrumbs="[{ title: 'CORE' }, { title: 'Audit trail', href: '/admin/audit-trail' }, { title: `#${entry.id}` }]"
        >
            <template #actions>
                <Link href="/admin/audit-trail">
                    <Button variant="outline">
                        <ArrowLeft class="size-4" />
                        Back to log
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <!-- Context -->
            <Card>
                <template #title>
                    <span class="flex items-center gap-2">
                        <Layers class="size-4 text-muted-foreground" />
                        Context
                    </span>
                </template>

                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Event</dt>
                        <dd><Badge :tone="TONES[entry.event] ?? 'neutral'" dot>{{ entry.event_label }}</Badge></dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Module</dt>
                        <dd>
                            <Badge v-if="entry.module" tone="brand" outline>{{ entry.module }}</Badge>
                            <span v-else class="text-foreground">—</span>
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted-foreground">Subject</dt>
                        <dd class="truncate text-foreground">
                            {{ entry.auditable_type ? `${entry.auditable_type} #${entry.auditable_id}` : '—' }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="inline-flex items-center gap-1.5 text-muted-foreground">
                            <UserCheck class="size-3.5" /> User
                        </dt>
                        <dd class="text-foreground">{{ entry.user_name ?? 'System' }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="inline-flex items-center gap-1.5 text-muted-foreground">
                            <Globe class="size-3.5" /> IP address
                        </dt>
                        <dd class="font-mono text-xs text-foreground">{{ entry.ip_address ?? '—' }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="inline-flex items-center gap-1.5 text-muted-foreground">
                            <MousePointerClick class="size-3.5" /> Request
                        </dt>
                        <dd class="max-w-[60%] truncate font-mono text-xs text-foreground">
                            {{ entry.method }} {{ entry.url }}
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="inline-flex items-center gap-1.5 text-muted-foreground">
                            <Clock class="size-3.5" /> Timestamp
                        </dt>
                        <dd class="text-foreground">{{ entry.created_at_human }}</dd>
                    </div>
                </dl>

                <template #footer>
                    <p v-if="entry.user_agent" class="truncate text-xs text-muted-foreground" :title="entry.user_agent">
                        {{ entry.user_agent }}
                    </p>
                </template>
            </Card>

            <!-- Values -->
            <Card class="lg:col-span-2">
                <template #title>Recorded values</template>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">Before</p>
                        <JsonViewer :data="entry.old_values" empty-label="No previous state — this record was created." />
                    </div>

                    <div class="space-y-2">
                        <p class="text-xs font-semibold tracking-wide text-muted-foreground uppercase">After</p>
                        <JsonViewer :data="entry.new_values" empty-label="Nothing stored — this record was deleted." />
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
