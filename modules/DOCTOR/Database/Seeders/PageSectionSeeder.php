<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DOCTOR\Models\PageSection;

class PageSectionSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public const SECTIONS = [
        [
            'section_key' => 'hero',
            'badge' => 'AMZ HOSPITAL',
            'title' => 'Advanced care, delivered with a steady hand.',
            'subtitle' => 'Consultant-led treatment across 16 specialities, with diagnostics and pharmacy under one roof.',
            'primary_button_text' => 'Book An Appointment',
            'primary_button_url' => '/appointment',
            'secondary_button_text' => 'Find A Doctor',
            'secondary_button_url' => '/find-doctor',
            'image' => '/assets/images/hero-hospital.jpg',
        ],
        [
            'section_key' => 'what_brings_you_here',
            'badge' => 'WHAT BRINGS YOU HERE',
            'title' => 'Tell us how you feel, we will point you to the right care.',
            'subtitle' => 'Pick the area that matches your symptoms and we will match you with a specialist.',
            'primary_button_text' => 'Find A Doctor',
            'primary_button_url' => '/find-doctor',
            'secondary_button_text' => 'All Departments',
            'secondary_button_url' => '/departments',
            'image' => null,
        ],
        [
            'section_key' => 'departments',
            'badge' => 'AMZ MEDICAL DEPARTMENTS',
            'title' => 'Find the Right Department for Your Care.',
            'subtitle' => 'Sixteen consultant-led departments, each with its own dedicated team and day-case theatre.',
            'primary_button_text' => 'Browse Departments',
            'primary_button_url' => '/departments',
            'secondary_button_text' => null,
            'secondary_button_url' => null,
            'image' => null,
        ],
        [
            'section_key' => 'urgent_care',
            'badge' => 'URGENT CARE',
            'title' => 'Emergency care that starts the moment you arrive.',
            'subtitle' => 'A 24/7 emergency department with on-site imaging, laboratory and critical care support.',
            'primary_button_text' => 'Emergency Contact',
            'primary_button_url' => '/contact',
            'secondary_button_text' => null,
            'secondary_button_url' => null,
            'image' => null,
        ],
        [
            'section_key' => 'bottom_cta',
            'badge' => 'BOOK A CONSULTATION',
            'title' => 'Speak to a specialist today.',
            'subtitle' => 'Choose a time that suits you — in person or online.',
            'primary_button_text' => 'Book An Appointment',
            'primary_button_url' => '/appointment',
            'secondary_button_text' => 'Find A Doctor',
            'secondary_button_url' => '/find-doctor',
            'image' => null,
        ],
    ];

    public function run(): void
    {
        foreach (self::SECTIONS as $section) {
            PageSection::query()->updateOrCreate(
                ['section_key' => $section['section_key']],
                [...$section, 'is_active' => true],
            );
        }
    }
}
