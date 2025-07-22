<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PvApprovalDecisionEnum;
use App\Models\Pv;
use App\Models\PvApproval;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class PvApprovalFactory extends Factory
{
    protected $model = PvApproval::class;

    public function definition(): array
    {
        $validationDate = $this->faker->dateTimeBetween('-3 months', 'now');
        $decision = $this->faker->randomElement(PvApprovalDecisionEnum::cases());

        return [
            'pv_id' => Pv::factory(),
            'teacher_id' => Teacher::factory(),
            'pv_approval_decision_id' => $decision,
            'validation_date' => $validationDate,
            'comment' => $decision !== PvApprovalDecisionEnum::APPROVED ? $this->faker->sentence() : null,
        ];
    }
}