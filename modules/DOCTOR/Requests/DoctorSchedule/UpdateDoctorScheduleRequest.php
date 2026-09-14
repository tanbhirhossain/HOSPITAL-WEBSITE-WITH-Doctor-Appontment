<?php

namespace Modules\DOCTOR\Requests\DoctorSchedule;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\DOCTOR\Models\DoctorSchedule;

class UpdateDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $schedule = $this->route('schedule') ?? $this->route('doctorSchedule');

        return [
            'doctor_id' => ['required', 'integer', Rule::exists('doctors', 'id')],
            'day_of_week' => ['required', Rule::in(DoctorSchedule::DAYS)],
            'start_time' => [
                'required',
                'date_format:H:i',
                Rule::unique('doctor_schedules', 'start_time')
                    ->where(
                        fn ($query) => $query
                            ->where('doctor_id', $this->input('doctor_id'))
                            ->where('day_of_week', $this->input('day_of_week')),
                    )
                    ->ignore(is_object($schedule) ? $schedule->id : $schedule),
            ],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'consultation_type' => ['nullable', Rule::in(DoctorSchedule::CONSULTATION_TYPES)],
            'availability_status' => ['nullable', Rule::in(DoctorSchedule::AVAILABILITY_STATUSES)],
            'max_patients' => ['nullable', 'integer', 'min:1', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_time.unique' => 'This doctor already has a slot starting at that time on the selected day.',
            'end_time.after' => 'The end time must be after the start time.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'doctor_id' => 'doctor',
            'day_of_week' => 'day',
            'max_patients' => 'patient limit',
        ];
    }
}
