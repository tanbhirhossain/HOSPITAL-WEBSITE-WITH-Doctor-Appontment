<?php

namespace Modules\CORE\Services\AuditTrail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Modules\CORE\Interfaces\AuditTrailRepositoryInterface;
use Modules\CORE\Models\AuditTrail;

/**
 * Single writer for the audit log.
 *
 * Isolated from Eloquent observers so the log can also be written for
 * non-model events (sign in, sign out, failed sign in) without duplicating
 * the payload-shaping rules.
 */
class AuditRecorder
{
    public function __construct(
        private readonly AuditTrailRepositoryInterface $repository,
    ) {}

    /**
     * Record a model lifecycle event (created / updated / deleted).
     */
    public function recordModelEvent(string $event, Model $model): ?AuditTrail
    {
        if (config('audit.enabled') !== true) {
            return null;
        }

        $changed = $event === 'updated'
            ? Arr::except($model->getDirty(), $this->ignored())
            : Arr::except($model->getAttributes(), $this->ignored());

        return $this->record(
            event: $event,
            model: $model,
            description: $this->describe($event, $model),
            oldValues: $event === 'updated' ? Arr::only($model->getOriginal(), array_keys($changed)) : null,
            newValues: $changed === [] ? null : $changed,
        );
    }

    /**
     * Record an authentication event, which has no subject model.
     */
    public function recordAuthEvent(string $event, string $description, ?array $newValues = null): ?AuditTrail
    {
        return $this->record($event, null, $description, null, $newValues, 'CORE');
    }

    public function record(
        string $event,
        ?Model $model = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $module = null,
    ): ?AuditTrail {
        if (config('audit.enabled') !== true) {
            return null;
        }

        $user = Auth::user();

        /** @var AuditTrail $entry */
        $entry = AuditTrail::query()->create([
            'user_id' => $user?->getAuthIdentifier(),
            'user_name' => $user->name ?? 'System',
            'event' => $event,
            'module' => $module ?? ($model ? $this->moduleFor($model) : null),
            'description' => $description,
            'auditable_type' => $model?->getMorphClass(),
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues === [] ? null : $oldValues,
            'new_values' => $newValues === [] ? null : $newValues,
            'ip_address' => RequestFacade::ip(),
            'user_agent' => str(RequestFacade::userAgent() ?? '')->limit(500)->toString(),
            'url' => str(RequestFacade::fullUrl())->limit(500)->toString(),
            'method' => RequestFacade::method(),
        ]);

        return $entry;
    }

    /**
     * Derive the owning module from the model's namespace
     * (`Modules\DOCTOR\Models\Doctor` => `DOCTOR`).
     */
    private function moduleFor(Model $model): string
    {
        $class = $model->getMorphClass();
        $segments = explode('\\', $class);

        if (($segments[0] ?? null) === 'Modules' && isset($segments[1])) {
            return $segments[1];
        }

        /*
         * Vendor and `app/` models (Spatie roles and permissions, the User
         * model) are administered from the CORE access-control screens, so
         * they are attributed to CORE rather than to their composer package.
         */
        foreach ((array) config('audit.module_map', []) as $namespace => $module) {
            if (str_starts_with($class, (string) $namespace)) {
                return (string) $module;
            }
        }

        return 'CORE';
    }

    private function describe(string $event, Model $model): string
    {
        $label = $model->getAttribute('name')
            ?? $model->getAttribute('title')
            ?? $model->getAttribute('email')
            ?? '#'.$model->getKey();

        return sprintf('%s %s "%s"', ucfirst($event), class_basename($model), $label);
    }

    /**
     * @return array<int, string>
     */
    private function ignored(): array
    {
        return array_values(array_unique([
            ...AuditTrail::hiddenAttributes(),
            ...(array) config('audit.ignore_attributes', []),
        ]));
    }
}
