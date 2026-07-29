<?php

namespace App\Http\Requests\Document;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Application $application */
        $application = $this->route('application');

        return $this->user()->id === $application->applicant_id
            && in_array($application->status, [
                ApplicationStatus::Draft,
                ApplicationStatus::RevisionRequested,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:10240', // 10 MB
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File dokumen wajib diunggah.',
            'file.mimes'    => 'Format file harus: PDF, DOC, DOCX, JPG, JPEG, atau PNG.',
            'file.max'      => 'Ukuran file tidak boleh melebihi 10 MB.',
        ];
    }
}
