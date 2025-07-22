<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConformityCriterion;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConformityCriterionFactory extends Factory
{
    protected $model = ConformityCriterion::class;

    public function definition(): array
    {
        return [
            'label' => $this->faker->unique()->sentence(3),
            'description' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(90),
            'type' => $this->faker->randomElement(['MANUAL', 'AUTOMATIC']),
            'version' => $this->faker->numberBetween(1, 3),
            'code' => $this->faker->unique()->word() . '_' . $this->faker->randomNumber(3),
        ];
    }
}