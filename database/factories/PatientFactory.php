<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'doctor_id'       => Doctor::factory(),
            'name'            => fake()->name(),
            'email'           => fake()->unique()->safeEmail(),
            'phone'           => fake()->phoneNumber(),
            'gender'          => fake()->randomElement(['male', 'female', 'other']),
            'date_of_birth'   => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'allergies'       => null,
            'medical_history' => null,
            'notes'           => null,
            'first_seen_at'   => now(),
        ];
    }
}
