<?php

use App\Models\User;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\DepartmentCategory;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\PageSection;
use Modules\DOCTOR\Models\Symptom;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [

    /*
    |--------------------------------------------------------------------------
    | Audit trail
    |--------------------------------------------------------------------------
    |
    | Every Eloquent event on the models below is written to `audit_trails`.
    | The `AuditTrail` and `SeoMetaData` models are deliberately excluded so
    | logging can never recurse into itself.
    |
    */

    'enabled' => env('AUDIT_ENABLED', true),

    'models' => [
        User::class,
        Role::class,
        Permission::class,
        DepartmentCategory::class,
        Department::class,
        Doctor::class,
        PageSection::class,
        Symptom::class,
    ],

    'events' => ['created', 'updated', 'deleted', 'restored'],

    /*
    |--------------------------------------------------------------------------
    | Namespace to module map
    |--------------------------------------------------------------------------
    |
    | Used to attribute audited models that live outside `Modules\` to the
    | module whose screens administer them. Checked in order, first match wins.
    |
    */

    'module_map' => [
        'Spatie\\Permission\\Models' => 'CORE',
        'App\\Models' => 'CORE',
    ],

    /*
    | Attributes stripped from every payload, in addition to the secrets that
    | `AuditTrail::hiddenAttributes()` already removes. Add noisy columns
    | (timestamps, counters) here to keep the diff readable.
    */
    'ignore_attributes' => [
        'updated_at',
        'remember_token',
    ],

    'retention_days' => env('AUDIT_RETENTION_DAYS', 180),
];
