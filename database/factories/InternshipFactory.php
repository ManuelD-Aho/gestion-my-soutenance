<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use App\Models\Internship;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InternshipFactory extends Factory
{
    protected $model = Internship::class;

    public function definition(): array
    {
        // Générer une date de début dans une plage valide
        $startDate = $this->faker->dateTimeBetween('-2 years', '-1 month'); // Début il y a 2 ans jusqu'à il y a 1 mois
        // Générer une date de fin après la date de début
        $endDate = $this->faker->dateTimeBetween($startDate, 'now'); // Fin entre la date de début et maintenant

        return [
            'student_id' => Student::factory(),
            'company_id' => Company::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'subject' => $this->faker->sentence(5),
            'company_tutor_name' => $this->faker->name(),
            'is_validated' => $this->faker->boolean(80),
            'validation_date' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween($endDate, 'now') : null,
            'validated_by_user_id' => User::factory(),
        ];
    }
}