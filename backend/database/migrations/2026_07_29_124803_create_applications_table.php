<?php

use App\Enums\ApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', array_column(ApplicationStatus::cases(), 'value'))
                  ->default(ApplicationStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('latest_reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->integer('version')->default(1);
            $table->timestamps();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['applicant_id', 'status']);
            $table->index('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
