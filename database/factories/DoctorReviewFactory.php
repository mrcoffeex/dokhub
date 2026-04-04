<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\DoctorReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorReview>
 */
class DoctorReviewFactory extends Factory
{
    protected $model = DoctorReview::class;

    public function definition(): array
    {
        return [
            'doctor_id'     => Doctor::factory(),
            'patient_name'  => fake()->name(),
            'patient_email' => fake()->unique()->safeEmail(),
            'rating'        => fake()->numberBetween(1, 5),
            'comment'       => fake()->paragraph(),
            'is_approved'   => true,
        ];
    }
}
