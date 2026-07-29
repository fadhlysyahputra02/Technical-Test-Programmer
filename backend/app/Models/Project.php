<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'applicant_id',
        'status',
    ];

    /**
     * Get the user who owns this project.
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    /**
     * Get the applications associated with the project.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'project_id');
    }
}
