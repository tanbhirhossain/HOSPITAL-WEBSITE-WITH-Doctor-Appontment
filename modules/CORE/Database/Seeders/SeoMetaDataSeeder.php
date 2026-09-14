<?php

namespace Modules\CORE\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\CORE\Models\SeoMetaData;
use Modules\DOCTOR\Models\Department;

class SeoMetaDataSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->with('category')->limit(6)->get();

        if ($departments->isEmpty()) {
            return;
        }

        foreach ($departments as $department) {
            SeoMetaData::query()->updateOrCreate(
                [
                    'seoable_type' => $department->getMorphClass(),
                    'seoable_id' => $department->getKey(),
                ],
                [
                    'meta_title' => $department->title.' | AMZ Hospital',
                    'meta_description' => str($department->short_description ?: 'Consult experienced specialists at AMZ Hospital.')
                        ->stripTags()
                        ->limit(150)
                        ->toString(),
                    'meta_keywords' => strtolower($department->title).', amz hospital, dhaka',
                    'canonical_url' => url('/departments/'.$department->slug),
                    'robots' => 'index, follow',
                    'og_title' => $department->title,
                    'og_description' => $department->short_description,
                    'og_type' => 'website',
                    'twitter_card' => 'summary_large_image',
                ],
            );
        }
    }
}
