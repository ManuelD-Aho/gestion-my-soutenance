<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicDecision;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\PaymentStatus;
use App\Models\Student;
use App\Models\StudyLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        // Générer une date d'inscription dans une plage réaliste
        $enrollmentDate = $this->faker->dateTimeBetween('-2 years', 'now');
        // La date de paiement doit être après ou égale à la date d'inscription
        $paymentDate = $this->faker->dateTimeBetween($enrollmentDate, (clone $enrollmentDate)->modify('+30 days'));

        return [
            'student_id' => Student::factory(),
            'study_level_id' => StudyLevel::inRandomOrder()->first()->id,
            'academic_year_id' => AcademicYear::inRandomOrder()->first()->id,
            'enrollment_amount' => $this->faker->randomFloat(2, 50000, 500000),
            'enrollment_date' => $enrollmentDate,
            'payment_status_id' => PaymentStatus::inRandomOrder()->first()->id,
            'payment_date' => $this->faker->boolean(90) ? $paymentDate : null,
            'payment_receipt_number' => $this->faker->unique()->bothify('REC-########'),
            'academic_decision_id' => $this->faker->boolean(70) ? AcademicDecision::inRandomOrder()->first()->id : null,
        ];
    }
}
