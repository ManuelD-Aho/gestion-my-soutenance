<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReclamationStatusEnum;
use App\Models\AdministrativeStaff;
use App\Models\Penalty;
use App\Models\Reclamation;
use App\Models\Report;
use App\Models\Student;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReclamationFactory extends Factory
{
    protected $model = Reclamation::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');
        
        // Générer une date de soumission dans une plage réaliste
        $submissionDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $status = $this->faker->randomElement(ReclamationStatusEnum::cases());

        $reclaimableType = $this->faker->randomElement([Report::class, Penalty::class, null]);
        $reclaimableId = null;
        if ($reclaimableType === Report::class) {
            $reclaimableId = Report::factory();
        } elseif ($reclaimableType === Penalty::class) {
            $reclaimableId = Penalty::factory();
        }

        return [
            'reclamation_id' => $uniqueIdGeneratorService->generate('RECLA', $currentYear),
            'student_id' => Student::factory(),
            'subject' => $this->faker->sentence(5),
            'description' => $this->faker->paragraph(2),
            'submission_date' => $submissionDate,
            'status' => $status,
            'response' => $status === ReclamationStatusEnum::RESOLVED || $status === ReclamationStatusEnum::CLOSED ? $this->faker->paragraph(1) : null,
            // La date de réponse doit être après ou égale à la date de soumission
            'response_date' => $status === ReclamationStatusEnum::RESOLVED || $status === ReclamationStatusEnum::CLOSED ? $this->faker->dateTimeBetween($submissionDate, 'now') : null,
            'admin_staff_id' => $this->faker->boolean(70) ? AdministrativeStaff::factory() : null,
            'reclaimable_type' => $reclaimableType,
            'reclaimable_id' => $reclaimableId,
        ];
    }
}