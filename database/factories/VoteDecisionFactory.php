<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VoteDecisionEnum;
use App\Models\VoteDecision;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteDecisionFactory extends Factory
{
    protected $model = VoteDecision::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(VoteDecisionEnum::cases()),
        ];
    }
}