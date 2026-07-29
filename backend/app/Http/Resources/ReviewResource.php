<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'application_id' => $this->application_id,
            'decision'       => $this->decision?->value,
            'decision_label' => $this->decisionLabel(),
            'notes'          => $this->notes,
            'reviewer'       => $this->whenLoaded('reviewer', fn () => [
                'id'   => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function decisionLabel(): string
    {
        return match ($this->decision?->value) {
            'revision_requested' => 'Butuh Revisi',
            'approved'           => 'Disetujui',
            'rejected'           => 'Ditolak',
            default              => '–',
        };
    }
}
