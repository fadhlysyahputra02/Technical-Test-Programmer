<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for Spatie Permission before each test
        $this->seed(RoleSeeder::class);

        // Fake local storage for document uploads
        Storage::fake('local');
    }

    /**
     * Helper to create a user and assign a role.
     */
    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole($roleName);
        return $user;
    }

    // 1. applicant_can_register_and_login
    public function test_applicant_can_register_and_login(): void
    {
        $registerData = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Register
        $response = $this->postJson('/api/register', $registerData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'email', 'roles'],
                'token'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('applicant'));

        // Login
        $loginResponse = $this->postJson('/api/login', [
            'email'    => 'john@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['message', 'data', 'token']);
    }

    // 2. applicant_can_create_project
    public function test_applicant_can_create_project(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $projectData = [
            'name'        => 'Proyek Penelitian Baru',
            'description' => 'Deskripsi proyek pengujian.',
            'status'      => 'active'
        ];

        $response = $this->postJson('/api/projects', $projectData);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Proyek Penelitian Baru');

        $this->assertDatabaseHas('projects', [
            'name'         => 'Proyek Penelitian Baru',
            'applicant_id' => $applicant->id
        ]);
    }

    // 3. applicant_can_create_draft_application
    public function test_applicant_can_create_draft_application(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $project = Project::factory()->create(['applicant_id' => $applicant->id]);

        $response = $this->postJson('/api/applications', [
            'project_id' => $project->id
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.version', 1);

        $this->assertDatabaseHas('applications', [
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => 'draft'
        ]);
    }

    // 4. applicant_cannot_edit_submitted_application
    public function test_applicant_cannot_edit_submitted_application(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Submitted->value,
            'version'      => 1
        ]);

        $response = $this->putJson("/api/applications/{$application->id}", [
            'notes' => 'Catatan revisi baru.'
        ]);

        $response->assertStatus(403);
    }

    // 5. applicant_can_upload_document_to_draft
    public function test_applicant_can_upload_document_to_draft(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Draft->value
        ]);

        $file = UploadedFile::fake()->create('test-document.pdf', 500, 'application/pdf');

        $response = $this->postJson("/api/applications/{$application->id}/documents", [
            'file' => $file
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('application_documents', [
            'application_id' => $application->id,
            'file_name'      => 'test-document.pdf',
            'uploaded_by'    => $applicant->id
        ]);
    }

    // 6. applicant_can_submit_application_with_document
    public function test_applicant_can_submit_application_with_document(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Draft->value
        ]);

        // Seed at least 1 document first
        ApplicationDocument::create([
            'application_id' => $application->id,
            'file_name'      => 'doc.pdf',
            'file_path'      => 'documents/1/doc.pdf',
            'file_type'      => 'application/pdf',
            'file_size'      => 1024,
            'uploaded_by'    => $applicant->id
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/submit");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'submitted');

        $this->assertDatabaseHas('applications', [
            'id'     => $application->id,
            'status' => 'submitted'
        ]);
    }

    // 7. applicant_cannot_submit_without_document
    public function test_applicant_cannot_submit_without_document(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Draft->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/submit");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['documents']);
    }

    // 8. reviewer_can_request_revision
    public function test_reviewer_can_request_revision(): void
    {
        $reviewer = $this->createUserWithRole('reviewer');
        Sanctum::actingAs($reviewer);

        $applicant   = $this->createUserWithRole('applicant');
        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Submitted->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/reviews", [
            'decision' => 'revision_requested',
            'notes'    => 'Mohon perbaiki dokumen lampiran nomor 2.'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('applications', [
            'id'                 => $application->id,
            'status'             => 'revision_requested',
            'latest_reviewer_id' => $reviewer->id
        ]);
    }

    // 9. reviewer_can_approve_application
    public function test_reviewer_can_approve_application(): void
    {
        $reviewer = $this->createUserWithRole('reviewer');
        Sanctum::actingAs($reviewer);

        $applicant   = $this->createUserWithRole('applicant');
        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Submitted->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/reviews", [
            'decision' => 'approved',
            'notes'    => 'Seluruh dokumen valid dan disetujui.'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('applications', [
            'id'     => $application->id,
            'status' => 'approved'
        ]);
        $this->assertNotNull($application->fresh()->approved_at);
    }

    // 10. reviewer_can_reject_application
    public function test_reviewer_can_reject_application(): void
    {
        $reviewer = $this->createUserWithRole('reviewer');
        Sanctum::actingAs($reviewer);

        $applicant   = $this->createUserWithRole('applicant');
        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Submitted->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/reviews", [
            'decision' => 'rejected',
            'notes'    => 'Dokumen tidak lengkap dan permohonan ditolak.'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('applications', [
            'id'     => $application->id,
            'status' => 'rejected'
        ]);
        $this->assertNotNull($application->fresh()->rejected_at);
    }

    // 11. applicant_cannot_access_other_applicant_application
    public function test_applicant_cannot_access_other_applicant_application(): void
    {
        $applicantA = $this->createUserWithRole('applicant');
        $applicantB = $this->createUserWithRole('applicant');

        $projectB     = Project::factory()->create(['applicant_id' => $applicantB->id]);
        $applicationB = Application::factory()->create([
            'project_id'   => $projectB->id,
            'applicant_id' => $applicantB->id,
            'status'       => ApplicationStatus::Draft->value
        ]);

        Sanctum::actingAs($applicantA);

        $response = $this->getJson("/api/applications/{$applicationB->id}");
        $response->assertStatus(403);
    }

    // 12. reviewer_cannot_review_draft_application
    public function test_reviewer_cannot_review_draft_application(): void
    {
        $reviewer = $this->createUserWithRole('reviewer');
        Sanctum::actingAs($reviewer);

        $applicant   = $this->createUserWithRole('applicant');
        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Draft->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/reviews", [
            'decision' => 'approved',
            'notes'    => 'Mencoba menilai permohonan berstatus draft.'
        ]);

        $response->assertStatus(403);
    }

    // 13. approved_application_cannot_be_reviewed_again
    public function test_approved_application_cannot_be_reviewed_again(): void
    {
        $reviewer = $this->createUserWithRole('reviewer');
        Sanctum::actingAs($reviewer);

        $applicant   = $this->createUserWithRole('applicant');
        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Approved->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/reviews", [
            'decision' => 'rejected',
            'notes'    => 'Mencoba merubah permohonan yang sudah approved.'
        ]);

        $response->assertStatus(403);
    }

    // 14. status_history_is_recorded_on_every_decision
    public function test_status_history_is_recorded_on_every_decision(): void
    {
        $reviewer = $this->createUserWithRole('reviewer');
        Sanctum::actingAs($reviewer);

        $applicant   = $this->createUserWithRole('applicant');
        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::Submitted->value
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/reviews", [
            'decision' => 'approved',
            'notes'    => 'Persetujuan resmi dari penilai.'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->id,
            'from_status'    => 'submitted',
            'to_status'      => 'approved',
            'changed_by'     => $reviewer->id,
            'notes'          => 'Persetujuan resmi dari penilai.'
        ]);
    }

    // 15. applicant_can_resubmit_after_revision
    public function test_applicant_can_resubmit_after_revision(): void
    {
        $applicant = $this->createUserWithRole('applicant');
        Sanctum::actingAs($applicant);

        $project     = Project::factory()->create(['applicant_id' => $applicant->id]);
        $application = Application::factory()->create([
            'project_id'   => $project->id,
            'applicant_id' => $applicant->id,
            'status'       => ApplicationStatus::RevisionRequested->value,
            'version'      => 1
        ]);

        // Seed 1 document
        ApplicationDocument::create([
            'application_id' => $application->id,
            'file_name'      => 'doc_v2.pdf',
            'file_path'      => 'documents/1/doc_v2.pdf',
            'file_type'      => 'application/pdf',
            'file_size'      => 2048,
            'uploaded_by'    => $applicant->id
        ]);

        $response = $this->postJson("/api/applications/{$application->id}/submit");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.version', 2); // Version increments on re-submit

        $this->assertDatabaseHas('applications', [
            'id'      => $application->id,
            'status'  => 'submitted',
            'version' => 2
        ]);
    }
}
