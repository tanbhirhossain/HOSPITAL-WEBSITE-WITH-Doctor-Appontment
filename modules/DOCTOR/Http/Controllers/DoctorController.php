<?php

namespace Modules\DOCTOR\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CORE\Support\Http\Requests\DataTableRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorSchedule;
use Modules\DOCTOR\Requests\Doctor\StoreDoctorRequest;
use Modules\DOCTOR\Requests\Doctor\UpdateDoctorRequest;
use Modules\DOCTOR\Services\DoctorService;

/**
 * A doctor is edited through one form split into collapsible sections:
 * the profile, a repeatable weekly timetable, expertises and SEO.
 */
class DoctorController extends Controller
{
    public function __construct(
        private readonly DoctorService $service,
    ) {}

    public function index(DataTableRequest $request): Response
    {
        $paginator = $this->service->paginate($this->service->dataTableQuery($request, 'id', 'desc'));

        return Inertia::render('DOCTOR::Doctor/Index', [
            'doctors' => $this->service->toDataTable($paginator, fn (Doctor $doctor): array => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'slug' => $doctor->slug,
                'initials' => $doctor->initials,
                'designation' => $doctor->designation,
                'specialty' => $doctor->specialty,
                'qualification' => $doctor->qualification,
                'experience' => $doctor->experience,
                'experience_years' => $doctor->experience_years,
                'hospital_name' => $doctor->hospital_name,
                'location' => $doctor->location,
                'profile_photo' => $doctor->profile_photo,
                'photo_url' => $doctor->photo_url,
                'bio' => $doctor->bio,
                'rating' => (float) $doctor->rating,
                'reviews_count' => $doctor->reviews_count,
                'social_links' => $doctor->social_links,
                'department_id' => $doctor->department_id,
                'department_name' => $doctor->department?->title,
                'is_featured' => (bool) $doctor->is_featured,
                'is_active' => (bool) $doctor->is_active,
                'expertises_count' => $doctor->expertises_count ?? 0,
                'schedules_count' => $doctor->schedules_count ?? 0,
                'expertises' => $doctor->relationLoaded('expertises')
                    ? $doctor->expertises->map(fn ($expertise): array => [
                        'title' => $expertise->title,
                        'description' => $expertise->description,
                        'icon' => $expertise->icon,
                        'sort_order' => $expertise->sort_order,
                    ])->all()
                    : [],
                'created_at' => $doctor->created_at?->toISOString(),
                'created_at_human' => $doctor->created_at?->diffForHumans(),
            ]),
            'filters' => $request->filters(),
            'query' => $request->queryState(),
            'departmentOptions' => $this->service->departmentOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DOCTOR::Doctor/Form', $this->formProps());
    }

    public function store(StoreDoctorRequest $request): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $doctor = $this->service->saveWithRelations(
                doctor: null,
                data: Arr::except($validated, ['expertises', 'schedules', 'seo']),
                expertises: (array) ($validated['expertises'] ?? []),
                schedules: (array) ($validated['schedules'] ?? []),
                seoData: (array) ($validated['seo'] ?? []),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('doctor.edit', $doctor)
            ->with('success', __('Doctor created successfully.'));
    }

    public function edit(Doctor $doctor): Response
    {
        $doctor->load(['expertises', 'schedules', 'seo']);

        return Inertia::render('DOCTOR::Doctor/Form', $this->formProps($doctor));
    }

    public function update(UpdateDoctorRequest $request, Doctor $doctor): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $this->service->saveWithRelations(
                doctor: $doctor,
                data: Arr::except($validated, ['expertises', 'schedules', 'seo']),
                expertises: (array) ($validated['expertises'] ?? []),
                schedules: (array) ($validated['schedules'] ?? []),
                seoData: (array) ($validated['seo'] ?? []),
            );
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', __('Doctor updated successfully.'));
    }

    public function destroy(Doctor $doctor): RedirectResponse
    {
        $this->service->delete($doctor);

        return back()->with('success', __('Doctor deleted successfully.'));
    }

    public function toggleFeatured(Doctor $doctor): RedirectResponse
    {
        $this->service->update($doctor, ['is_featured' => ! $doctor->is_featured]);

        return back()->with('success', __('Featured state updated.'));
    }

    public function toggleStatus(Doctor $doctor): RedirectResponse
    {
        $this->service->update($doctor, ['is_active' => ! $doctor->is_active]);

        return back()->with('success', __('Availability updated.'));
    }

    /**
     * Everything the doctor form needs, whether it is blank or editing.
     *
     * @return array<string, mixed>
     */
    private function formProps(?Doctor $doctor = null): array
    {
        return [
            'doctor' => $doctor === null ? null : [
                'id' => $doctor->id,
                'department_id' => $doctor->department_id,
                'name' => $doctor->name,
                'slug' => $doctor->slug,
                'designation' => $doctor->designation,
                'specialty' => $doctor->specialty,
                'qualification' => $doctor->qualification,
                'experience' => $doctor->experience,
                'experience_years' => $doctor->experience_years,
                'hospital_name' => $doctor->hospital_name,
                'location' => $doctor->location,
                'profile_photo' => $doctor->profile_photo,
                'bio' => $doctor->bio,
                'rating' => (float) $doctor->rating,
                'reviews_count' => $doctor->reviews_count,
                'social_links' => array_merge(
                    ['facebook' => '', 'linkedin' => '', 'twitter' => '', 'youtube' => ''],
                    (array) ($doctor->social_links ?? []),
                ),
                'is_featured' => (bool) $doctor->is_featured,
                'is_active' => (bool) $doctor->is_active,
                'expertises' => $doctor->expertises
                    ->map(fn ($expertise): array => [
                        'title' => $expertise->title,
                        'description' => $expertise->description,
                        'icon' => $expertise->icon,
                        'sort_order' => $expertise->sort_order,
                    ])
                    ->values()
                    ->all(),
                'schedules' => $doctor->schedules
                    ->map(fn ($schedule): array => [
                        'day_of_week' => $schedule->day_of_week,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'consultation_type' => $schedule->consultation_type ?? 'in_person',
                        'availability_status' => $schedule->availability_status ?? 'available',
                        'max_patients' => $schedule->max_patients ?? 20,
                        'is_active' => (bool) $schedule->is_active,
                    ])
                    ->values()
                    ->all(),
                'seo' => $this->seoPayload($doctor),
            ],
            'departmentOptions' => $this->service->departmentOptions(),
            'dayOptions' => $this->service->dayOptions(),
            'consultationTypeOptions' => $this->service->consultationTypeOptions(),
            'availabilityOptions' => $this->service->availabilityOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seoPayload(Doctor $doctor): array
    {
        $seo = $doctor->relationLoaded('seo') ? $doctor->seo : $doctor->seo()->first();

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
