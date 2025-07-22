<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GenderEnum;
use App\Models\Student;
use App\Services\UniqueIdGeneratorService;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $uniqueIdGeneratorService = app(UniqueIdGeneratorService::class);
        $currentYear = (int) date('Y');

        return [
            'student_card_number' => $uniqueIdGeneratorService->generate('ETU', $currentYear),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email_contact_personnel' => $this->faker->unique()->safeEmail(),
            'date_of_birth' => $this->faker->date(),
            'place_of_birth' => $this->faker->city(),
            'country_of_birth' => $this->faker->country(),
            'nationality' => $this->faker->countryCode(),
            'gender' => $this->faker->randomElement(GenderEnum::cases()),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'secondary_email' => $this->faker->unique()->safeEmail(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'emergency_contact_relation' => $this->faker->word(),
            'is_active' => $this->faker->boolean(90),
            'end_date' => $this->faker->boolean(10) ? $this->faker->dateTimeBetween('-5 years', 'now') : null,
            'user_id' => null, // Sera lié manuellement ou par le seeder
        ];
    }
}