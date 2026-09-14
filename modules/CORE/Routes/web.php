<?php

use Illuminate\Support\Facades\Route;
use Modules\CORE\Http\Controllers\AccessControl\PermissionController;
use Modules\CORE\Http\Controllers\AccessControl\RoleController;
use Modules\CORE\Http\Controllers\AccessControl\UserAccessController;
use Modules\CORE\Http\Controllers\AuditTrail\AuditTrailController;
use Modules\CORE\Http\Controllers\DashboardController;
use Modules\CORE\Http\Controllers\SEO\SeoMetaDataController;

/*
|--------------------------------------------------------------------------
| CORE module — administration
|--------------------------------------------------------------------------
|
| Prefix: /admin   Name: core.*
| Every route is behind `auth` + `verified`; fine-grained checks are done
| with Spatie permissions via the `permission:` middleware on write actions.
|
*/

Route::prefix('admin')
    ->name('core.')
    ->middleware(['web', 'auth', 'verified'])
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        /* ------------------------- Access control ------------------------ */
        Route::prefix('access-control')->name('acl.')->group(function (): void {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('roles', [RoleController::class, 'store'])
                ->middleware('permission:role.create')
                ->name('roles.store');
            Route::put('roles/{role}', [RoleController::class, 'update'])
                ->middleware('permission:role.update')
                ->name('roles.update');
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])
                ->middleware('permission:role.delete')
                ->name('roles.destroy');

            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::post('permissions', [PermissionController::class, 'store'])
                ->middleware('permission:permission.create')
                ->name('permissions.store');
            Route::put('permissions/{permission}', [PermissionController::class, 'update'])
                ->middleware('permission:permission.update')
                ->name('permissions.update');
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])
                ->middleware('permission:permission.delete')
                ->name('permissions.destroy');

            Route::get('users', [UserAccessController::class, 'index'])->name('users.index');
            Route::post('users', [UserAccessController::class, 'store'])
                ->middleware('permission:user.create')
                ->name('users.store');
            Route::put('users/{user}', [UserAccessController::class, 'update'])
                ->middleware('permission:user.update')
                ->name('users.update');
            Route::delete('users/{user}', [UserAccessController::class, 'destroy'])
                ->middleware('permission:user.delete')
                ->name('users.destroy');
        });

        /* ------------------------------- SEO ----------------------------- */
        Route::prefix('seo')->name('seo.')->group(function (): void {
            Route::get('/', [SeoMetaDataController::class, 'index'])->name('index');
            Route::post('/', [SeoMetaDataController::class, 'store'])
                ->middleware('permission:seo.create')
                ->name('store');
            Route::put('{seo}', [SeoMetaDataController::class, 'update'])
                ->middleware('permission:seo.update')
                ->name('update');
            Route::delete('{seo}', [SeoMetaDataController::class, 'destroy'])
                ->middleware('permission:seo.delete')
                ->name('destroy');
        });

        /* --------------------------- Audit trail ------------------------- */
        Route::prefix('audit-trail')->name('audit.')->group(function (): void {
            Route::get('/', [AuditTrailController::class, 'index'])->name('index');
            Route::get('{auditTrail}', [AuditTrailController::class, 'show'])->name('show');
            Route::delete('prune', [AuditTrailController::class, 'prune'])
                ->middleware('permission:audit.delete')
                ->name('prune');
            Route::delete('{auditTrail}', [AuditTrailController::class, 'destroy'])
                ->middleware('permission:audit.delete')
                ->name('destroy');
        });
    });
