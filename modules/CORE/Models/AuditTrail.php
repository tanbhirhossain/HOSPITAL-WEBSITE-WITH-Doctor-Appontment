<?php

namespace Modules\CORE\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only record of every auditable change in the system.
 *
 * Users are denormalised (`user_name`) so the history still reads correctly
 * after the referenced account is deleted.
 */
class AuditTrail extends Model
{
    protected $table = 'audit_trails';

    protected $fillable = [
        'user_id',
        'user_name',
        'event',
        'module',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Attributes that must never be persisted in an audit payload.
     *
     * @return array<int, string>
     */
    public static function hiddenAttributes(): array
    {
        return ['password', 'remember_token', 'api_token', 'two_factor_secret', 'two_factor_recovery_codes'];
    }

    public function scopeForEvent(Builder $query, ?string $event): Builder
    {
        return $event ? $query->where('event', $event) : $query;
    }

    public function scopeForModule(Builder $query, ?string $module): Builder
    {
        return $module ? $query->where('module', $module) : $query;
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Signed in',
            'logout' => 'Signed out',
            'failed-login' => 'Failed sign in',
            default => (string) str($this->event)->headline()->toString(),
        };
    }
}
