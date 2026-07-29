<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name'               => $this->name,
            'description'        => $this->description,
            'status'             => $this->status,
            'applicant_id'       => $this->applicant_id,
            'applicant'          => $this->whenLoaded('applicant', fn () => [
                'id'   => $this->applicant->id,
                'name' => $this->applicant->name,
            ]),
            'applications_count' => $this->when(
                $this->applications_count !== null,
                $this->applications_count
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
