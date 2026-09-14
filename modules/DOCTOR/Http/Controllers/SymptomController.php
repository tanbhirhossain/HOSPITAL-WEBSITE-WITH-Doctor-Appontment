<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DOCTOR\Requests\Symptom\IndexSymptomRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\DOCTOR\Models\Symptom;
use Modules\DOCTOR\Requests\Symptom\StoreSymptomRequest;
use Modules\DOCTOR\Requests\Symptom\UpdateSymptomRequest;
use Modules\DOCTOR\Services\SymptomService;

class SymptomController extends Controller
{
    public function __construct(
        private readonly SymptomService $service,
    ) {}

    public function index(IndexSymptomRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'sort_order', 'asc'));

        return Inertia::render('DOCTOR::Symptom/Index', [
            'symptoms' => $this->service->toDataTable($paginator, fn (Symptom $symptom): array => [
                'id' => $symptom->id,
                'title' => $symptom->title,
                'icon' => $symptom->icon,
                'link_url' => $symptom->link_url,
                'sort_order' => $symptom->sort_order,
                'is_active' => (bool) $symptom->is_active,
                'created_at' => $symptom->created_at?->toISOString(),
                'created_at_human' => $symptom->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
        ]);
    }

    public function store(StoreSymptomRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', __('Symptom created successfully.'));
    }

    public function update(UpdateSymptomRequest $request, Symptom $symptom): RedirectResponse
    {
        $this->service->update($symptom, $request->validated());

        return back()->with('success', __('Symptom updated successfully.'));
    }

    public function destroy(Symptom $symptom): RedirectResponse
    {
        $this->service->delete($symptom);

        return back()->with('success', __('Symptom deleted successfully.'));
    }
}
