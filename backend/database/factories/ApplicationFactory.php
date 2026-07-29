<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        $status = fake()->randomElement(ApplicationStatus::cases());

        $submittedAt   = in_array($status, [
            ApplicationStatus::Submitted,
            ApplicationStatus::UnderReview,
            ApplicationStatus::RevisionRequested,
            ApplicationStatus::Approved,
            ApplicationStatus::Rejected,
        ]) ? fake()->dateTimeBetween('-6 months', 'now') : null;

        $approvedAt = $status === ApplicationStatus::Approved
            ? fake()->dateTimeBetween($submittedAt ?? '-3 months', 'now')
            : null;

        $rejectedAt = $status === ApplicationStatus::Rejected
            ? fake()->dateTimeBetween($submittedAt ?? '-3 months', 'now')
            : null;

        return [
            'application_number'  => strtoupper(fake()->bothify('APP-####-????')),
            'project_id'          => null, // to be set when seeding
            'applicant_id'        => null, // to be set when seeding
            'status'              => $status->value,
            'submitted_at'        => $submittedAt,
            'approved_at'         => $approvedAt,
            'rejected_at'         => $rejectedAt,
            'latest_reviewer_id'  => null,
            'notes'               => fake()->optional(0.4)->sentence(),
            'version'             => fake()->numberBetween(1, 3),
        ];
    }
}
