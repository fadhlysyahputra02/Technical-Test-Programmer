<?php

namespace App\Http\Requests\Application;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Application::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where('applicant_id', $this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.exists' => 'Project tidak ditemukan atau bukan milik Anda.',
        ];
    }
}
