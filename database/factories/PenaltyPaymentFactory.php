<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdministrativeStaff;
use App\Models\Penalty;
use App\Models\PenaltyPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenaltyPaymentFactory extends Factory
{
    protected $model = PenaltyPayment::class;

    public function definition(): array
    {
        return [
            'penalty_id' => Penalty::factory(),
            'amount_paid' => $this->faker->randomFloat(2, 1000, 10000),
            'payment_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'payment_method' => $this->faker->randomElement(['Espèces', 'Virement', 'Chèque', 'Mobile Money']),
            'reference_number' => $this->faker->unique()->bothify('PAY-########'),
            'recorded_by_staff_id' => AdministrativeStaff::factory(),
        ];
    }
}