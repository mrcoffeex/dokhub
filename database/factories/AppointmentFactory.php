<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'doctor_id'        => Doctor::factory(),
            'reference'        => Appointment::generateReference(),
            'appointment_type' => fake()->randomElement(['in_person', 'online']),
            'patient_name'     => fake()->name(),
            'patient_email'    => fake()->safeEmail(),
            'patient_phone'    => fake()->phoneNumber(),
            'appointment_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'appointment_time' => fake()->randomElement(['09:00', '10:00', '11:00', '14:00', '15:00']),
            'reason'           => fake()->sentence(),
            'status'           => 'pending',
        ];
    }

    public function confirmed(): static
    {
        return $this->state([
            'status'       => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status'               => 'cancelled',
            'cancellation_reason'  => fake()->sentence(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }

    public function inPerson(): static
    {
        return $this->state(['appointment_type' => 'in_person']);
    }

    public function online(): static
    {
        return $this->state(['appointment_type' => 'online']);
    }
}
