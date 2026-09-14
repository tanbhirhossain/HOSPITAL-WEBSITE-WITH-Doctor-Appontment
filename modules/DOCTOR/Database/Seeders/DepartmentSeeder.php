<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\DepartmentCategory;

class DepartmentSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, category: string, description: string, popular: bool, featured: bool}>
     */
    public const DEPARTMENTS = [
        ['title' => 'Cardiology', 'category' => 'Medicine', 'description' => 'Comprehensive heart care, from preventive screening to advanced interventional procedures.', 'popular' => true, 'featured' => true],
        ['title' => 'Neurology', 'category' => 'Medicine', 'description' => 'Diagnosis and treatment of disorders affecting the brain, spine and nervous system.', 'popular' => true, 'featured' => true],
        ['title' => 'Gastroenterology', 'category' => 'Medicine', 'description' => 'Specialised care for digestive, liver and pancreatic conditions.', 'popular' => false, 'featured' => false],
        ['title' => 'Pulmonology', 'category' => 'Medicine', 'description' => 'Respiratory medicine covering asthma, COPD and sleep-related breathing disorders.', 'popular' => true, 'featured' => false],
        ['title' => 'Nephrology', 'category' => 'Medicine', 'description' => 'Kidney care including dialysis and transplant follow-up.', 'popular' => false, 'featured' => false],
        ['title' => 'Endocrinology', 'category' => 'Medicine', 'description' => 'Diabetes, thyroid and hormonal disorder management.', 'popular' => false, 'featured' => false],
        ['title' => 'General Surgery', 'category' => 'Surgery', 'description' => 'Laparoscopic and open surgical procedures performed by an experienced team.', 'popular' => true, 'featured' => true],
        ['title' => 'Orthopaedics', 'category' => 'Surgery', 'description' => 'Joint replacement, sports injury and trauma surgery.', 'popular' => true, 'featured' => true],
        ['title' => 'Urology', 'category' => 'Surgery', 'description' => 'Urinary tract and male reproductive health, including stone disease.', 'popular' => false, 'featured' => false],
        ['title' => 'Gynaecology & Obstetrics', 'category' => 'Mother & Child', 'description' => 'Complete women’s health services from antenatal care to advanced gynaecological surgery.', 'popular' => true, 'featured' => true],
        ['title' => 'Paediatrics', 'category' => 'Mother & Child', 'description' => 'Newborn, infant and adolescent care delivered by dedicated child specialists.', 'popular' => true, 'featured' => false],
        ['title' => 'Neonatology', 'category' => 'Mother & Child', 'description' => 'Level III neonatal intensive care for premature and critically ill newborns.', 'popular' => false, 'featured' => false],
        ['title' => 'Radiology & Imaging', 'category' => 'Diagnostics', 'description' => 'MRI, CT, ultrasound and digital X-ray reported by consultant radiologists.', 'popular' => false, 'featured' => false],
        ['title' => 'Pathology', 'category' => 'Diagnostics', 'description' => 'Accredited laboratory services with home sample collection across the city.', 'popular' => true, 'featured' => false],
        ['title' => 'ENT', 'category' => 'Dental & ENT', 'description' => 'Ear, nose and throat care including endoscopic sinus and hearing surgery.', 'popular' => false, 'featured' => false],
        ['title' => 'Dentistry', 'category' => 'Dental & ENT', 'description' => 'General dentistry, implants and cosmetic smile design.', 'popular' => false, 'featured' => false],
    ];

    public function run(): void
    {
        foreach (self::DEPARTMENTS as $index => $department) {
            $category = DepartmentCategory::query()->where('slug', Str::slug($department['category']))->first();

            Department::query()->updateOrCreate(
                ['slug' => Str::slug($department['title'])],
                [
                    'department_category_id' => $category?->id,
                    'title' => $department['title'],
                    'short_description' => $department['description'],
                    'is_popular_search' => $department['popular'],
                    'is_featured' => $department['featured'],
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );
        }
    }
}
