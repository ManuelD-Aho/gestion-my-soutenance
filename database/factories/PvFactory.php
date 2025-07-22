<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PvStatusEnum;
use App\Models\CommissionSession;
use App\Models\Pv;
use App\Models\Report;
use App\Models\User;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class PvFactory extends Factory
{
    protected $model = Pv::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');
        $isReportSpecific = $this->faker->boolean(30);
        $status = $this->faker->randomElement(PvStatusEnum::cases());
        
        // Générer une date de début pour la période d'approbation
        $approvalStartDate = $this->faker->dateTimeBetween('-1 month', 'now');
        // La date limite d'approbation doit être après la date de début
        $approvalDeadline = (clone $approvalStartDate)->modify('+7 days');

        return [
            'pv_id' => $uniqueIdGeneratorService->generate('PV', $currentYear),
            'commission_session_id' => CommissionSession::factory(),
            'report_id' => $isReportSpecific ? Report::factory() : null,
            'type' => $isReportSpecific ? 'report_specific' : 'session',
            'content' => $this->faker->paragraphs(3, true),
            'author_user_id' => User::factory(),
            'status' => $status,
            'approval_deadline' => $status === PvStatusEnum::PENDING_APPROVAL ? $approvalDeadline : null,
            'version' => $this->faker->numberBetween(1, 3),
        ];
    }
}