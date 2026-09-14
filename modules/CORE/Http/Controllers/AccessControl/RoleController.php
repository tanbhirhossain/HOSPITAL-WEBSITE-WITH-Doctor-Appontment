<?php

namespace Modules\CORE\Http\Controllers\AccessControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CORE\Requests\AccessControl\IndexRoleRequest;
use Modules\CORE\Requests\AccessControl\StoreRoleRequest;
use Modules\CORE\Requests\AccessControl\UpdateRoleRequest;
use Modules\CORE\Services\AccessControl\RoleService;
use RuntimeException;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $service,
    ) {}

    public function index(IndexRoleRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request));

        return Inertia::render('CORE::AccessControl/Roles/Index', [
            'roles' => $this->service->toDataTable($paginator, fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions_count' => $role->permissions_count ?? $role->permissions()->count(),
                'users_count' => $role->users_count ?? $role->users()->count(),
                'is_protected' => $this->service->isProtectedName($role->name),
                'permissions' => $role->permissions->pluck('name')->all(),
                'created_at' => $role->created_at?->toISOString(),
                'created_at_human' => $role->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'permissionMatrix' => $this->service->permissionMatrix(),
            'guards' => ['web'],
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = Arr::except($request->validated(), ['permissions']);

        $this->service->createWithPermissions($data, (array) $request->input('permissions', []));

        return back()->with('success', __('Role created successfully.'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $data = Arr::except($request->validated(), ['permissions']);

            $this->service->updateWithPermissions($role, $data, (array) $request->input('permissions', []));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->service->delete($role);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Role deleted successfully.'));
    }
}
