<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DOCTOR\Requests\PageSection\IndexPageSectionRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\DOCTOR\Models\PageSection;
use Modules\DOCTOR\Requests\PageSection\StorePageSectionRequest;
use Modules\DOCTOR\Requests\PageSection\UpdatePageSectionRequest;
use Modules\DOCTOR\Services\PageSectionService;

class PageSectionController extends Controller
{
    public function __construct(
        private readonly PageSectionService $service,
    ) {}

    public function index(IndexPageSectionRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'section_key', 'asc'));

        return Inertia::render('DOCTOR::PageSection/Index', [
            'sections' => $this->service->toDataTable($paginator, fn (PageSection $section): array => [
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
                'updated_at' => $section->updated_at?->toISOString(),
                'updated_at_human' => $section->updated_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
        ]);
    }

    public function store(StorePageSectionRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', __('Page section created successfully.'));
    }

    public function update(UpdatePageSectionRequest $request, PageSection $section): RedirectResponse
    {
        $this->service->update($section, $request->validated());

        return back()->with('success', __('Page section updated successfully.'));
    }

    public function destroy(PageSection $section): RedirectResponse
    {
        $this->service->delete($section);

        return back()->with('success', __('Page section deleted successfully.'));
    }
}
