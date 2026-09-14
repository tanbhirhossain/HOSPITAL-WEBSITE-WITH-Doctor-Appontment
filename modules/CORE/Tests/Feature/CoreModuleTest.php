<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class);

use App\Models\User;
use Modules\CORE\Models\AuditTrail;
use Modules\CORE\Models\SeoMetaData;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

/**
 * Covers the CORE module: dashboard, Spatie-backed access control, SEO
 * metadata and the audit trail.
 */
beforeEach(function (): void {
    $this->seed(\Modules\CORE\Database\Seeders\AccessControlSeeder::class);

    $this->admin = User::query()->where('email', 'admin@amzhospital.test')->firstOrFail();
    $this->admin->assignRole('super-admin');
});

/* ------------------------------------------------------------------ */
/*  Dashboard                                                          */
/* ------------------------------------------------------------------ */

it('renders the dashboard with the expected module page', function (): void {
    actingAs($this->admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('CORE::Dashboard/Index', false));
});

/* ------------------------------------------------------------------ */
/*  Access control                                                     */
/* ------------------------------------------------------------------ */

it('lists roles with their permission counts', function (): void {
    actingAs($this->admin)
        ->get('/admin/access-control/roles')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('CORE::AccessControl/Roles/Index', false)
                ->has('roles.data')
                ->has('permissionMatrix'),
        );
});

it('creates a role and syncs its permissions', function (): void {
    actingAs($this->admin)
        ->post('/admin/access-control/roles', [
            'name' => 'front-desk',
            'permissions' => ['doctor.view', 'schedule.create'],
        ])
        ->assertRedirect();

    assertDatabaseHas('roles', ['name' => 'front-desk']);

    expect(Role::findByName('front-desk')->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(['doctor.view', 'schedule.create']);
});

it('rejects a duplicate role name', function (): void {
    actingAs($this->admin)
        ->post('/admin/access-control/roles', ['name' => 'admin', 'permissions' => []])
        ->assertSessionHasErrors('name');
});

it('refuses to delete a protected role', function (): void {
    $role = Role::findByName('super-admin');

    actingAs($this->admin)
        ->delete("/admin/access-control/roles/{$role->id}")
        ->assertRedirect();

    assertDatabaseHas('roles', ['name' => 'super-admin']);
});

it('deletes a role that is not protected', function (): void {
    $role = Role::create(['name' => 'temp-role', 'guard_name' => 'web']);

    actingAs($this->admin)
        ->delete("/admin/access-control/roles/{$role->id}")
        ->assertRedirect();

    assertDatabaseMissing('roles', ['name' => 'temp-role']);
});

it('creates a permission in resource.action format and rejects bad input', function (): void {
    actingAs($this->admin)
        ->post('/admin/access-control/permissions', ['name' => 'inventory.adjust'])
        ->assertRedirect();

    assertDatabaseHas('permissions', ['name' => 'inventory.adjust']);

    actingAs($this->admin)
        ->post('/admin/access-control/permissions', ['name' => 'NotAValidPermission'])
        ->assertSessionHasErrors('name');
});

it('lists users and can filter by role', function (): void {
    actingAs($this->admin)
        ->get('/admin/access-control/users')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('CORE::AccessControl/Users/Index', false)
                ->has('users.data')
                ->has('roleOptions'),
        );

    actingAs($this->admin)
        ->get('/admin/access-control/users?filters[role][]=super-admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('users.data', 1));
});

it('creates a user with roles', function (): void {
    actingAs($this->admin)
        ->post('/admin/access-control/users', [
            'name' => 'Reception Lead',
            'email' => 'reception@amzhospital.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'roles' => ['editor'],
        ])
        ->assertRedirect();

    $user = User::query()->where('email', 'reception@amzhospital.test')->firstOrFail();

    expect($user->hasRole('editor'))->toBeTrue();
});

it('requires a confirmed password when creating a user', function (): void {
    actingAs($this->admin)
        ->post('/admin/access-control/users', [
            'name' => 'No Confirm',
            'email' => 'noconfirm@amzhospital.test',
            'password' => 'secret123',
            'password_confirmation' => 'different',
        ])
        ->assertSessionHasErrors('password');
});

it('will not let a user delete their own account', function (): void {
    actingAs($this->admin)
        ->delete("/admin/access-control/users/{$this->admin->id}")
        ->assertRedirect();

    assertDatabaseHas('users', ['id' => $this->admin->id]);
});

/* ------------------------------------------------------------------ */
/*  SEO meta data                                                      */
/* ------------------------------------------------------------------ */

it('lists SEO metadata', function (): void {
    actingAs($this->admin)
        ->get('/admin/seo')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('CORE::SEO/Index', false)->has('records.data'));
});

