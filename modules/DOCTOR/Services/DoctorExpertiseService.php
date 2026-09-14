<?php

namespace Modules\DOCTOR\Services;

use Modules\CORE\Support\Services\BaseService;
use Modules\DOCTOR\Interfaces\DoctorExpertiesRepositoryInterface;

/**
 * NOTE: named for the model (`DoctorExpertise`) while honouring the
 * pre-existing `DoctorExpertiesRepositoryInterface` contract.
 */
class DoctorExpertiseService extends BaseService
{
    public function __construct(DoctorExpertiesRepositoryInterface $repository)
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
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data): array
    {
        foreach (['title', 'description', 'icon'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        return $data;
    }
}
