<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Applicants can view their own application list.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('applicant');
    }

    /**
     * Owner or any reviewer can view a single application.
     */
    public function view(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            || $user->hasRole('reviewer');
    }

    /**
     * Only applicants can create applications.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('applicant');
    }

    /**
     * Only the owner can update, and only when status is draft or revision_requested.
     */
    public function update(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            && in_array($application->status, [
                ApplicationStatus::Draft,
                ApplicationStatus::RevisionRequested,
            ]);
    }

    /**
     * Only the owner can submit, and only from draft or revision_requested.
     */
    public function submit(User $user, Application $application): bool
    {
        return $user->id === $application->applicant_id
            && in_array($application->status, [
                ApplicationStatus::Draft,
                ApplicationStatus::RevisionRequested,
            ]);
    }
}
