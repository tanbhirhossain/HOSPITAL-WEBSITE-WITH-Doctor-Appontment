<?php

namespace Modules\CORE\Http\Controllers\AccessControl;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CORE\Requests\AccessControl\IndexUserRequest;
use Modules\CORE\Requests\AccessControl\StoreUserRequest;
use Modules\CORE\Requests\AccessControl\UpdateUserRequest;
use Modules\CORE\Services\AccessControl\UserAccessService;
use RuntimeException;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    public function __construct(
        private readonly UserAccessService $service,
    ) {}

    public function index(IndexUserRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request));

        return Inertia::render('CORE::AccessControl/Users/Index', [
            'users' => $this->service->toDataTable($paginator, fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => $user->email_verified_at !== null,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'roles' => $user->roles->pluck('name')->all(),
                'direct_permissions' => $user->permissions->pluck('name')->all(),
                'initials' => (string) str($user->name)
                    ->explode(' ')
                    ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
                    ->take(2)
                    ->implode(''),
                'created_at' => $user->created_at?->toISOString(),
                'created_at_human' => $user->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'roleOptions' => Role::query()->orderBy('name')->pluck('name', 'name')->all(),
            'permissionMatrix' => $this->service->permissionMatrix(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->service->createWithAccess(
            $request->validated(),
            (array) $request->input('roles', []),
            (array) $request->input('permissions', []),
        );

        return back()->with('success', __('User created successfully.'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $data = Arr::except($request->validated(), ['roles', 'permissions']);

            $this->service->updateWithPermissions($user, $data, (array) $request->input('permissions', []));

            $this->service->syncAccess(
                $user->refresh(),
                (array) $request->input('roles', []),
                (array) $request->input('permissions', []),
                $data['password'] ?? null,
            );
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('User access updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', __('You cannot delete your own account.'));
        }

        $this->service->delete($user);

        return back()->with('success', __('User deleted successfully.'));
    }
}
