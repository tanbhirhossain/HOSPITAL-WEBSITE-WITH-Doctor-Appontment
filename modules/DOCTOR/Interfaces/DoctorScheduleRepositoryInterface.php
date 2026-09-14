<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;

interface DoctorScheduleRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * @return array<string, string>
     */
    public function doctorOptions(): array;

    /**
     * @return array<string, string>
     */
    public function dayOptions(): array;

    /**
     * @return array<string, string>
     */
    public function consultationTypeOptions(): array;

    /**
     * @return array<string, string>
     */
    public function availabilityOptions(): array;
}
