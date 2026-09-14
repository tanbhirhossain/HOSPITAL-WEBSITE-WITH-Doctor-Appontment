<?php

namespace Modules\CORE\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Modules\CORE\Interfaces\AuditTrailRepositoryInterface;
use Modules\CORE\Models\AuditTrail;

class AuditTrailRepository extends BaseRepository implements AuditTrailRepositoryInterface
{
    /** @var class-string<AuditTrail> */
    protected string $modelClass = AuditTrail::class;

    protected array $searchable = ['description', 'url', 'user_name', 'ip_address'];

    protected array $sortable = ['id', 'event', 'module', 'user_name', 'ip_address', 'created_at'];

    protected array $filterable = [
        'event' => 'event',
        'module' => 'module',
        'user_id' => 'user_id',
        'created_at' => 'created_at',
    ];

    protected array $dateFilters = ['created_at'];

    protected array $with = ['user'];

    public function eventOptions(): array
    {
        return AuditTrail::query()
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->all();
    }

    public function moduleOptions(): array
    {
        return AuditTrail::query()
            ->distinct()
            ->whereNotNull('module')
            ->orderBy('module')
            ->pluck('module')
            ->all();
    }

    public function prune(int $days): int
    {
        return AuditTrail::query()
            ->where('created_at', '<', now()->subDays(max(1, $days)))
            ->delete();
    }

    public function countByEvent(): array
    {
        return AuditTrail::query()
            ->selectRaw('event, count(*) as aggregate')
            ->groupBy('event')
            ->pluck('aggregate', 'event')
            ->all();
    }

    /**
     * Audit rows are never edited in place, so `update` is intentionally a
     * no-op guard rather than a silent mutation.
     */
    public function update(int|string|\Illuminate\Database\Eloquent\Model $model, array $data): \Illuminate\Database\Eloquent\Model
    {
        return $this->resolveModel($model);
    }

    protected function applyFilter(Builder $query, string $key, mixed $value): void
    {
        // `created_at` is handled by the shared date-range filter.
        unset($query, $key, $value);
    }

    public function latestFor(string $type, int $id, int $limit = 10): Builder
    {
        return AuditTrail::query()
            ->where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->latest();
    }

    protected function applyDateRange(Builder $query, string $column, array $range): void
    {
        $from = $range['from'] ?? null;
        $to = $range['to'] ?? null;

        if ($from) {
            $query->whereDate($column, '>=', $from instanceof CarbonInterface ? $from->toDateString() : $from);
        }

        if ($to) {
            $query->whereDate($column, '<=', $to instanceof CarbonInterface ? $to->toDateString() : $to);
        }
    }
}
