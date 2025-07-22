<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CommissionSessionModeEnum;
use App\Enums\CommissionSessionStatusEnum;
use App\Models\CommissionSession;
use App\Models\Teacher;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionSessionFactory extends Factory
{
    protected $model = CommissionSession::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');
        
        // Générer une date de début de session dans une plage réaliste
        $startDate = $this->faker->dateTimeBetween('-6 months', '+3 months');
        // La date de fin planifiée doit être après la date de début
        $endDatePlanned = $this->faker->dateTimeBetween($startDate, (clone $startDate)->modify('+4 hours'));

        return [
            'session_id' => $uniqueIdGeneratorService->generate('SESS', $currentYear),
            'name' => 'Session de Soutenance '.$this->faker->unique()->word().' '.$this->faker->year(),
            'start_date' => $startDate,
            'end_date_planned' => $endDatePlanned,
            'president_teacher_id' => Teacher::factory(),
            'mode' => $this->faker->randomElement(CommissionSessionModeEnum::cases()),
            'status' => $this->faker->randomElement(CommissionSessionStatusEnum::cases()),
            'required_voters_count' => $this->faker->numberBetween(1, 3),
        ];
    }
}