<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\DOCTOR\Models\Department;
use Modules\DOCTOR\Models\Doctor;

class DoctorSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public const DOCTORS = [
        ['name' => 'Dr. Ahsanul Kabir', 'department' => 'Cardiology', 'designation' => 'Senior Consultant', 'specialty' => 'Interventional Cardiology', 'qualification' => 'MBBS, MD (Cardiology), FACC', 'years' => 22, 'featured' => true],
        ['name' => 'Dr. Nasreen Akter', 'department' => 'Cardiology', 'designation' => 'Consultant', 'specialty' => 'Echocardiography', 'qualification' => 'MBBS, FCPS (Cardiology)', 'years' => 14, 'featured' => false],
        ['name' => 'Dr. Mahfuzur Rahman', 'department' => 'Neurology', 'designation' => 'Professor', 'specialty' => 'Stroke & Epilepsy', 'qualification' => 'MBBS, FCPS (Medicine), MD (Neurology)', 'years' => 26, 'featured' => true],
        ['name' => 'Dr. Sharmin Sultana', 'department' => 'Neurology', 'designation' => 'Consultant', 'specialty' => 'Neuromuscular Disorders', 'qualification' => 'MBBS, MD (Neurology)', 'years' => 12, 'featured' => false],
        ['name' => 'Dr. Tanvir Hossain', 'department' => 'Gastroenterology', 'designation' => 'Consultant', 'specialty' => 'Hepatology', 'qualification' => 'MBBS, MD (Gastroenterology)', 'years' => 16, 'featured' => false],
        ['name' => 'Dr. Ferdousi Begum', 'department' => 'Pulmonology', 'designation' => 'Senior Consultant', 'specialty' => 'Chest & Sleep Medicine', 'qualification' => 'MBBS, DTCD, FCPS (Pulmonology)', 'years' => 19, 'featured' => true],
        ['name' => 'Dr. Imran Chowdhury', 'department' => 'Nephrology', 'designation' => 'Consultant', 'specialty' => 'Dialysis & Transplant', 'qualification' => 'MBBS, MD (Nephrology)', 'years' => 13, 'featured' => false],
        ['name' => 'Dr. Rownak Jahan', 'department' => 'Endocrinology', 'designation' => 'Consultant', 'specialty' => 'Diabetes & Thyroid', 'qualification' => 'MBBS, FCPS (Endocrinology)', 'years' => 11, 'featured' => false],
        ['name' => 'Dr. Kamal Hossain', 'department' => 'General Surgery', 'designation' => 'Senior Consultant', 'specialty' => 'Laparoscopic Surgery', 'qualification' => 'MBBS, FCPS (Surgery), MRCS', 'years' => 24, 'featured' => true],
        ['name' => 'Dr. Sabrina Islam', 'department' => 'General Surgery', 'designation' => 'Consultant', 'specialty' => 'Breast & Endocrine Surgery', 'qualification' => 'MBBS, MS (Surgery)', 'years' => 15, 'featured' => false],
        ['name' => 'Dr. Rezaul Karim', 'department' => 'Orthopaedics', 'designation' => 'Professor', 'specialty' => 'Joint Replacement', 'qualification' => 'MBBS, MS (Ortho), FRCS', 'years' => 28, 'featured' => true],
        ['name' => 'Dr. Adnan Rahman', 'department' => 'Orthopaedics', 'designation' => 'Consultant', 'specialty' => 'Sports Injury', 'qualification' => 'MBBS, MS (Ortho)', 'years' => 10, 'featured' => false],
        ['name' => 'Dr. Shahidul Islam', 'department' => 'Urology', 'designation' => 'Consultant', 'specialty' => 'Endourology', 'qualification' => 'MBBS, MS (Urology)', 'years' => 17, 'featured' => false],
        ['name' => 'Dr. Tahmina Ahmed', 'department' => 'Gynaecology & Obstetrics', 'designation' => 'Senior Consultant', 'specialty' => 'High Risk Pregnancy', 'qualification' => 'MBBS, FCPS (Gynae & Obs)', 'years' => 21, 'featured' => true],
        ['name' => 'Dr. Farhana Yasmin', 'department' => 'Gynaecology & Obstetrics', 'designation' => 'Consultant', 'specialty' => 'Gynaecological Laparoscopy', 'qualification' => 'MBBS, MCPS, FCPS', 'years' => 13, 'featured' => false],
        ['name' => 'Dr. Mizanur Rahman', 'department' => 'Paediatrics', 'designation' => 'Professor', 'specialty' => 'Paediatric Medicine', 'qualification' => 'MBBS, FCPS (Paediatrics)', 'years' => 25, 'featured' => true],
        ['name' => 'Dr. Sadia Rahman', 'department' => 'Paediatrics', 'designation' => 'Consultant', 'specialty' => 'Paediatric Nutrition', 'qualification' => 'MBBS, MD (Paediatrics)', 'years' => 9, 'featured' => false],
        ['name' => 'Dr. Nusrat Jahan', 'department' => 'Neonatology', 'designation' => 'Consultant', 'specialty' => 'Neonatal Intensive Care', 'qualification' => 'MBBS, MD (Neonatology)', 'years' => 12, 'featured' => false],
        ['name' => 'Dr. Ashrafuzzaman', 'department' => 'Radiology & Imaging', 'designation' => 'Consultant Radiologist', 'specialty' => 'Cross-sectional Imaging', 'qualification' => 'MBBS, MD (Radiology)', 'years' => 18, 'featured' => false],
        ['name' => 'Dr. Sultana Razia', 'department' => 'Pathology', 'designation' => 'Consultant Pathologist', 'specialty' => 'Histopathology', 'qualification' => 'MBBS, M.Phil (Pathology)', 'years' => 16, 'featured' => false],
        ['name' => 'Dr. Mahbub Alam', 'department' => 'ENT', 'designation' => 'Senior Consultant', 'specialty' => 'Endoscopic Sinus Surgery', 'qualification' => 'MBBS, FCPS (ENT)', 'years' => 20, 'featured' => false],
        ['name' => 'Dr. Rifat Hasan', 'department' => 'Dentistry', 'designation' => 'Consultant', 'specialty' => 'Implantology', 'qualification' => 'BDS, MDS', 'years' => 11, 'featured' => false],
    ];

    public function run(): void
    {
        foreach (self::DOCTORS as $index => $doctor) {
            $department = Department::query()->where('slug', Str::slug($doctor['department']))->first();

            if ($department === null) {
                continue;
            }

            Doctor::query()->updateOrCreate(
                ['slug' => Str::slug($doctor['name'])],
                [
                    'department_id' => $department->id,
                    'name' => $doctor['name'],
                    'designation' => $doctor['designation'],
                    'specialty' => $doctor['specialty'],
                    'qualification' => $doctor['qualification'],
                    'experience' => $doctor['years'].' years',
                    'experience_years' => $doctor['years'],
                    'hospital_name' => 'AMZ Hospital Ltd.',
                    'location' => 'AMZ Hospital, Dhaka',
                    'profile_photo' => null,
                    'bio' => sprintf(
                        '%s is a %s in the Department of %s at AMZ Hospital Ltd., with %d years of clinical experience in %s.',
                        $doctor['name'],
                        strtolower($doctor['designation']),
                        $doctor['department'],
                        $doctor['years'],
                        strtolower($doctor['specialty']),
                    ),
                    'rating' => fake()->randomFloat(2, 4.2, 5),
                    'reviews_count' => fake()->numberBetween(40, 620),
                    'social_links' => null,
                    'is_featured' => $doctor['featured'],
                    'is_active' => true,
                ],
            );
        }

        unset($index);
    }
}