it('stores SEO metadata and trims empty values', function (): void {
    actingAs($this->admin)
        ->post('/admin/seo', [
            'meta_title' => 'Cardiology | AMZ Hospital',
            'meta_description' => 'Consultant-led heart care in Dhaka.',
            'meta_keywords' => 'cardiology, heart, dhaka',
            'robots' => 'index, follow',
            'schema_json' => '{"@type":"Hospital"}',
        ])
        ->assertRedirect();

    assertDatabaseHas('seo_meta_data', ['meta_title' => 'Cardiology | AMZ Hospital']);
});

it('rejects SEO metadata without a meta title', function (): void {
    actingAs($this->admin)
        ->post('/admin/seo', ['meta_description' => 'No title here.'])
        ->assertSessionHasErrors('meta_title');
});

it('deletes SEO metadata', function (): void {
    $seo = SeoMetaData::query()->create(['meta_title' => 'Discard me']);

    actingAs($this->admin)->delete("/admin/seo/{$seo->id}")->assertRedirect();

    assertDatabaseMissing('seo_meta_data', ['id' => $seo->id]);
});

/* ------------------------------------------------------------------ */
/*  Audit trail                                                        */
/* ------------------------------------------------------------------ */

it('records an audit entry when a role is created', function (): void {
    actingAs($this->admin)
        ->post('/admin/access-control/roles', ['name' => 'audited-role', 'permissions' => []]);

    assertDatabaseHas('audit_trails', [
        'event' => 'created',
        'module' => 'CORE',
        'user_id' => $this->admin->id,
    ]);
});

it('never stores passwords in the audit payload', function (): void {
    actingAs($this->admin)->post('/admin/access-control/users', [
        'name' => 'Audit Subject',
        'email' => 'subject@amzhospital.test',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'roles' => ['viewer'],
    ]);

    $entry = AuditTrail::query()->latest('id')->firstOrFail();

    expect(json_encode($entry->new_values))->not->toContain('secret123');
});

it('lists the audit trail and shows a single entry', function (): void {
    $entry = AuditTrail::query()->create([
        'event' => 'updated',
        'module' => 'CORE',
        'user_name' => 'Tester',
        'description' => 'Updated something important',
        'old_values' => ['title' => 'Before'],
        'new_values' => ['title' => 'After'],
    ]);

    get('/admin/audit-trail')->assertRedirect();

    actingAs($this->admin)
        ->get('/admin/audit-trail')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('CORE::AuditTrail/Index', false)->has('entries.data'));

    actingAs($this->admin)
        ->get("/admin/audit-trail/{$entry->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('CORE::AuditTrail/Show', false)->where('entry.id', $entry->id));
});

it('filters the audit trail by event', function (): void {
    AuditTrail::query()->create(['event' => 'deleted', 'module' => 'DOCTOR', 'user_name' => 'A']);
    AuditTrail::query()->create(['event' => 'created', 'module' => 'CORE', 'user_name' => 'B']);

    actingAs($this->admin)
        ->get('/admin/audit-trail?filters[event][]=deleted')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('entries.data', 1));
});

it('keeps guests out of the panel', function (): void {
    get('/admin/doctors')->assertRedirect('/login');
});

/* ------------------------------------------------------------------ */
/*  Remaining write paths                                              */
/* ------------------------------------------------------------------ */

it('updates a role and re-syncs its permissions', function (): void {
    actingAs($this->admin)->post('/admin/access-control/roles', [
        'name' => 'duty-manager',
        'permissions' => ['doctor.view'],
    ]);

    $role = Role::findByName('duty-manager');

    actingAs($this->admin)
        ->put("/admin/access-control/roles/{$role->id}", [
            'name' => 'shift-manager',
            'permissions' => ['doctor.update', 'schedule.create'],
        ])
        ->assertRedirect();

    $role->refresh();

    expect($role->name)->toBe('shift-manager')
        ->and($role->permissions->pluck('name')->all())
        ->toEqualCanonicalizing(['doctor.update', 'schedule.create']);
});

