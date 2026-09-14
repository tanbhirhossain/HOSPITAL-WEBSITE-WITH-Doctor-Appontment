<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Requests\Department\IndexDepartmentRequest;
use Modules\DOCTOR\Requests\Department\StoreDepartmentRequest;
use Modules\DOCTOR\Requests\Department\UpdateDepartmentRequest;
use Modules\DOCTOR\Services\DepartmentCategoryService;
use Modules\DOCTOR\Services\DepartmentService;
use Modules\DOCTOR\Services\SymptomService;

/**
 * Departments own their page content, so create/edit render a single form
 * built from collapsible sections (info, page sections, symptoms, SEO).
 */
class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $service,
        private readonly DepartmentCategoryService $categories,
        private readonly SymptomService $symptoms,
    ) {}

    public function index(IndexDepartmentRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'sort_order', 'asc'));

        return Inertia::render('DOCTOR::Department/Index', [
            'departments' => $this->service->toDataTable($paginator, fn (Department $department): array => [
                'id' => $department->id,
                'title' => $department->title,
                'slug' => $department->slug,
                'short_description' => $department->short_description,
                'icon' => $department->icon,
                'featured_image' => $department->featured_image,
                'category_id' => $department->department_category_id,
                'category_name' => $department->category?->name,
                'sort_order' => $department->sort_order,
                'is_popular_search' => (bool) $department->is_popular_search,
                'is_featured' => (bool) $department->is_featured,
                'is_active' => (bool) $department->is_active,
                'doctors_count' => $department->doctors_count ?? $department->doctors()->count(),
                'page_sections_count' => $department->page_sections_count ?? $department->pageSections()->count(),
                'symptoms_count' => $department->symptoms_count ?? $department->symptoms()->count(),
                'created_at' => $department->created_at?->toISOString(),
                'created_at_human' => $department->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'categoryOptions' => $this->categories->categoryOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DOCTOR::Department/Form', $this->formProps());
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $department = $this->service->saveWithRelations(
            id: null,
            data: Arr::except($validated, ['page_sections', 'symptoms', 'seo']),
            pageSections: (array) ($validated['page_sections'] ?? []),
            symptomIds: (array) ($validated['symptoms'] ?? []),
            seoData: (array) ($validated['seo'] ?? []),
        );

        return redirect()
            ->route('doctor.departments.edit', $department)
            ->with('success', __('Department created successfully.'));
    }

    public function edit(Department $department): Response
    {
        $department->load(['pageSections', 'symptoms', 'seo']);

        return Inertia::render('DOCTOR::Department/Form', $this->formProps($department));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $validated = $request->validated();

        $this->service->saveWithRelations(
            id: $department->id,
            data: Arr::except($validated, ['page_sections', 'symptoms', 'seo']),
            pageSections: (array) ($validated['page_sections'] ?? []),
            symptomIds: (array) ($validated['symptoms'] ?? []),
            seoData: (array) ($validated['seo'] ?? []),
        );

        return back()->with('success', __('Department updated successfully.'));
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->doctors()->exists()) {
            return back()->with('error', __('This department still has doctors assigned to it.'));
        }

        $this->service->delete($department);

        return back()->with('success', __('Department deleted successfully.'));
    }

    public function toggleFeatured(Department $department): RedirectResponse
    {
        $this->service->toggleFeatured($department);

        return back()->with('success', __('Featured state updated.'));
    }

    /**
     * Everything the department form needs, whether it is blank or editing.
     *
     * @return array<string, mixed>
     */
    private function formProps(?Department $department = null): array
    {
        return [
            'department' => $department === null ? null : [
                'id' => $department->id,
                'department_category_id' => $department->department_category_id,
                'title' => $department->title,
                'slug' => $department->slug,
                'short_description' => $department->short_description,
                'icon' => $department->icon,
                'featured_image' => $department->featured_image,
                'sort_order' => $department->sort_order,
                'is_popular_search' => (bool) $department->is_popular_search,
                'is_featured' => (bool) $department->is_featured,
                'is_active' => (bool) $department->is_active,
                'page_sections' => $department->pageSections
                    ->map(fn ($section): array => [
                        'id' => $section->id,
                        'section_key' => $section->section_key,
                        'badge' => $section->badge,
                        'title' => $section->title,
                        'subtitle' => $section->subtitle,
                        'primary_button_text' => $section->primary_button_text,
                        'primary_button_url' => $section->primary_button_url,
                        'secondary_button_text' => $section->secondary_button_text,
                        'secondary_button_url' => $section->secondary_button_url,
                        'image' => $section->image,
                        'is_active' => (bool) $section->is_active,
                    ])
                    ->values()
                    ->all(),
                'symptoms' => $department->symptoms->pluck('id')->all(),
                'seo' => $this->seoPayload($department),
            ],
            'categoryOptions' => $this->categories->categoryOptions(),
            'symptomOptions' => $this->symptoms->symptomOptions(),
            'sectionKeyOptions' => $this->service->sectionKeyOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seoPayload(Department $department): array
    {
        $seo = $department->relationLoaded('seo') ? $department->seo : $department->seo()->first();

        return [
            'meta_title' => $seo?->meta_title ?? '',
            'meta_description' => $seo?->meta_description ?? '',
            'meta_keywords' => $seo?->meta_keywords ?? '',
            'canonical_url' => $seo?->canonical_url ?? '',
            'robots' => $seo?->robots ?? 'index, follow',
            'og_title' => $seo?->og_title ?? '',
            'og_description' => $seo?->og_description ?? '',
            'og_image' => $seo?->og_image ?? '',
            'og_type' => $seo?->og_type ?? 'website',
            'twitter_card' => $seo?->twitter_card ?? 'summary_large_image',
            'twitter_title' => $seo?->twitter_title ?? '',
            'twitter_description' => $seo?->twitter_description ?? '',
            'twitter_image' => $seo?->twitter_image ?? '',
            'schema_json' => $seo?->schema_json ?? '',
        ];
    }
}
