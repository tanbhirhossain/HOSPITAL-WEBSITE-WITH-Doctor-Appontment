<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DOCTOR\Models\Doctor;
use Modules\DOCTOR\Models\DoctorExpertise;

class DoctorExpertiseSeeder extends Seeder
{
    /**
     * Focus areas keyed by the doctor's specialty, so seeded profiles read
     * like real hospital content rather than lorem ipsum.
     *
     * @var array<string, array<int, array{title: string, description: string}>>
     */
    public const BY_SPECIALTY = [
        'Interventional Cardiology' => [
            ['title' => 'Angioplasty & Stenting', 'description' => 'Minimally invasive opening of blocked coronary arteries.'],
            ['title' => 'Heart Failure Clinic', 'description' => 'Long-term management of reduced cardiac function.'],
            ['title' => 'Arrhythmia Care', 'description' => 'Diagnosis and rhythm control for irregular heartbeats.'],
        ],
        'Echocardiography' => [
            ['title' => 'Stress Echocardiography', 'description' => 'Exercise-based assessment of coronary blood flow.'],
            ['title' => 'Valve Assessment', 'description' => 'Detailed imaging of mitral and aortic valve disease.'],
        ],
        'Stroke & Epilepsy' => [
            ['title' => 'Acute Stroke Thrombolysis', 'description' => 'Time-critical clot-busting treatment for ischaemic stroke.'],
            ['title' => 'Epilepsy Monitoring', 'description' => 'Video EEG and long-term seizure management.'],
        ],
        'Neuromuscular Disorders' => [
            ['title' => 'EMG & Nerve Studies', 'description' => 'Electrodiagnostic testing for nerve and muscle disease.'],
            ['title' => 'Neuropathy Clinic', 'description' => 'Assessment and treatment of peripheral neuropathy.'],
        ],
        'Hepatology' => [
            ['title' => 'Endoscopy & Colonoscopy', 'description' => 'Diagnostic and therapeutic gastrointestinal endoscopy.'],
            ['title' => 'Liver Disease Clinic', 'description' => 'Management of hepatitis, fatty liver and cirrhosis.'],
        ],
        'Chest & Sleep Medicine' => [
            ['title' => 'Asthma & COPD', 'description' => 'Inhaler technique, pulmonary rehab and exacerbation prevention.'],
            ['title' => 'Sleep Apnoea', 'description' => 'Overnight oximetry and CPAP titration.'],
        ],
        'Dialysis & Transplant' => [
            ['title' => 'Haemodialysis', 'description' => 'In-centre dialysis with a dedicated nursing team.'],
            ['title' => 'Transplant Follow-up', 'description' => 'Long-term care after kidney transplantation.'],
        ],
        'Diabetes & Thyroid' => [
            ['title' => 'Insulin Pump Therapy', 'description' => 'Advanced insulin delivery for brittle diabetes.'],
            ['title' => 'Thyroid Nodule Clinic', 'description' => 'Ultrasound-guided assessment of thyroid lumps.'],
        ],
        'Laparoscopic Surgery' => [
            ['title' => 'Gallbladder Surgery', 'description' => 'Keyhole removal of the gallbladder with same-week recovery.'],
            ['title' => 'Hernia Repair', 'description' => 'Mesh-based repair of inguinal and ventral hernias.'],
        ],
        'Breast & Endocrine Surgery' => [
            ['title' => 'Breast Lump Clinic', 'description' => 'Triple assessment of breast lumps in a single visit.'],
            ['title' => 'Thyroid Surgery', 'description' => 'Surgery for goitre, nodules and thyroid cancer.'],
        ],
        'Joint Replacement' => [
            ['title' => 'Knee Replacement', 'description' => 'Total and partial knee arthroplasty with rapid recovery pathways.'],
            ['title' => 'Hip Replacement', 'description' => 'Anterior approach hip replacement for faster rehabilitation.'],
        ],
        'Sports Injury' => [
            ['title' => 'ACL Reconstruction', 'description' => 'Arthroscopic ligament reconstruction for athletes.'],
            ['title' => 'Shoulder Arthroscopy', 'description' => 'Keyhole treatment of rotator cuff tears.'],
        ],
        'Endourology' => [
            ['title' => 'Kidney Stone Surgery', 'description' => 'Laser lithotripsy and ureteroscopy for stone disease.'],
            ['title' => 'Prostate Care', 'description' => 'Assessment and treatment of prostate enlargement.'],
        ],
        'High Risk Pregnancy' => [
            ['title' => 'Antenatal Care', 'description' => 'Structured monitoring for complicated pregnancies.'],
            ['title' => 'Foetal Medicine', 'description' => 'Detailed ultrasound and growth surveillance.'],
        ],
        'Gynaecological Laparoscopy' => [
            ['title' => 'Fibroid Surgery', 'description' => 'Keyhole myomectomy preserving the uterus.'],
            ['title' => 'Endometriosis Care', 'description' => 'Laparoscopic treatment of pelvic endometriosis.'],
        ],
        'Paediatric Medicine' => [
            ['title' => 'Childhood Infections', 'description' => 'Management of fever, respiratory and gut infections.'],
            ['title' => 'Growth & Development', 'description' => 'Monitoring milestones and nutritional status.'],
        ],
        'Paediatric Nutrition' => [
            ['title' => 'Infant Feeding', 'description' => 'Support for breastfeeding and weaning.'],
            ['title' => 'Childhood Obesity', 'description' => 'Family-based weight management programmes.'],
        ],
        'Neonatal Intensive Care' => [
            ['title' => 'Preterm Care', 'description' => 'Specialist care for babies born before 34 weeks.'],
            ['title' => 'Neonatal Jaundice', 'description' => 'Phototherapy and exchange transfusion services.'],
        ],
        'Cross-sectional Imaging' => [
            ['title' => 'MRI Reporting', 'description' => 'Subspecialty reporting of neuro and musculoskeletal MRI.'],
            ['title' => 'CT Angiography', 'description' => 'Non-invasive vascular imaging.'],
        ],
        'Histopathology' => [
            ['title' => 'Cancer Reporting', 'description' => 'Histological diagnosis and tumour grading.'],
            ['title' => 'Frozen Section', 'description' => 'Rapid intra-operative diagnosis.'],
        ],
        'Endoscopic Sinus Surgery' => [
            ['title' => 'Sinus Surgery', 'description' => 'Image-guided surgery for chronic sinusitis.'],
            ['title' => 'Hearing Assessment', 'description' => 'Audiometry and hearing aid counselling.'],
        ],
        'Implantology' => [
            ['title' => 'Dental Implants', 'description' => 'Single-tooth and full-arch implant rehabilitation.'],
            ['title' => 'Cosmetic Dentistry', 'description' => 'Veneers and professional smile design.'],
        ],
    ];

    public function run(): void
    {
        $doctors = Doctor::query()->get();

        foreach ($doctors as $doctor) {
            $rows = self::BY_SPECIALTY[$doctor->specialty] ?? [
                ['title' => $doctor->specialty, 'description' => 'Specialist consultation and follow-up care.'],
            ];

            foreach (array_values($rows) as $index => $row) {
                DoctorExpertise::query()->updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'title' => $row['title'],
                    ],
                    [
                        'description' => $row['description'],
                        'sort_order' => $index,
                    ],
                );
            }
        }
    }
}
