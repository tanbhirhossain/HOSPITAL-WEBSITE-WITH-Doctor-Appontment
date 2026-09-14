<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Modules\DOCTOR\Models\DoctorSchedule;
use Modules\DOCTOR\Requests\DoctorSchedule\StoreDoctorScheduleRequest;
use Modules\DOCTOR\Requests\DoctorSchedule\UpdateDoctorScheduleRequest;
use Modules\DOCTOR\Services\DoctorScheduleService;

class DoctorScheduleController extends Controller
{
    public function __construct(
        private readonly DoctorScheduleService $service,
    ) {}

    public function index(DataTableRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'day_of_week', 'asc'));

        return Inertia::render('DOCTOR::DoctorSchedule/Index', [
            'schedules' => $this->service->toDataTable($paginator, fn (DoctorSchedule $schedule): array => [
                'id' => $schedule->id,
                'doctor_id' => $schedule->doctor_id,
                'doctor_name' => $schedule->doctor?->name,
                'day_of_week' => $schedule->day_of_week,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'time_range' => $schedule->time_range,
                'consultation_type' => $schedule->consultation_type,
                'availability_status' => $schedule->availability_status,
                'max_patients' => $schedule->max_patients,
                'is_active' => (bool) $schedule->is_active,
                'created_at' => $schedule->created_at?->toISOString(),
                'created_at_human' => $schedule->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'doctorOptions' => collect($this->service->doctorOptions())
                ->map(fn (string $name, int $id): array => ['value' => $id, 'label' => $name])
                ->values()
                ->all(),
            'dayOptions' => collect($this->service->dayOptions())
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'consultationTypeOptions' => collect($this->service->consultationTypeOptions())
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'availabilityOptions' => collect($this->service->availabilityOptions())
                ->map(fn (string $label, string $value): array => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
        ]);
    }

    public function store(StoreDoctorScheduleRequest $request): RedirectResponse
    {
        try {
            $this->service->create($request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Schedule slot created successfully.'));
    }

    public function update(UpdateDoctorScheduleRequest $request, DoctorSchedule $schedule): RedirectResponse
    {
        try {
            $this->service->update($schedule, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Schedule slot updated successfully.'));
    }

    public function destroy(DoctorSchedule $schedule): RedirectResponse
    {
        $this->service->delete($schedule);

        return back()->with('success', __('Schedule slot deleted successfully.'));
    }
}
