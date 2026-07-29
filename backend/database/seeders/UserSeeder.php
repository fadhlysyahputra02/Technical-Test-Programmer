<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now()->toDateTimeString();
        $password = Hash::make('password');
        $demoPassword = Hash::make('password123');

        // ─── Demo users ───────────────────────────────────────────────
        $demoApplicant = User::firstOrCreate(
            ['email' => 'applicant@demo.com'],
            [
                'name'              => 'Demo Applicant',
                'password'          => $demoPassword,
                'email_verified_at' => $now,
                'remember_token'    => Str::random(10),
            ]
        );
        $demoApplicant->syncRoles('applicant');

        $demoReviewer = User::firstOrCreate(
            ['email' => 'reviewer@demo.com'],
            [
                'name'              => 'Demo Reviewer',
                'password'          => $demoPassword,
                'email_verified_at' => $now,
                'remember_token'    => Str::random(10),
            ]
        );
        $demoReviewer->syncRoles('reviewer');

        $this->command->info('✓ Demo users seeded.');

        // ─── 1.000 Applicant users (chunked insert) ───────────────────
        $this->seedUsersWithRole('applicant', 1000, $password, $now);
        $this->command->info('✓ 1.000 applicant users seeded.');

        // ─── 1.000 Reviewer users (chunked insert) ────────────────────
        $this->seedUsersWithRole('reviewer', 1000, $password, $now);
        $this->command->info('✓ 1.000 reviewer users seeded.');
    }

    private function seedUsersWithRole(string $role, int $total, string $password, string $now): void
    {
        $chunkSize = 500;
        $batches   = (int) ceil($total / $chunkSize);

        for ($batch = 0; $batch < $batches; $batch++) {
            $count = ($batch === $batches - 1)
                ? $total - ($batch * $chunkSize)
                : $chunkSize;

            $rows = [];
            for ($i = 0; $i < $count; $i++) {
                $rows[] = [
                    'name'              => fake()->name(),
                    'email'             => "user_{$role}_{$batch}_{$i}_" . Str::random(6) . "@example.com",
                    'password'          => $password,
                    'email_verified_at' => $now,
                    'remember_token'    => Str::random(10),
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }

            DB::table('users')->insert($rows);

            // Assign roles to the just-inserted chunk using last inserted IDs
            $lastId   = DB::table('users')->orderByDesc('id')->value('id');
            $firstId  = $lastId - $count + 1;
            $userIds  = range($firstId, $lastId);

            $roleId   = DB::table('roles')->where('name', $role)->value('id');
            $roleRows = [];
            foreach ($userIds as $userId) {
                $roleRows[] = [
                    'role_id'    => $roleId,
                    'model_id'   => $userId,
                    'model_type' => 'App\\Models\\User',
                ];
            }
            DB::table('model_has_roles')->insert($roleRows);
        }
    }
}
