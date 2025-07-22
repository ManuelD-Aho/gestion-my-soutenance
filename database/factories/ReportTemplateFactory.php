<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReportTemplate;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportTemplateFactory extends Factory
{
    protected $model = ReportTemplate::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');

        return [
            'template_id' => $uniqueIdGeneratorService->generate('TPL', $currentYear),
            'name' => $this->faker->unique()->word() . ' Template',
            'description' => $this->faker->sentence(),
            'version' => $this->faker->randomFloat(1, 1.0, 2.0),
            'status' => $this->faker->randomElement(['Active', 'Draft', 'Archived']),
        ];
    }
}