<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        // 1. Create roles and permissions first
            UserSeeder::class,        // 2. Create users and assign roles
            ProjectSeeder::class,     // 3. Create 10.000 projects assigned to applicants
            ApplicationSeeder::class, // 4. Create 10.000 applications (1 per project)
        ]);
    }
}
