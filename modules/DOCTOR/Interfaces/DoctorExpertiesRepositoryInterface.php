<?php

namespace Modules\DOCTOR\Interfaces;

use Modules\CORE\Support\Contracts\DataTableRepositoryInterface;

/**
 * NOTE: the interface name preserves the spelling used when the module was
 * scaffolded (`Experties`). It maps to the `DoctorExpertise` model and the
 * `doctor_expertises` table.
 */
interface DoctorExpertiesRepositoryInterface extends DataTableRepositoryInterface
{
    /**
     * @return array<string, string>
     */
    public function doctorOptions(): array;
}
