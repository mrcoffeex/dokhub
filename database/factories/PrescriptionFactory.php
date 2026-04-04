<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'reference'    => 'RX-' . now()->year . '-' . str_pad(fake()->unique()->randomNumber(4), 4, '0', STR_PAD_LEFT),
            'doctor_id'    => Doctor::factory(),
            'patient_id'   => Patient::factory(),
            'diagnosis_id' => null,
            'medications'  => [
                [
                    'name'         => fake()->word() . 'cin',
                    'dosage'       => '500mg',
                    'frequency'    => 'Twice daily',
                    'duration'     => '7 days',
                    'instructions' => 'Take with food',
                ],
            ],
            'notes' => null,
        ];
    }
}
