<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Support\Services\BaseService;
use InvalidArgumentException;
use Modules\DOCTOR\Interfaces\DoctorScheduleRepositoryInterface;

class DoctorScheduleService extends BaseService
{
    public function __construct(DoctorScheduleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return array<string, string>
     */
    public function doctorOptions(): array
    {
        return $this->repository->doctorOptions();
    }

    /**
     * @return array<string, string>
     */
    public function dayOptions(): array
    {
        return $this->repository->dayOptions();
    }

    /**
     * @return array<string, string>
     */
    public function consultationTypeOptions(): array
    {
        return $this->repository->consultationTypeOptions();
    }

    /**
     * @return array<string, string>
     */
    public function availabilityOptions(): array
    {
        return $this->repository->availabilityOptions();
    }


    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        if (isset($data['start_time'], $data['end_time']) && $data['end_time'] <= $data['start_time']) {
            throw new InvalidArgumentException('The end time must be after the start time.');
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        return $data;
    }
}
