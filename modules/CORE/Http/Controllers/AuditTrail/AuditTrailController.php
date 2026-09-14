<?php

namespace Modules\CORE\Http\Controllers\AuditTrail;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CORE\Models\AuditTrail;
use Modules\CORE\Requests\AuditTrail\IndexAuditTrailRequest;
use Modules\CORE\Services\AuditTrail\AuditTrailService;

class AuditTrailController extends Controller
{
    public function __construct(
        private readonly AuditTrailService $service,
    ) {}

    public function index(IndexAuditTrailRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'created_at', 'desc'));

        return Inertia::render('CORE::AuditTrail/Index', [
            'entries' => $this->service->toDataTable($paginator, fn (AuditTrail $entry): array => [
                'id' => $entry->id,
                'event' => $entry->event,
                'event_label' => $entry->event_label,
                'module' => $entry->module,
                'description' => $entry->description,
                'user_name' => $entry->user_name,
                'user_id' => $entry->user_id,
                'auditable_type' => $entry->auditable_type ? class_basename($entry->auditable_type) : null,
                'auditable_id' => $entry->auditable_id,
                'old_values' => $entry->old_values,
                'new_values' => $entry->new_values,
                'ip_address' => $entry->ip_address,
                'url' => $entry->url,
                'method' => $entry->method,
                'created_at' => $entry->created_at?->toISOString(),
                'created_at_human' => $entry->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'filterOptions' => $this->service->filterOptions(),
            'statistics' => $this->service->statistics(),
        ]);
    }

    public function show(AuditTrail $auditTrail): Response
    {
        return Inertia::render('CORE::AuditTrail/Show', [
            'entry' => [
                'id' => $auditTrail->id,
                'event' => $auditTrail->event,
                'event_label' => $auditTrail->event_label,
                'module' => $auditTrail->module,
                'description' => $auditTrail->description,
                'user_name' => $auditTrail->user_name,
                'auditable_type' => $auditTrail->auditable_type,
                'auditable_id' => $auditTrail->auditable_id,
                'old_values' => $auditTrail->old_values,
                'new_values' => $auditTrail->new_values,
                'ip_address' => $auditTrail->ip_address,
                'user_agent' => $auditTrail->user_agent,
                'url' => $auditTrail->url,
                'method' => $auditTrail->method,
                'created_at' => $auditTrail->created_at?->toISOString(),
                'created_at_human' => $auditTrail->created_at?->format('d M Y, h:i A'),
            ],
        ]);
    }

    public function destroy(AuditTrail $auditTrail): RedirectResponse
    {
        $this->service->delete($auditTrail);

        return back()->with('success', __('Log entry removed.'));
    }

    /**
     * Housekeeping: drop entries past the configured retention window.
     */
    public function prune(): RedirectResponse
    {
        $deleted = $this->service->prune();

        return back()->with('success', __(':count old log entries were pruned.', ['count' => $deleted]));
    }
}
