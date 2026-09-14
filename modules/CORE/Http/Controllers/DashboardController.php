<?php

namespace Modules\CORE\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CORE\Interfaces\AuditTrailRepositoryInterface;
use Modules\DOCTOR\Interfaces\DepartmentRepositoryInterface;
use Modules\DOCTOR\Interfaces\DoctorRepositoryInterface;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Admin landing page. Only reads — every number is produced by the module
     * that owns the data.
     */
    public function __invoke(
        DoctorRepositoryInterface $doctors,
        DepartmentRepositoryInterface $departments,
        AuditTrailRepositoryInterface $audit,
    ): Response {
        return Inertia::render('CORE::Dashboard/Index', [
            'stats' => [
                'doctors' => $doctors->count(),
                'departments' => $departments->count(),
                'users' => User::query()->count(),
                'roles' => Role::query()->count(),
            ],
            'recentActivity' => $this->recentActivity($audit),
            'activityByEvent' => $audit->countByEvent(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function recentActivity(AuditTrailRepositoryInterface $audit): array
    {
        return $audit
            ->query()
            ->with('user')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($entry): array => [
                'id' => $entry->id,
                'event' => $entry->event,
                'event_label' => $entry->event_label,
                'description' => $entry->description,
                'module' => $entry->module,
                'user_name' => $entry->user_name,
                'created_at' => $entry->created_at?->toISOString(),
                'created_at_human' => $entry->created_at?->diffForHumans(),
            ])
            ->all();
    }
}
