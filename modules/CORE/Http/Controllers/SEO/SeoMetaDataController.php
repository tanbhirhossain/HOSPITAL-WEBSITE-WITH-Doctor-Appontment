<?php

namespace Modules\CORE\Http\Controllers\SEO;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CORE\Models\SeoMetaData;
use Modules\CORE\Requests\SEO\IndexSeoMetaDataRequest;
use Modules\CORE\Requests\SEO\StoreSeoMetaDataRequest;
use Modules\CORE\Requests\SEO\UpdateSeoMetaDataRequest;
use Modules\CORE\Services\SEO\SeoMetaDataService;

class SeoMetaDataController extends Controller
{
    public function __construct(
        private readonly SeoMetaDataService $service,
    ) {}

    public function index(IndexSeoMetaDataRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'id', 'desc'));

        return Inertia::render('CORE::SEO/Index', [
            'records' => $this->service->toDataTable($paginator, fn (SeoMetaData $seo): array => [
                'id' => $seo->id,
                'meta_title' => $seo->meta_title,
                'meta_description' => $seo->meta_description,
                'canonical_url' => $seo->canonical_url,
                'robots' => $seo->robots,
                'seoable_type' => $seo->seoable_type,
                'seoable_type_label' => class_basename((string) $seo->seoable_type) ?: 'Global',
                'seoable_id' => $seo->seoable_id,
                'owner_label' => $seo->owner_label,
                'is_complete' => filled($seo->meta_title) && filled($seo->meta_description),
                'updated_at' => $seo->updated_at?->toISOString(),
                'updated_at_human' => $seo->updated_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'seoableTypes' => collect($this->service->seoableTypes())
                ->map(fn (string $label, string $class): array => ['value' => $class, 'label' => $label])
                ->values()
                ->all(),
        ]);
    }

    public function store(StoreSeoMetaDataRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', __('SEO metadata created successfully.'));
    }

    public function update(UpdateSeoMetaDataRequest $request, SeoMetaData $seo): RedirectResponse
    {
        $this->service->update($seo, $request->validated());

        return back()->with('success', __('SEO metadata updated successfully.'));
    }

    public function destroy(SeoMetaData $seo): RedirectResponse
    {
        $this->service->delete($seo);

        return back()->with('success', __('SEO metadata deleted successfully.'));
    }
}
