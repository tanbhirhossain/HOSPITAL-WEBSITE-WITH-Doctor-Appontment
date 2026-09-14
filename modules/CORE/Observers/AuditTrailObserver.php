<?php

namespace Modules\CORE\Observers;

use Illuminate\Database\Eloquent\Model;
use Modules\CORE\Services\AuditTrail\AuditRecorder;

/**
 * Generic observer attached to every model listed in `config/audit.php`.
 *
 * One observer instead of one per module keeps the recording rules in a
 * single place and makes the audited surface configurable.
 */
class AuditTrailObserver
{
    public function __construct(
        private readonly AuditRecorder $recorder,
    ) {}

    public function created(Model $model): void
    {
        $this->recorder->recordModelEvent('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->recorder->recordModelEvent('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->recorder->recordModelEvent('deleted', $model);
    }

    public function restored(Model $model): void
    {
        $this->recorder->recordModelEvent('restored', $model);
    }
}
