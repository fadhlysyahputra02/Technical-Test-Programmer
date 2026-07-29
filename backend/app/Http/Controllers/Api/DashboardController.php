<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Models\ApplicationReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * GET /api/dashboard
     * Returns role-based dashboard data with Redis caching (TTL: 5 min).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('reviewer')) {
            return response()->json($this->reviewerDashboard($user));
        }

        return response()->json($this->applicantDashboard($user));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // APPLICANT DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────

    private function applicantDashboard(object $user): array
    {
        $summary = Cache::remember(
            "dashboard:applicant:summary:{$user->id}",
            self::CACHE_TTL,
            fn () => $this->applicantSummary($user->id)
        );

        $chart = Cache::remember(
            "dashboard:applicant:chart:{$user->id}",
            self::CACHE_TTL,
            fn () => $this->applicantMonthlyChart($user->id)
        );

        // Recent 5 applications — not cached (real-time)
        $recent = Application::select([
            'id', 'application_number', 'project_id', 'applicant_id',
            'status', 'submitted_at', 'version', 'created_at', 'updated_at',
        ])
            ->with('project:id,name,status')
            ->where('applicant_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        return [
            'role'                => 'applicant',
            'summary'             => $summary,
            'chart_monthly'       => $chart,
            'recent_applications' => ApplicationResource::collection($recent)->resolve(),
        ];
    }

    private function applicantSummary(int $userId): array
    {
        $counts = Application::select('status', DB::raw('count(*) as total'))
            ->where('applicant_id', $userId)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $get = fn (ApplicationStatus $s) => (int) ($counts[$s->value] ?? 0);

        return [
            'total'              => array_sum($counts),
            'draft'              => $get(ApplicationStatus::Draft),
            'submitted'          => $get(ApplicationStatus::Submitted),
            'under_review'       => $get(ApplicationStatus::UnderReview),
            'revision_requested' => $get(ApplicationStatus::RevisionRequested),
            'approved'           => $get(ApplicationStatus::Approved),
            'rejected'           => $get(ApplicationStatus::Rejected),
        ];
    }

    private function applicantMonthlyChart(int $userId): array
    {
        $months = $this->last12Months();

        $rows = Application::select([
            DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month_key"),
            'status',
            DB::raw('count(*) as total'),
        ])
            ->where('applicant_id', $userId)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key', 'status')
            ->get();

        // Index by [month_key][status] => count
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->month_key][$row->status] = (int) $row->total;
        }

        return collect($months)->map(function (array $month) use ($indexed) {
            $data = $indexed[$month['key']] ?? [];
            $total = array_sum($data);

            return [
                'month'    => $month['label'],
                'total'    => $total,
                'approved' => (int) ($data[ApplicationStatus::Approved->value] ?? 0),
                'rejected' => (int) ($data[ApplicationStatus::Rejected->value] ?? 0),
            ];
        })->values()->all();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REVIEWER DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────

    private function reviewerDashboard(object $user): array
    {
        $summary = Cache::remember(
            "dashboard:reviewer:summary:{$user->id}",
            self::CACHE_TTL,
            fn () => $this->reviewerSummary()
        );

        $chart = Cache::remember(
            "dashboard:reviewer:chart:{$user->id}",
            self::CACHE_TTL,
            fn () => $this->reviewerMonthlyChart()
        );

        // 5 applications needing review — not cached (real-time)
        $recent = Application::select([
            'id', 'application_number', 'project_id', 'applicant_id',
            'status', 'submitted_at', 'latest_reviewer_id', 'version', 'created_at', 'updated_at',
        ])
            ->with([
                'project:id,name,status',
                'applicant:id,name',
                'latestReviewer:id,name',
            ])
            ->whereIn('status', [
                ApplicationStatus::Submitted->value,
                ApplicationStatus::UnderReview->value,
            ])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return [
            'role'                => 'reviewer',
            'summary'             => $summary,
            'chart_monthly'       => $chart,
            'recent_applications' => ApplicationResource::collection($recent)->resolve(),
        ];
    }

    private function reviewerSummary(): array
    {
        $counts = Application::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $get = fn (ApplicationStatus $s) => (int) ($counts[$s->value] ?? 0);

        $pending = $get(ApplicationStatus::Submitted) + $get(ApplicationStatus::UnderReview);

        return [
            'total_incoming'     => array_sum($counts),
            'pending_review'     => $pending,
            'approved'           => $get(ApplicationStatus::Approved),
            'revision_requested' => $get(ApplicationStatus::RevisionRequested),
            'rejected'           => $get(ApplicationStatus::Rejected),
        ];
    }

    private function reviewerMonthlyChart(): array
    {
        $months = $this->last12Months();

        // Count reviews by decision per month (reviewer's own activity)
        $rows = ApplicationReview::select([
            DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month_key"),
            'decision',
            DB::raw('count(*) as total'),
        ])
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key', 'decision')
            ->get();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row->month_key][$row->decision] = (int) $row->total;
        }

        return collect($months)->map(function (array $month) use ($indexed) {
            $data = $indexed[$month['key']] ?? [];

            return [
                'month'    => $month['label'],
                'approved' => (int) ($data['approved'] ?? 0),
                'rejected' => (int) ($data['rejected'] ?? 0),
                'revision' => (int) ($data['revision_requested'] ?? 0),
            ];
        })->values()->all();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build an array of the last 12 months (key + human label).
     *
     * @return array<int, array{key: string, label: string}>
     */
    private function last12Months(): array
    {
        return collect(range(11, 0))
            ->map(fn (int $offset) => [
                'key'   => now()->subMonths($offset)->format('Y-m'),
                'label' => now()->subMonths($offset)->translatedFormat('M Y'),
            ])
            ->all();
    }
}
