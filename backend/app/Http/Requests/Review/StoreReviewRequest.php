<?php

namespace App\Http\Requests\Review;

use App\Enums\ApplicationDecision;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Application $application */
        $application = $this->route('application');

        // Only reviewers can submit a review
        if (! $this->user()->hasRole('reviewer')) {
            return false;
        }

        // Application must be submitted or under_review
        return in_array($application->status, [
            ApplicationStatus::Submitted,
            ApplicationStatus::UnderReview,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ApplicationDecision::class)],
            'notes'    => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'decision.required' => 'Keputusan review wajib diisi.',
            'decision.enum'     => 'Keputusan harus salah satu dari: revision_requested, approved, rejected.',
            'notes.required'    => 'Catatan wajib diisi.',
            'notes.min'         => 'Catatan minimal 10 karakter.',
        ];
    }
}
