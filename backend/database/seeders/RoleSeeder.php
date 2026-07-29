<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions per role
        $applicantPermissions = [
            'create-project',
            'edit-project',
            'create-application',
            'edit-application',
            'submit-application',
            'upload-document',
            'view-own-application',
        ];

        $reviewerPermissions = [
            'view-all-applications',
            'review-application',
            'view-all-reviews',
        ];

        // Create all unique permissions
        $allPermissions = array_unique(array_merge($applicantPermissions, $reviewerPermissions));
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create 'applicant' role and assign permissions
        $applicantRole = Role::firstOrCreate(['name' => 'applicant', 'guard_name' => 'web']);
        $applicantRole->syncPermissions($applicantPermissions);

        // Create 'reviewer' role and assign permissions
        $reviewerRole = Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $reviewerRole->syncPermissions($reviewerPermissions);

        $this->command->info('✓ Roles and permissions seeded successfully.');
    }
}
