<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReportStatusEnum;
use App\Models\AcademicYear;
use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\Student;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');
        
        // Générer une date de soumission dans une plage réaliste
        $submissionDate = $this->faker->dateTimeBetween('-1 year', 'now');
        
        return [
            'report_id' => $uniqueIdGeneratorService->generate('RAP', $currentYear),
            'title' => $this->faker->sentence(6),
            'theme' => $this->faker->sentence(3),
            'abstract' => $this->faker->paragraph(3),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'status' => $this->faker->randomElement(ReportStatusEnum::cases()),
            'page_count' => $this->faker->numberBetween(30, 100),
            'submission_date' => $submissionDate,
            // last_modified_date doit être après ou égale à submission_date
            'last_modified_date' => $this->faker->dateTimeBetween($submissionDate, 'now'),
            'version' => $this->faker->numberBetween(1, 5),
            'report_template_id' => ReportTemplate::factory(),
        ];
    }
}