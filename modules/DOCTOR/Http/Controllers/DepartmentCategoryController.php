<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DOCTOR\Requests\DepartmentCategory\IndexDepartmentCategoryRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\DOCTOR\Models\DepartmentCategory;
use Modules\DOCTOR\Requests\DepartmentCategory\StoreDepartmentCategoryRequest;
use Modules\DOCTOR\Requests\DepartmentCategory\UpdateDepartmentCategoryRequest;
use Modules\DOCTOR\Services\DepartmentCategoryService;

class DepartmentCategoryController extends Controller
{
    public function __construct(
        private readonly DepartmentCategoryService $service,
    ) {}

    public function index(IndexDepartmentCategoryRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'sort_order', 'asc'));

        return Inertia::render('DOCTOR::DepartmentCategory/Index', [
            'categories' => $this->service->toDataTable($paginator, fn (DepartmentCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'sort_order' => $category->sort_order,
                'is_active' => (bool) $category->is_active,
                'departments_count' => $category->departments_count ?? $category->departments()->count(),
                'created_at' => $category->created_at?->toISOString(),
                'created_at_human' => $category->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
        ]);
    }

    public function store(StoreDepartmentCategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', __('Category created successfully.'));
    }

    public function update(UpdateDepartmentCategoryRequest $request, DepartmentCategory $category): RedirectResponse
    {
        $this->service->update($category, $request->validated());

        return back()->with('success', __('Category updated successfully.'));
    }

    public function destroy(DepartmentCategory $category): RedirectResponse
    {
        if ($category->departments()->exists()) {
            return back()->with('error', __('Move or delete this category\'s departments first.'));
        }

        $this->service->delete($category);

        return back()->with('success', __('Category deleted successfully.'));
    }
}
