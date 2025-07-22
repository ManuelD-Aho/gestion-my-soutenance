<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ConformityStatusEnum;
use App\Models\ConformityCheckDetail;
use App\Models\ConformityCriterion;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConformityCheckDetailFactory extends Factory
{
    protected $model = ConformityCheckDetail::class;

    public function definition(): array
    {
        $criterion = ConformityCriterion::inRandomOrder()->first() ?? ConformityCriterion::factory()->create();
        $status = $this->faker->randomElement(ConformityStatusEnum::cases());

        return [
            'report_id' => Report::factory(),
            'conformity_criterion_id' => $criterion->id,
            'validation_status' => $status,
            'comment' => $status === ConformityStatusEnum::NON_CONFORME ? $this->faker->sentence() : null,
            'verification_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'verified_by_user_id' => User::factory(),
            'criterion_label' => $criterion->label,
            'criterion_description' => $criterion->description,
            'criterion_version' => $criterion->version,
        ];
    }
}