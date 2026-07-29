<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Only applicants can view their own project list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('applicant');
    }

    /**
     * Owner can view any single project.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->applicant_id;
    }

    /**
     * Only applicants can create projects.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('applicant');
    }

    /**
     * Only the owner (applicant) can update their project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->applicant_id;
    }

    /**
     * Only the owner can delete a project, and only if there are no applications.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->applicant_id
            && $project->applications()->count() === 0;
    }
}