it('refuses to rename a protected role', function (): void {
    $role = Role::findByName('super-admin');

    actingAs($this->admin)
        ->put("/admin/access-control/roles/{$role->id}", ['name' => 'root'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($role->refresh()->name)->toBe('super-admin');
});

it('lists permissions grouped by resource', function (): void {
    actingAs($this->admin)
        ->get('/admin/access-control/permissions')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('CORE::AccessControl/Permissions/Index', false)
                ->has('permissions.data')
                ->has('grouped'),
        );
});

it('updates a permission name', function (): void {
    $permission = Permission::create(['name' => 'inventory.adjust', 'guard_name' => 'web']);

    actingAs($this->admin)
        ->put("/admin/access-control/permissions/{$permission->id}", ['name' => 'inventory.transfer'])
        ->assertRedirect();

    expect($permission->refresh()->name)->toBe('inventory.transfer');
});

it('deletes a permission', function (): void {
    $permission = Permission::create(['name' => 'inventory.archive', 'guard_name' => 'web']);

    actingAs($this->admin)
        ->delete("/admin/access-control/permissions/{$permission->id}")
        ->assertRedirect();

    assertDatabaseMissing('permissions', ['name' => 'inventory.archive']);
});

it('updates a user and their roles', function (): void {
    actingAs($this->admin)->post('/admin/access-control/users', [
        'name' => 'Shift Lead',
        'email' => 'shiftlead@amzhospital.test',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'roles' => ['viewer'],
    ]);

    $user = User::query()->where('email', 'shiftlead@amzhospital.test')->firstOrFail();

    actingAs($this->admin)
        ->put("/admin/access-control/users/{$user->id}", [
            'name' => 'Shift Supervisor',
            'email' => 'supervisor@amzhospital.test',
            'roles' => ['editor'],
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->name)->toBe('Shift Supervisor')
        ->and($user->email)->toBe('supervisor@amzhospital.test')
        ->and($user->roles->pluck('name')->all())->toBe(['editor']);
});

it('updates SEO metadata', function (): void {
    $seo = SeoMetaData::query()->create(['meta_title' => 'Before update']);

    actingAs($this->admin)
        ->put("/admin/seo/{$seo->id}", [
            'meta_title' => 'After update',
            'meta_description' => 'Refreshed description.',
            'robots' => 'noindex, nofollow',
            'og_type' => 'article',
        ])
        ->assertRedirect();

    $seo->refresh();

    expect($seo->meta_title)->toBe('After update')
        ->and($seo->robots)->toBe('noindex, nofollow');
});

it('rejects an invalid SEO robots directive', function (): void {
    $seo = SeoMetaData::query()->create(['meta_title' => 'Robots check']);

    actingAs($this->admin)
        ->put("/admin/seo/{$seo->id}", ['meta_title' => 'Robots check', 'robots' => 'index-everything'])
        ->assertSessionHasErrors('robots');
});

it('deletes a single audit entry', function (): void {
    $entry = AuditTrail::query()->create(['event' => 'updated', 'module' => 'CORE', 'user_name' => 'T']);

    actingAs($this->admin)->delete("/admin/audit-trail/{$entry->id}")->assertRedirect();

    assertDatabaseMissing('audit_trails', ['id' => $entry->id]);
});

it('prunes only audit entries past the retention window', function (): void {
    $stale = AuditTrail::query()->create(['event' => 'created', 'module' => 'CORE', 'user_name' => 'Old']);
    $fresh = AuditTrail::query()->create(['event' => 'created', 'module' => 'CORE', 'user_name' => 'New']);

    $stale->forceFill(['created_at' => now()->subDays(400)])->save();
    $fresh->forceFill(['created_at' => now()->subDay()])->save();

    actingAs($this->admin)->delete('/admin/audit-trail/prune')->assertRedirect();

    assertDatabaseMissing('audit_trails', ['id' => $stale->id]);
    assertDatabaseHas('audit_trails', ['id' => $fresh->id]);
});

it('filters roles by guard', function (): void {
    actingAs($this->admin)
        ->get('/admin/access-control/roles?filters[guard_name][]=web')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('roles.data'));

    actingAs($this->admin)
        ->get('/admin/access-control/roles?filters[guard_name][]=nope')
        ->assertSessionHasErrors('filters.guard_name.0');
});
