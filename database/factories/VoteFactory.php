<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VoteDecisionEnum;
use App\Models\CommissionSession;
use App\Models\Report;
use App\Models\Teacher;
use App\Models\Vote;
use App\Models\VoteDecision;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');

        // Générer une date de vote dans une plage réaliste
        $voteDate = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'vote_id' => $uniqueIdGeneratorService->generate('VOTE', $currentYear),
            'commission_session_id' => CommissionSession::factory(),
            'report_id' => Report::factory(),
            'teacher_id' => Teacher::factory(),
            'vote_decision_id' => VoteDecision::inRandomOrder()->first()->id, // Récupère l'ID d'une décision de vote existante
            'comment' => $this->faker->boolean(70) ? $this->faker->sentence() : null,
            'vote_date' => $voteDate,
            'vote_round' => $this->faker->numberBetween(1, 2),
            'status' => $this->faker->randomElement(['ACTIVE', 'CANCELLED']),
        ];
    }
}
