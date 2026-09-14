<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\DOCTOR\Models\DoctorExpertise;
use Modules\DOCTOR\Requests\DoctorExpertise\StoreDoctorExpertiseRequest;
use Modules\DOCTOR\Requests\DoctorExpertise\UpdateDoctorExpertiseRequest;
use Modules\DOCTOR\Services\DoctorExpertiseService;

class DoctorExpertiseController extends Controller
{
    public function __construct(
        private readonly DoctorExpertiseService $service,
    ) {}

    public function index(DataTableRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request));

        return Inertia::render('DOCTOR::DoctorExpertise/Index', [
            'expertises' => $this->service->toDataTable($paginator, fn (DoctorExpertise $expertise): array => [
                'id' => $expertise->id,
                'doctor_id' => $expertise->doctor_id,
                'doctor_name' => $expertise->doctor?->name,
                'title' => $expertise->title,
                'description' => $expertise->description,
                'icon' => $expertise->icon,
                'sort_order' => $expertise->sort_order,
                'created_at' => $expertise->created_at?->toISOString(),
                'created_at_human' => $expertise->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'doctorOptions' => collect($this->service->doctorOptions())
                ->map(fn (string $name, int $id): array => ['value' => $id, 'label' => $name])
                ->values()
                ->all(),
        ]);
    }

    public function store(StoreDoctorExpertiseRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', __('Expertise added successfully.'));
    }

    public function update(UpdateDoctorExpertiseRequest $request, DoctorExpertise $expertise): RedirectResponse
    {
        $this->service->update($expertise, $request->validated());

        return back()->with('success', __('Expertise updated successfully.'));
    }

    public function destroy(DoctorExpertise $expertise): RedirectResponse
    {
        $this->service->delete($expertise);

        return back()->with('success', __('Expertise deleted successfully.'));
    }
}
