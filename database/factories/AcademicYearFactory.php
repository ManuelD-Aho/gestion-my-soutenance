<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AcademicYearStatusEnum;
use App\Models\AcademicYear;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    protected $model = AcademicYear::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $year = $this->faker->unique()->numberBetween(2020, 2030);
        $startDate = \Carbon\Carbon::createFromDate($year, 9, 1);
        $endDate = (clone $startDate)->addYear()->subDay();

        return [
            'academic_year_id' => $uniqueIdGeneratorService->generate('AY', $year),
            'label' => $year . '-' . ($year + 1),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => false, // Par défaut, le seeder principal activera une année
            'status' => AcademicYearStatusEnum::PLANNED,
            'report_submission_deadline' => (clone $endDate)->subMonth(),
        ];
    }
}