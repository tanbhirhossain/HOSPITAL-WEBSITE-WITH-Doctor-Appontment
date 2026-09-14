<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Repositories\SeoMetaDataRepository;
use Modules\CORE\Support\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DOCTOR\Interfaces\DoctorRepositoryInterface;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorSchedule;

class DoctorService extends BaseService
{
    public function __construct(
        DoctorRepositoryInterface $repository,
        private readonly SeoMetaDataRepository $seo,
    ) {
        parent::__construct($repository);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $expertises
     */
    public function createWithExpertises(array $data, array $expertises = []): Doctor
    {
        /** @var Doctor $doctor */
        $doctor = $this->create($data);

        if ($expertises !== []) {
            $this->repository->syncExpertises($doctor, $expertises);
        }

        return $doctor->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $expertises
     */
    public function updateWithExpertises(Doctor $doctor, array $data, array $expertises = []): Doctor
    {
        $this->update($doctor, $data);

        $this->repository->syncExpertises($doctor->refresh(), $expertises);

        return $doctor->refresh();
    }

    /**
     * Persist a doctor together with every section of its form: the profile,
     * the weekly timetable, expertises and the SEO row.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $expertises
     * @param  array<int, array<string, mixed>>  $schedules
     * @param  array<string, mixed>  $seoData
     */
    public function saveWithRelations(
        ?Doctor $doctor,
        array $data,
        array $expertises = [],
        array $schedules = [],
        array $seoData = [],
    ): Doctor {
        /** @var Doctor $doctor */
        $doctor = $doctor === null ? $this->create($data) : tap($doctor, fn () => $this->update($doctor, $data));

        $doctor = $doctor->refresh();

        return DB::transaction(function () use ($doctor, $expertises, $schedules, $seoData): Doctor {
            $this->repository->syncExpertises($doctor, $expertises);
            $this->repository->syncSchedules($doctor, $schedules);

            if ($seoData !== []) {
                $this->seo->syncFor($doctor, $seoData);
            }

            return $doctor->refresh();
        });
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function dayOptions(): array
    {
        return collect(DoctorSchedule::DAYS)
            ->map(fn (string $day): array => ['value' => $day, 'label' => $day])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function consultationTypeOptions(): array
    {
        return collect(DoctorSchedule::CONSULTATION_TYPES)
            ->map(fn (string $type): array => ['value' => $type, 'label' => Str::headline($type)])
            ->all();
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function availabilityOptions(): array
    {
        return collect(DoctorSchedule::AVAILABILITY_STATUSES)
            ->map(fn (string $status): array => ['value' => $status, 'label' => Str::headline($status)])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function departmentOptions(): array
    {
        return $this->repository->allDepartmentOptions();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        if (isset($data['name']) && blank($data['slug'] ?? null)) {
            $data['slug'] = Str::slug($data['name']);
        }

        if (array_key_exists('social_links', $data)) {
            $data['social_links'] = $this->normaliseSocialLinks($data['social_links']);
        }

        foreach (['is_featured', 'is_active'] as $flag) {
            if (array_key_exists($flag, $data)) {
                $data[$flag] = filter_var($data[$flag], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (array_key_exists('rating', $data) && ($data['rating'] === null || $data['rating'] === '')) {
            $data['rating'] = 5.00;
        }

        return $data;
    }

    /**
     * @return array<string, string>|null
     */
    private function normaliseSocialLinks(mixed $links): ?array
    {
        if (blank($links)) {
            return null;
        }

        $links = is_array($links) ? $links : [];

        $clean = collect($links)
            ->map(fn (mixed $url): string => trim((string) $url))
            ->filter()
            ->all();

        return $clean === [] ? null : $clean;
    }
}
