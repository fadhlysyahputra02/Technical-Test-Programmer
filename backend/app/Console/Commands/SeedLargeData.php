<?php

namespace App\Console\Commands;

use Database\Seeders\ApplicationSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;

class SeedLargeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-large-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed roles, users (1.000 applicants & 1.000 reviewers), 10.000 projects, and 10.000 applications using bulk DB inserts.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting large data seeding process...');

        $this->info('1. Seeding Spatie roles & permissions...');
        $roleSeeder = new RoleSeeder();
        $roleSeeder->setCommand($this);
        $roleSeeder->run();

        $this->info('2. Seeding users (applicants and reviewers)...');
        $userSeeder = new UserSeeder();
        $userSeeder->setCommand($this);
        $userSeeder->run();

        $this->info('3. Seeding projects (10.000 records)...');
        $projectSeeder = new ProjectSeeder();
        $projectSeeder->setCommand($this);
        $projectSeeder->run();

        $this->info('4. Seeding applications (10.000 records)...');
        $applicationSeeder = new ApplicationSeeder();
        $applicationSeeder->setCommand($this);
        $applicationSeeder->run();

        $this->info('✓ Large data seeding completed successfully!');
    }
}
