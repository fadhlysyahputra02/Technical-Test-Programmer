<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationDocumentResource extends JsonResource
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
            'file_name'      => $this->file_name,
            'file_type'      => $this->file_type,
            'file_size'      => $this->file_size,
            'file_size_label' => $this->formatFileSize($this->file_size),
            'download_url'   => route('documents.download', $this->id),
            'uploaded_by'    => $this->whenLoaded('uploader', fn () => [
                'id'   => $this->uploader->id,
                'name' => $this->uploader->name,
            ]),
            'created_at'     => $this->created_at?->toISOString(),
        ];
    }

    /**
     * Format file size into a human-readable string.
     */
    private function formatFileSize(?int $bytes): string
    {
        if ($bytes === null) {
            return '–';
        }

        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / 1048576, 2) . ' MB';
    }
}
