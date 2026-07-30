<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * GET /api/projects
     * List projects belonging to the authenticated applicant.
     * Supports filters: status, search (name).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::select(['id', 'name', 'description', 'status', 'applicant_id', 'created_at', 'updated_at'])
            ->withCount('applications');

        if ($request->user()->hasRole('applicant')) {
            $query->where('applicant_id', $request->user()->id);
        }

        $projects = $query
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'ilike', '%' . $request->search . '%'))
            ->latest()
            ->paginate($request->integer('limit', 20));

        return ProjectResource::collection($projects);
    }

    /**
     * POST /api/projects
     * Create a new project (applicant only, authorized via StoreProjectRequest).
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'name'         => $request->name,
            'description'  => $request->description,
            'applicant_id' => $request->user()->id,
            'status'       => $request->input('status', 'active'),
        ]);

        return response()->json([
            'message' => 'Project berhasil dibuat.',
            'data'    => new ProjectResource($project),
        ], 201);
    }

    /**
     * GET /api/projects/{project}
     * Show a single project with its application count and applicant detail.
     */
    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->loadCount('applications')
                ->load(['applicant:id,name,email']);

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * PUT /api/projects/{project}
     * Update a project (owner only, authorized via UpdateProjectRequest).
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->only(['name', 'description', 'status']));

        return response()->json([
            'message' => 'Project berhasil diperbarui.',
            'data'    => new ProjectResource($project->fresh()),
        ]);
    }

    /**
     * DELETE /api/projects/{project}
     * Delete a project if it has no applications.
     */
    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json([
            'message' => 'Project berhasil dihapus.',
        ]);
    }
}
