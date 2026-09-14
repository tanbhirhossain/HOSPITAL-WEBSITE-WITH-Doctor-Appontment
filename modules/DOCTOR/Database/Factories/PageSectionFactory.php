<?php

namespace Modules\DOCTOR\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\DOCTOR\Models\PageSection;

/**
 * @extends Factory<PageSection>
 */
class PageSectionFactory extends Factory
{
    protected $model = PageSection::class;

    public function definition(): array
    {
        return [
            'section_key' => fake()->unique()->slug(2, '_'),
            'badge' => strtoupper(fake()->words(2, true)),
            'title' => fake()->sentence(6),
            'subtitle' => fake()->sentence(14),
            'primary_button_text' => 'Book Appointment',
            'primary_button_url' => '/appointment',
            'secondary_button_text' => 'Find A Doctor',
            'secondary_button_url' => '/find-doctor',
            'image' => null,
            'is_active' => true,
        ];
    }
}
