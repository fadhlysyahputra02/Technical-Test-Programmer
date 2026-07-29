<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'application_number' => $this->application_number,
            'status'             => $this->status,
            'status_label'       => $this->status?->label(),
            'version'            => $this->version,
            'notes'              => $this->notes,
            'submitted_at'       => $this->submitted_at?->toISOString(),
            'approved_at'        => $this->approved_at?->toISOString(),
            'rejected_at'        => $this->rejected_at?->toISOString(),
            'created_at'         => $this->created_at?->toISOString(),
            'updated_at'         => $this->updated_at?->toISOString(),

            // Relationships (conditionally loaded)
            'project' => $this->whenLoaded('project', fn () => [
                'id'     => $this->project->id,
                'name'   => $this->project->name,
                'status' => $this->project->status,
            ]),

            'applicant' => $this->whenLoaded('applicant', fn () => [
                'id'   => $this->applicant->id,
                'name' => $this->applicant->name,
            ]),

            'latest_reviewer' => $this->whenLoaded('latestReviewer', fn () => $this->latestReviewer ? [
                'id'   => $this->latestReviewer->id,
                'name' => $this->latestReviewer->name,
            ] : null),

            'documents' => $this->whenLoaded('documents', fn () =>
                $this->documents->map(fn ($doc) => [
                    'id'          => $doc->id,
                    'file_name'   => $doc->file_name,
                    'file_type'   => $doc->file_type,
                    'file_size'   => $doc->file_size,
                    'file_path'   => $doc->file_path,
                    'uploaded_by' => $doc->uploaded_by,
                    'created_at'  => $doc->created_at?->toISOString(),
                ])
            ),

            'reviews' => $this->whenLoaded('reviews', fn () =>
                $this->reviews->map(fn ($review) => [
                    'id'         => $review->id,
                    'decision'   => $review->decision,
                    'notes'      => $review->notes,
                    'reviewer'   => [
                        'id'   => $review->reviewer?->id,
                        'name' => $review->reviewer?->name,
                    ],
                    'created_at' => $review->created_at?->toISOString(),
                ])
            ),

            'status_histories' => $this->whenLoaded('statusHistories', fn () =>
                $this->statusHistories->map(fn ($history) => [
                    'id'          => $history->id,
                    'from_status' => $history->from_status?->value,
                    'to_status'   => $history->to_status?->value,
                    'notes'       => $history->notes,
                    'changed_by'  => [
                        'id'   => $history->changedBy?->id,
                        'name' => $history->changedBy?->name,
                    ],
                    'created_at'  => $history->created_at?->toISOString(),
                ])
            ),
        ];
    }
}
