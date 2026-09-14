<?php

namespace Modules\CORE\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;

interface AuditTrailRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * Distinct event names actually present in the log, for the filter bar.
     *
     * @return array<int, string>
     */
    public function eventOptions(): array;

    /**
     * Distinct module names present in the log.
     *
     * @return array<int, string>
     */
    public function moduleOptions(): array;

    /**
     * Remove log entries older than the given number of days.
     */
    public function prune(int $days): int;

    /**
     * @return array<string, int>
     */
    public function countByEvent(): array;
}
