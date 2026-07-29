<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Http\Requests\Application\UpdateApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    /**
     * GET /api/applications
     * List applications belonging to the authenticated applicant.
     * Filters: status, project_id, date_from, date_to
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Application::class);

        $applications = Application::select([
            'id', 'application_number', 'project_id', 'applicant_id',
            'status', 'submitted_at', 'approved_at', 'rejected_at',
            'latest_reviewer_id', 'version', 'created_at', 'updated_at',
        ])
            ->with([
                'project:id,name,status',
                'latestReviewer:id,name',
            ])
            ->where('applicant_id', $request->user()->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->latest()
            ->paginate(20);

        return ApplicationResource::collection($applications);
    }

    /**
     * POST /api/applications
     * Create a new application with status = draft.
     * Auto-generates a unique application_number.
     */
    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $application = DB::transaction(function () use ($request) {
            $number = $this->generateApplicationNumber();

            return Application::create([
                'application_number' => $number,
                'project_id'         => $request->project_id,
                'applicant_id'       => $request->user()->id,
                'status'             => ApplicationStatus::Draft->value,
                'version'            => 1,
            ]);
        });

        return response()->json([
            'message' => 'Permohonan berhasil dibuat.',
            'data'    => new ApplicationResource($application->load('project:id,name,status')),
        ], 201);
    }

    /**
     * GET /api/applications/{application}
     * Full detail with documents, reviews (with reviewer), statusHistories (with changedBy).
     * Accessible by the owner or any reviewer.
     */
    public function show(Application $application): JsonResponse
    {
        $this->authorize('view', $application);

        $application->load([
            'project:id,name,status',
            'applicant:id,name,email',
            'latestReviewer:id,name',
            'documents',
            'reviews.reviewer:id,name',
            'statusHistories.changedBy:id,name',
        ]);

        return response()->json([
            'data' => new ApplicationResource($application),
        ]);
    }

    /**
     * PUT /api/applications/{application}
     * Update notes. Only allowed when status is draft or revision_requested.
     * Authorization is handled by UpdateApplicationRequest (Policy).
     */
    public function update(UpdateApplicationRequest $request, Application $application): JsonResponse
    {
        $application->update($request->only(['notes']));

        return response()->json([
            'message' => 'Permohonan berhasil diperbarui.',
            'data'    => new ApplicationResource($application->fresh()),
        ]);
    }

    /**
     * POST /api/applications/{application}/submit
     * Submit an application (draft → submitted, or revision_requested → submitted).
     * Requirements: at least 1 document must be uploaded.
     * Increments version if re-submitting after revision.
     * Records status history. Wrapped in DB::transaction.
     */
    public function submit(Request $request, Application $application): JsonResponse
    {
        $this->authorize('submit', $application);

        // Must have at least 1 document
        if ($application->documents()->count() === 0) {
            throw ValidationException::withMessages([
                'documents' => ['Permohonan harus memiliki minimal 1 dokumen sebelum dapat diajukan.'],
            ]);
        }

        $fromStatus = $application->status;

        DB::transaction(function () use ($application, $request, $fromStatus) {
            // Increment version only on re-submit after revision
            $newVersion = $fromStatus === ApplicationStatus::RevisionRequested
                ? $application->version + 1
                : $application->version;

            $application->update([
                'status'       => ApplicationStatus::Submitted->value,
                'submitted_at' => now(),
                'version'      => $newVersion,
            ]);

            // Record the status transition in history
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'changed_by'     => $request->user()->id,
                'from_status'    => $fromStatus->value,
                'to_status'      => ApplicationStatus::Submitted->value,
                'notes'          => 'Diajukan oleh pemohon.',
            ]);
        });

        return response()->json([
            'message' => 'Permohonan berhasil diajukan.',
            'data'    => new ApplicationResource($application->fresh()->load('project:id,name,status')),
        ]);
    }

    /**
     * Generate a unique application number: APP-{YEAR}-{6-digit random}.
     */
    private function generateApplicationNumber(): string
    {
        $year = now()->year;

        do {
            $number = 'APP-' . $year . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Application::where('application_number', $number)->exists());

        return $number;
    }
}
