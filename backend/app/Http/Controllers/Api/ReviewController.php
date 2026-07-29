<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationDecision;
use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\StatusHistoryResource;
use App\Models\Application;
use App\Models\ApplicationReview;
use App\Models\ApplicationStatusHistory;
use App\Notifications\ApplicationReviewedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * GET /api/reviewer/applications
     * List all incoming applications for reviewers.
     * Filters: status, project_id, applicant_id, date_from, date_to, search (application_number)
     */
    public function applicationList(Request $request): AnonymousResourceCollection
    {
        abort_unless($request->user()->hasRole('reviewer'), 403, 'Hanya penilai yang dapat mengakses endpoint ini.');

        $applications = Application::select([
            'id', 'application_number', 'project_id', 'applicant_id',
            'status', 'submitted_at', 'approved_at', 'rejected_at',
            'latest_reviewer_id', 'version', 'created_at', 'updated_at',
        ])
            ->with([
                'project:id,name,status',
                'applicant:id,name,email',
                'latestReviewer:id,name',
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('applicant_id'), fn ($q) => $q->where('applicant_id', $request->applicant_id))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('submitted_at', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('submitted_at', '<=', $request->date_to))
            ->when($request->filled('search'), fn ($q) => $q->where('application_number', 'ilike', '%' . $request->search . '%'))
            ->latest('submitted_at')
            ->paginate(20);

        return ApplicationResource::collection($applications);
    }

    /**
     * POST /api/applications/{application}/reviews
     * Submit a review decision on an application.
     *
     * Within DB::transaction:
     *  1. Save ApplicationReview record
     *  2. Update application status based on decision
     *  3. Update latest_reviewer_id
     *  4. Save ApplicationStatusHistory (from → to, changed_by, notes)
     *  5. Notify the applicant (queued)
     */
    public function store(StoreReviewRequest $request, Application $application): JsonResponse
    {
        $decision   = ApplicationDecision::from($request->decision);
        $fromStatus = $application->status;
        $toStatus   = $this->resolveStatusFromDecision($decision);

        $review = DB::transaction(function () use ($request, $application, $decision, $fromStatus, $toStatus) {
            // 1. Save review record
            $review = ApplicationReview::create([
                'application_id' => $application->id,
                'reviewer_id'    => $request->user()->id,
                'decision'       => $decision->value,
                'notes'          => $request->notes,
            ]);

            // 2 & 3. Update application status and latest_reviewer_id
            $updatePayload = [
                'status'             => $toStatus->value,
                'latest_reviewer_id' => $request->user()->id,
            ];

            if ($toStatus === ApplicationStatus::Approved) {
                $updatePayload['approved_at'] = now();
            } elseif ($toStatus === ApplicationStatus::Rejected) {
                $updatePayload['rejected_at'] = now();
            }

            $application->update($updatePayload);

            // 4. Record status history
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'changed_by'     => $request->user()->id,
                'from_status'    => $fromStatus->value,
                'to_status'      => $toStatus->value,
                'notes'          => $request->notes,
            ]);

            return $review;
        });

        // 5. Dispatch queued notification to the applicant
        $review->load('reviewer:id,name', 'application');
        $application->applicant->notify(new ApplicationReviewedNotification($review));

        return response()->json([
            'message' => 'Review berhasil diberikan.',
            'data'    => new ReviewResource($review),
            'application' => [
                'id'          => $application->id,
                'status'      => $application->fresh()->status,
                'status_label' => $application->fresh()->status->label(),
            ],
        ], 201);
    }

    /**
     * GET /api/applications/{application}/histories
     * Status change history for an application.
     * Accessible by the owner or any reviewer.
     */
    public function histories(Request $request, Application $application): AnonymousResourceCollection
    {
        $user = $request->user();

        abort_unless(
            $user->id === $application->applicant_id || $user->hasRole('reviewer'),
            403,
            'Anda tidak memiliki akses ke riwayat permohonan ini.'
        );

        $histories = $application->statusHistories()
            ->select(['id', 'application_id', 'changed_by', 'from_status', 'to_status', 'notes', 'created_at'])
            ->with('changedBy:id,name')
            ->latest()
            ->get();

        return StatusHistoryResource::collection($histories);
    }

    /**
     * Map ApplicationDecision to the corresponding ApplicationStatus.
     */
    private function resolveStatusFromDecision(ApplicationDecision $decision): ApplicationStatus
    {
        return match ($decision) {
            ApplicationDecision::Approved          => ApplicationStatus::Approved,
            ApplicationDecision::Rejected          => ApplicationStatus::Rejected,
            ApplicationDecision::RevisionRequested => ApplicationStatus::RevisionRequested,
        };
    }
}
