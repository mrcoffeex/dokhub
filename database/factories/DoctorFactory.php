<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id'          => User::factory(),
            'name'             => $name,
            'slug'             => Str::slug($name) . '-' . fake()->unique()->randomNumber(4),
            'email'            => fake()->unique()->safeEmail(),
            'phone'            => fake()->phoneNumber(),
            'qualification'    => fake()->randomElement(['MD', 'MBBS', 'DO', 'PhD']),
            'bio'              => fake()->paragraph(),
            'specialization'   => ['General Practice'],
            'insurance'        => [],
            'languages'        => ['English'],
            'experience_years' => fake()->numberBetween(1, 30),
            'consultation_fee' => fake()->randomFloat(2, 100, 2000),
            'status'           => 'approved',
            'plan'             => 'free',
            'appointment_modes' => ['in_person', 'online'],
            'location'         => fake()->city(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }

    public function inTrial(): static
    {
        return $this->state([
            'plan'          => 'free',
            'trial_ends_at' => now()->addDays(10),
        ]);
    }

    public function pro(): static
    {
        return $this->state([
            'plan'           => 'pro',
            'pro_expires_at' => now()->addYear(),
        ]);
    }

    public function inPersonOnly(): static
    {
        return $this->state(['appointment_modes' => ['in_person']]);
    }

    public function onlineOnly(): static
    {
        return $this->state(['appointment_modes' => ['online']]);
    }
}
