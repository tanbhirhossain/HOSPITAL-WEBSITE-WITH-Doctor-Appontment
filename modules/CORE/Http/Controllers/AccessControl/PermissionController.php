<?php

namespace Modules\CORE\Http\Controllers\AccessControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CORE\Requests\AccessControl\IndexPermissionRequest;
use Modules\CORE\Requests\AccessControl\StorePermissionRequest;
use Modules\CORE\Requests\AccessControl\UpdatePermissionRequest;
use Modules\CORE\Services\AccessControl\PermissionService;
use RuntimeException;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $service,
    ) {}

    public function index(IndexPermissionRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request));

        return Inertia::render('CORE::AccessControl/Permissions/Index', [
            'permissions' => $this->service->toDataTable($paginator, fn (Permission $permission): array => [
                'id' => $permission->id,
                'name' => $permission->name,
                'resource' => (string) str($permission->name)->before('.'),
                'action' => (string) str($permission->name)->after('.'),
                'guard_name' => $permission->guard_name,
                'roles_count' => $permission->roles_count ?? $permission->roles()->count(),
                'created_at' => $permission->created_at?->toISOString(),
                'created_at_human' => $permission->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'grouped' => $this->service->permissionMatrix(),
            'guards' => ['web'],
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', __('Permission created successfully.'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        try {
            $this->service->update($permission, $request->validated());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Permission updated successfully.'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        try {
            $this->service->delete($permission);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Permission deleted successfully.'));
    }
}
