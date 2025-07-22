<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicDecision;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicDecisionFactory extends Factory
{
    protected $model = AcademicDecision::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Admis', 'Ajourné', 'Exclu', 'Validé', 'Non Validé']),
            'description' => $this->faker->sentence(),
        ];
    }
}