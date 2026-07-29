<?php

namespace App\Models;

use App\Enums\ApplicationDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationReview extends Model
{
    protected $fillable = [
        'application_id',
        'reviewer_id',
        'decision',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decision' => ApplicationDecision::class,
        ];
    }

    /**
     * Get the application associated with the review.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /**
     * Get the user who reviewed the application.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
