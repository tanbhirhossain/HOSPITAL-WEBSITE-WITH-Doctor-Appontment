<?php

namespace Modules\DOCTOR\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\DOCTOR\Models\Symptom;

class SymptomSeeder extends Seeder
{
    /**
     * @var array<int, array{title: string, link_url: string}>
     */
    public const SYMPTOMS = [
        ['title' => 'Heart & Chest', 'link_url' => '/departments/cardiology'],
        ['title' => 'Brain & Nerve', 'link_url' => '/departments/neurology'],
        ['title' => 'Bones & Joints', 'link_url' => '/departments/orthopaedics'],
        ['title' => 'Stomach & Gut', 'link_url' => '/departments/gastroenterology'],
        ['title' => 'Breathing', 'link_url' => '/departments/pulmonology'],
        ['title' => 'Pregnancy & Baby', 'link_url' => '/departments/gynaecology-obstetrics'],
        ['title' => 'Child Health', 'link_url' => '/departments/paediatrics'],
        ['title' => 'Teeth & Gum', 'link_url' => '/departments/dentistry'],
    ];

    public function run(): void
    {
        foreach (array_values(self::SYMPTOMS) as $index => $symptom) {
            Symptom::query()->updateOrCreate(
                ['title' => $symptom['title']],
                [
                    'link_url' => $symptom['link_url'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
