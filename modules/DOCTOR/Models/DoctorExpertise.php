<?php

namespace Modules\DOCTOR\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\DOCTOR\Database\Factories\DoctorExpertiseFactory;

/**
 * A single focus area ("Chest Pain", "Asthma", ...) listed on a doctor profile.
 */
class DoctorExpertise extends Model
{
    /** @use HasFactory<DoctorExpertiseFactory> */
    use HasFactory;

    protected $table = 'doctor_expertises';

    protected $fillable = [
        'doctor_id',
        'title',
        'description',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Doctor, $this>
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    protected static function newFactory(): Factory
    {
        return DoctorExpertiseFactory::new();
    }
}
