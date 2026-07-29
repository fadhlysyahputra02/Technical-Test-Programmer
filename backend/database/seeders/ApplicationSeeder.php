<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding applications (1 per project)...');

        $statuses = array_column(ApplicationStatus::cases(), 'value');

        // Get all projects with their applicant_id
        $projects = DB::table('projects')->select('id', 'applicant_id')->get();

        if ($projects->isEmpty()) {
            $this->command->warn('No projects found. Run ProjectSeeder first.');
            return;
        }

        $now       = now()->toDateTimeString();
        $chunkSize = 500;
        $rows      = [];
        $counter   = 0;

        foreach ($projects as $project) {
            $status = $statuses[array_rand($statuses)];
            $statusEnum = ApplicationStatus::from($status);

            $submittedAt = in_array($statusEnum, [
                ApplicationStatus::Submitted,
                ApplicationStatus::UnderReview,
                ApplicationStatus::RevisionRequested,
                ApplicationStatus::Approved,
                ApplicationStatus::Rejected,
            ]) ? now()->subDays(rand(1, 180))->toDateTimeString() : null;

            $approvedAt = $statusEnum === ApplicationStatus::Approved
                ? now()->subDays(rand(1, 90))->toDateTimeString()
                : null;

            $rejectedAt = $statusEnum === ApplicationStatus::Rejected
                ? now()->subDays(rand(1, 90))->toDateTimeString()
                : null;

            $rows[] = [
                'application_number' => strtoupper('APP-' . str_pad($project->id, 6, '0', STR_PAD_LEFT) . '-' . Str::upper(Str::random(4))),
                'project_id'         => $project->id,
                'applicant_id'       => $project->applicant_id,
                'status'             => $status,
                'submitted_at'       => $submittedAt,
                'approved_at'        => $approvedAt,
                'rejected_at'        => $rejectedAt,
                'latest_reviewer_id' => null,
                'notes'              => null,
                'version'            => rand(1, 3),
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            $counter++;

            // Flush chunk
            if ($counter % $chunkSize === 0) {
                DB::table('applications')->insert($rows);
                $rows = [];
                $this->command->info("  → Inserted {$counter} applications...");
            }
        }

        // Insert remaining rows
        if (!empty($rows)) {
            DB::table('applications')->insert($rows);
        }

        $this->command->info('✓ ' . $projects->count() . ' applications seeded.');
    }
}
