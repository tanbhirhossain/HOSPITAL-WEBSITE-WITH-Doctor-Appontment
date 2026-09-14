<?php

namespace Modules\CORE\Providers;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;
use Modules\CORE\Interfaces\AuditTrailRepositoryInterface;
use Modules\CORE\Interfaces\PermissionRepositoryInterface;
use Modules\CORE\Interfaces\RoleRepositoryInterface;
use Modules\CORE\Interfaces\SEOMetaDataRepositoryInterface;
use Modules\CORE\Interfaces\UserRepositoryInterface;
use Modules\CORE\Observers\AuditTrailObserver;
use Modules\CORE\Repositories\AuditTrailRepository;
use Modules\CORE\Repositories\PermissionRepository;
use Modules\CORE\Repositories\RoleRepository;
use Modules\CORE\Repositories\SeoMetaDataRepository;
use Modules\CORE\Repositories\UserRepository;
use Modules\CORE\Services\AuditTrail\AuditRecorder;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class COREServiceProvider extends ServiceProvider
{
    /**
     * Interface => concrete bindings. Everything above the persistence layer
     * depends on an interface, never on Eloquent directly.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        RoleRepositoryInterface::class => RoleRepository::class,
        PermissionRepositoryInterface::class => PermissionRepository::class,
        UserRepositoryInterface::class => UserRepository::class,
        SEOMetaDataRepositoryInterface::class => SeoMetaDataRepository::class,
        AuditTrailRepositoryInterface::class => AuditTrailRepository::class,
    ];

    public function register(): void
    {
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(SEOMetaDataRepositoryInterface::class, SeoMetaDataRepository::class);
        $this->app->singleton(AuditTrailRepositoryInterface::class, AuditTrailRepository::class);
        $this->app->singleton(AuditRecorder::class, AuditRecorder::class);

        $this->app->singleton(AuditTrailObserver::class, AuditTrailObserver::class);

        $this->mergeConfigFrom(__DIR__.'/../Config/audit.php', 'audit');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');

        if (file_exists(__DIR__.'/../Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        }

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->registerMiddleware();
        $this->registerSharedProps();
        $this->registerAuditObservers();
        $this->registerAuthenticationAudit();
        $this->registerGates();
    }

    /**
     * Spatie ships the classes but does not alias them in Laravel 12, so the
     * `permission:` / `role:` route middleware are registered from here
     * instead of `bootstrap/app.php`.
     */
    private function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);

        $router->aliasMiddleware('permission', PermissionMiddleware::class);
        $router->aliasMiddleware('role', RoleMiddleware::class);
        $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
    }

    /**
     * Props shared with every Inertia response, without touching
     * `app/Http/Middleware/HandleInertiaRequests.php`.
     *
     * The framework middleware owns the `auth` key (it shares the user on
     * every request and runs after service providers), so this module shares
     * its access-control data under its own `acl` key and its flash messages
     * under `flash`, neither of which the middleware sets.
     */
    private function registerSharedProps(): void
    {
        Inertia::share('flash', fn (): array => [
            'success' => session('success'),
            'error' => session('error'),
            'warning' => session('warning'),
            'info' => session('info'),
        ]);

        Inertia::share('acl', function (): array {
            /** @var \App\Models\User|null $user */
            $user = $this->app['auth']->user();

            if ($user === null) {
                return ['user' => null, 'roles' => [], 'permissions' => []];
            }

            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'roles' => $user->roles()->pluck('name')->all(),
                'permissions' => $user->getAllPermissions()->pluck('name')->all(),
            ];
        });
    }

    /**
     * Attach one observer to every auditable model listed in `config/audit.php`.
     */
    private function registerAuditObservers(): void
    {
        if (config('audit.enabled') !== true) {
            return;
        }

        $observer = $this->app->make(AuditTrailObserver::class);

        foreach ((array) config('audit.models', []) as $model) {
            if (class_exists($model) && is_subclass_of($model, \Illuminate\Database\Eloquent\Model::class)) {
                $model::observe($observer);
            }
        }
    }

    /**
     * Sign-in / sign-out / failed sign-in have no subject model, so they are
     * recorded through Laravel's authentication events.
     */
    private function registerAuthenticationAudit(): void
    {
        $recorder = fn (): AuditRecorder => $this->app->make(AuditRecorder::class);

        Event::listen(Login::class, function (Login $event) use ($recorder): void {
            $recorder()->recordAuthEvent(
                'login',
                sprintf('%s signed in.', $event->user->name ?? $event->user->email),
                ['email' => $event->user->email],
            );
        });

        Event::listen(Logout::class, function (Logout $event) use ($recorder): void {
            $name = $event->user->name ?? $event->user->email ?? 'Unknown user';

            $recorder()->recordAuthEvent('logout', sprintf('%s signed out.', $name));
        });

        Event::listen(Failed::class, function (Failed $event) use ($recorder): void {
            $recorder()->recordAuthEvent(
                'failed-login',
                'A sign in attempt failed.',
                ['email' => $event->credentials['email'] ?? null],
            );
        });
    }

    /**
     * Grant every capability to the `super-admin` role without having to
     * enumerate permissions anywhere else.
     */
    private function registerGates(): void
    {
        Gate::before(static function (?User $user, string $ability): ?bool {
            if ($user === null) {
                return null;
            }

            return $user->hasRole('super-admin') ? true : null;
        });
    }

    /**
     * Models managed by this module (used by seeders and the audit config).
     *
     * @return array<int, class-string>
     */
    public static function aclModels(): array
    {
        return [Role::class, Permission::class, User::class];
    }
}
