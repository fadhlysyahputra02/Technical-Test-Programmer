<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\UploadDocumentRequest;
use App\Http\Resources\ApplicationDocumentResource;
use App\Models\Application;
use App\Models\ApplicationDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApplicationDocumentController extends Controller
{
    /**
     * POST /api/applications/{application}/documents
     * Upload a document to the given application.
     * Authorization: owner + status is draft or revision_requested (UploadDocumentRequest).
     */
    public function store(UploadDocumentRequest $request, Application $application): JsonResponse
    {
        $file = $request->file('file');
        $timestamp = now()->format('YmdHis');
        $originalName = $file->getClientOriginalName();
        $storedName = "{$timestamp}_{$originalName}";
        $storagePath = "documents/{$application->id}";

        $document = DB::transaction(function () use ($file, $application, $request, $storagePath, $storedName, $originalName) {
            // Store file to storage/app/private/documents/{application_id}/
            $file->storeAs($storagePath, $storedName, 'local');

            return ApplicationDocument::create([
                'application_id' => $application->id,
                'file_name'      => $originalName,
                'file_path'      => "{$storagePath}/{$storedName}",
                'file_type'      => $file->getClientMimeType(),
                'file_size'      => $file->getSize(),
                'uploaded_by'    => $request->user()->id,
            ]);
        });

        return response()->json([
            'message' => 'Dokumen berhasil diunggah.',
            'data'    => new ApplicationDocumentResource($document->load('uploader:id,name')),
        ], 201);
    }

    /**
     * GET /api/applications/{application}/documents
     * List all documents for the given application.
     * Authorization: owner or reviewer.
     */
    public function index(Request $request, Application $application): AnonymousResourceCollection
    {
        $this->authorizeViewAccess($request, $application);

        $documents = $application->documents()
            ->select(['id', 'application_id', 'file_name', 'file_type', 'file_size', 'uploaded_by', 'created_at'])
            ->with('uploader:id,name')
            ->latest()
            ->get();

        return ApplicationDocumentResource::collection($documents);
    }

    /**
     * DELETE /api/applications/{application}/documents/{document}
     * Delete a document. Only allowed if the application is still draft or revision_requested.
     * Deletes both the physical file and the database record.
     */
    public function destroy(Request $request, Application $application, ApplicationDocument $document): JsonResponse
    {
        // Ensure the document belongs to this application
        abort_if($document->application_id !== $application->id, 404);

        // Only the owner can delete
        abort_if($request->user()->id !== $application->applicant_id, 403, 'Anda bukan pemilik permohonan ini.');

        // Only deletable in draft or revision_requested
        abort_unless(
            in_array($application->status, [ApplicationStatus::Draft, ApplicationStatus::RevisionRequested]),
            403,
            'Dokumen hanya dapat dihapus ketika permohonan berstatus draft atau revisi.'
        );

        DB::transaction(function () use ($document) {
            // Delete physical file from storage
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            $document->delete();
        });

        return response()->json([
            'message' => 'Dokumen berhasil dihapus.',
        ]);
    }

    /**
     * GET /api/documents/{document}/download
     * Download a document file using its original file name.
     * Authorization: owner of the application or any reviewer.
     */
    public function download(Request $request, ApplicationDocument $document): BinaryFileResponse
    {
        $application = $document->application;

        $this->authorizeViewAccess($request, $application);

        $fullPath = Storage::disk('local')->path($document->file_path);

        if (! Storage::disk('local')->exists($document->file_path)) {
            throw new NotFoundHttpException('File tidak ditemukan di server.');
        }

        return response()->download($fullPath, $document->file_name);
    }

    /**
     * Shared authorization: owner or reviewer can view/download.
     */
    private function authorizeViewAccess(Request $request, Application $application): void
    {
        $user = $request->user();

        abort_unless(
            $user->id === $application->applicant_id || $user->hasRole('reviewer'),
            403,
            'Anda tidak memiliki akses ke dokumen permohonan ini.'
        );
    }
}
