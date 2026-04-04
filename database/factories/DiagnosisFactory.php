<?php

namespace Database\Factories;

use App\Models\Diagnosis;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diagnosis>
 */
class DiagnosisFactory extends Factory
{
    protected $model = Diagnosis::class;

    public function definition(): array
    {
        return [
            'doctor_id'      => Doctor::factory(),
            'patient_id'     => Patient::factory(),
            'appointment_id' => null,
            'title'          => fake()->sentence(4),
            'symptoms'       => fake()->sentence(),
            'description'    => fake()->paragraph(),
            'treatment'      => fake()->sentence(),
            'follow_up_date' => null,
        ];
    }
}
