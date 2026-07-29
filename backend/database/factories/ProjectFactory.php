<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name'         => fake()->sentence(3, true),
            'description'  => fake()->paragraph(2),
            'applicant_id' => null, // to be set when seeding
            'status'       => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
