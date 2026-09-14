<?php

namespace Modules\CORE\Services\AuditTrail;

use App\Models\User;
use Modules\CORE\Support\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use Modules\CORE\Interfaces\AuditTrailRepositoryInterface;

class AuditTrailService extends BaseService
{
    public function __construct(AuditTrailRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Facet options for the filter bar.
     *
     * @return array{event: array<int, string>, module: array<int, string>, user: array<int, string>}
     */
    public function filterOptions(): array
    {
        return [
            'event' => $this->repository->eventOptions(),
            'module' => $this->repository->moduleOptions(),
            'user' => User::query()->orderBy('name')->pluck('name', 'id')->all(),
        ];
    }

    /**
     * Headline numbers for the audit dashboard cards.
     *
     * @return array<string, mixed>
     */
    public function statistics(): array
    {
        return Cache::remember('core.audit.statistics', now()->addMinutes(2), function (): array {
            $byEvent = $this->repository->countByEvent();

            return [
                'total' => array_sum($byEvent),
                'by_event' => $byEvent,
                'today' => $this->repository->query()->whereDate('created_at', today())->count(),
                'last_7_days' => $this->repository->query()->where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });
    }

    public function prune(?int $days = null): int
    {
        $days = $days ?? (int) config('audit.retention_days', 180);

        $deleted = $this->repository->prune($days);

        Cache::forget('core.audit.statistics');

        return $deleted;
    }
}
