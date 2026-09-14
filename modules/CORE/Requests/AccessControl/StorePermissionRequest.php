<?php

namespace Modules\CORE\Requests\AccessControl;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
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
        return [
            'name' => [
                'required',
                'string',
                'max:120',
                // Convention: `resource.action`, e.g. `doctor.create`.
                'regex:/^[a-z][a-z0-9-]*(?:\.[a-z][a-z0-9-]*)+$/',
                Rule::unique('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'web')),
            ],
            'guard_name' => ['nullable', 'string', Rule::in(['web'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Use the resource.action format, for example "doctor.create".',
            'name.unique' => 'That permission already exists.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['name'] = str($data['name'])->lower()->toString();
        $data['guard_name'] = $data['guard_name'] ?? 'web';

        return $data;
    }
}
