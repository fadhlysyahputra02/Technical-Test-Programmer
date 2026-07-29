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
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->enum('from_status', array_column(ApplicationStatus::cases(), 'value'));
            $table->enum('to_status', array_column(ApplicationStatus::cases(), 'value'));
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index
            $table->index(['application_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};
