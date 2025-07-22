<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\StudyLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyLevelFactory extends Factory
{
    protected $model = StudyLevel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2',
                'Doctorat 1', 'Doctorat 2', 'Doctorat 3',
            ]),
            'description' => $this->faker->sentence(),
        ];
    }
}