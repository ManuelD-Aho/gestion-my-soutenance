<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PenaltyStatusEnum;
use App\Models\AcademicYear;
use App\Models\AdministrativeStaff;
use App\Models\Penalty;
use App\Models\Student;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenaltyFactory extends Factory
{
    protected $model = Penalty::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');
        
        // Générer une date de création dans une plage réaliste
        $creationDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $isPaid = $this->faker->boolean(70);

        return [
            'penalty_id' => $uniqueIdGeneratorService->generate('PEN', $currentYear),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'type' => $this->faker->randomElement(['Financière', 'Administrative']),
            'amount' => $this->faker->randomFloat(2, 5000, 50000),
            'reason' => $this->faker->sentence(),
            'status' => $isPaid ? PenaltyStatusEnum::PAID : PenaltyStatusEnum::DUE,
            // La date de résolution doit être après ou égale à la date de création
            'resolution_date' => $isPaid ? $this->faker->dateTimeBetween($creationDate, 'now') : null,
            'creation_date' => $creationDate,
            'admin_staff_id' => AdministrativeStaff::factory(),
        ];
    }
}