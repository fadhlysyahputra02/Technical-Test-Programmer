<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Get the application associated with the document.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
