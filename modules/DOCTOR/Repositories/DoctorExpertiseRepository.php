<?php

namespace Modules\DOCTOR\Repositories;

use Modules\CORE\Support\Repositories\BaseRepository;
use Modules\DOCTOR\Interfaces\DoctorExpertiesRepositoryInterface;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorExpertise;

class DoctorExpertiseRepository extends BaseRepository implements DoctorExpertiesRepositoryInterface
{
    /** @var class-string<DoctorExpertise> */
    protected string $modelClass = DoctorExpertise::class;

    protected array $searchable = ['title', 'description', 'doctor.name'];

    protected array $sortable = ['id', 'title', 'sort_order', 'created_at', 'doctor.name'];

    protected array $filterable = [
        'doctor_id' => 'doctor_id',
    ];

    protected array $with = ['doctor'];

    public function doctorOptions(): array
    {
        return Doctor::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
