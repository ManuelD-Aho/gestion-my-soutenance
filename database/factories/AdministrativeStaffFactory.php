<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Models\AdministrativeStaff;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdministrativeStaffFactory extends Factory
{
    protected $model = AdministrativeStaff::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');

        return [
            'staff_id' => $uniqueIdGeneratorService->generate('ADM', $currentYear),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'professional_phone' => $this->faker->phoneNumber(),
            'professional_email' => $this->faker->unique()->safeEmail(),
            'service_assignment_date' => $this->faker->date(),
            'key_responsibilities' => $this->faker->sentence(),
            'date_of_birth' => $this->faker->date(),
            'place_of_birth' => $this->faker->city(),
            'country_of_birth' => $this->faker->country(),
            'nationality' => $this->faker->countryCode(),
            'gender' => $this->faker->randomElement(GenderEnum::cases()),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'personal_phone' => $this->faker->phoneNumber(),
            'personal_secondary_email' => $this->faker->unique()->safeEmail(),
            'is_active' => $this->faker->boolean(95),
            'end_date' => $this->faker->boolean(5) ? $this->faker->dateTimeBetween('-5 years', 'now') : null,
            'user_id' => null, // Sera lié manuellement ou par le seeder
        ];
    }
}