<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportSectionFactory extends Factory
{
    protected $model = ReportSection::class;

    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(rand(2, 5), true),
            'order' => $this->faker->numberBetween(1, 10),
        ];
    }
}