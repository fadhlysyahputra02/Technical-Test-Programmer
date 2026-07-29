<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_number',
        'project_id',
        'applicant_id',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'latest_reviewer_id',
        'notes',
        'version',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'version' => 'integer',
        ];
    }

    /**
     * Get the Indonesian label for the application status.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status ? $this->status->label() : '';
    }

    /**
     * Scope a query to only include applications of a given status.
     */
    public function scopeByStatus(Builder $query, ApplicationStatus|string $status): Builder
    {
        if ($status instanceof ApplicationStatus) {
            return $query->where('status', $status->value);
        }

        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include applications of a given applicant.
     */
    public function scopeByApplicant(Builder $query, User|int $applicant): Builder
    {
        $applicantId = $applicant instanceof User ? $applicant->id : $applicant;

        return $query->where('applicant_id', $applicantId);
    }

    /**
     * Scope a query to only include applications of a given project.
     */
    public function scopeByProject(Builder $query, Project|int $project): Builder
    {
        $projectId = $project instanceof Project ? $project->id : $project;

        return $query->where('project_id', $projectId);
    }

    /**
     * Get the project associated with the application.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the user who applied for this application.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * Get the documents uploaded for the application.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    /**
     * Get the reviews submitted for the application.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ApplicationReview::class, 'application_id');
    }

    /**
     * Get the status changes history for the application.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id');
    }

    /**
     * Get the latest reviewer who decided on this application.
     */
    public function latestReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'latest_reviewer_id');
    }
}
