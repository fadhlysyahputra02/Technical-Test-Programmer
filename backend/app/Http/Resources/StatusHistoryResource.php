<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'from_status'  => $this->from_status?->value,
            'from_label'   => $this->from_status?->label(),
            'to_status'    => $this->to_status?->value,
            'to_label'     => $this->to_status?->label(),
            'notes'        => $this->notes,
            'changed_by'   => $this->whenLoaded('changedBy', fn () => [
                'id'   => $this->changedBy->id,
                'name' => $this->changedBy->name,
            ]),
            'created_at'   => $this->created_at?->toISOString(),
        ];
    }
}
