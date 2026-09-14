<?php

namespace Modules\DOCTOR\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\CORE\Support\Http\Requests\Concerns\SeoRules;
use Modules\DOCTOR\Models\DoctorSchedule;

class StoreDoctorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    use SeoRules;

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('doctors', 'slug')],
            'designation' => ['nullable', 'string', 'max:150'],
            'specialty' => ['required', 'string', 'max:150'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string', 'max:120'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:70'],
            'hospital_name' => ['nullable', 'string', 'max:180'],
            'location' => ['nullable', 'string', 'max:180'],
            'profile_photo' => ['nullable', 'string', 'max:500'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'social_links' => ['nullable', 'array'],
            'social_links.*' => ['nullable', 'url', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'expertises' => ['nullable', 'array', 'max:20'],
            'expertises.*.title' => ['required_with:expertises', 'string', 'max:150'],
            'expertises.*.description' => ['nullable', 'string', 'max:500'],
            'expertises.*.icon' => ['nullable', 'string', 'max:120'],
            'expertises.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'schedules' => ['nullable', 'array', 'max:40'],
            'schedules.*.day_of_week' => ['required_with:schedules', Rule::in(DoctorSchedule::DAYS)],
            'schedules.*.start_time' => ['required_with:schedules', 'date_format:H:i'],
            'schedules.*.end_time' => ['required_with:schedules', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.consultation_type' => ['nullable', Rule::in(DoctorSchedule::CONSULTATION_TYPES)],
            'schedules.*.availability_status' => ['nullable', Rule::in(DoctorSchedule::AVAILABILITY_STATUSES)],
            'schedules.*.max_patients' => ['nullable', 'integer', 'min:1', 'max:500'],
            'schedules.*.is_active' => ['nullable', 'boolean'],
            ...$this->seoRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'department_id' => 'department',
            'experience_years' => 'years of experience',
            'social_links.*' => 'social link',
            'expertises.*.title' => 'expertise title',
        ];
    }
}
