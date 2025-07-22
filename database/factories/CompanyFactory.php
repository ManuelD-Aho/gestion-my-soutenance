<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');

        return [
            'company_id' => $uniqueIdGeneratorService->generate('COMP', $currentYear),
            'name' => $this->faker->unique()->company(),
            'activity_sector' => $this->faker->jobTitle(),
            'address' => $this->faker->address(),
            'contact_name' => $this->faker->name(),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
        ];
    }
}