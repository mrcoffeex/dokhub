<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@dokhub.com'],
            [
                'name'     => 'Admin',
                'email'    => 'admin@dokhub.com',
                'password' => Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        $doctors = [
            ['name' => 'Sarah Mitchell', 'specialization' => 'Cardiology',      'qualification' => 'MD, FACC',  'experience_years' => 12, 'consultation_fee' => 150, 'location' => 'New York, NY',      'bio' => 'Board-certified cardiologist specializing in preventive cardiology and heart failure management.'],
            ['name' => 'James Okafor',   'specialization' => 'Neurology',        'qualification' => 'MD, PhD',   'experience_years' => 15, 'consultation_fee' => 180, 'location' => 'Los Angeles, CA',   'bio' => 'Expert in neurological disorders including epilepsy, stroke, and movement disorders.'],
            ['name' => 'Priya Sharma',   'specialization' => 'Dermatology',      'qualification' => 'MD, FAAD',  'experience_years' => 8,  'consultation_fee' => 120, 'location' => 'Chicago, IL',       'bio' => 'Specializes in medical and cosmetic dermatology with a focus on skin cancer prevention.'],
            ['name' => 'Carlos Reyes',   'specialization' => 'Orthopedics',      'qualification' => 'MD, FAAOS', 'experience_years' => 10, 'consultation_fee' => 160, 'location' => 'Houston, TX',       'bio' => 'Sports medicine and orthopedic surgeon with expertise in minimally invasive procedures.'],
            ['name' => 'Emily Chen',     'specialization' => 'Pediatrics',       'qualification' => 'MD, FAAP',  'experience_years' => 9,  'consultation_fee' => 100, 'location' => 'Seattle, WA',       'bio' => 'Passionate about child health and development from newborn to adolescence.'],
            ['name' => 'David Park',     'specialization' => 'General Practice', 'qualification' => 'MD',        'experience_years' => 6,  'consultation_fee' => 80,  'location' => 'Boston, MA',        'bio' => 'Primary care physician dedicated to comprehensive family medicine and preventive care.'],
            ['name' => 'Amina Hassan',   'specialization' => 'Gynecology',       'qualification' => 'MD, FACOG', 'experience_years' => 11, 'consultation_fee' => 140, 'location' => 'Miami, FL',         'bio' => "Women's health specialist with expertise in minimally invasive gynecologic surgery."],
            ['name' => 'Robert Torres',  'specialization' => 'Psychiatry',       'qualification' => 'MD, FAPA',  'experience_years' => 14, 'consultation_fee' => 170, 'location' => 'San Francisco, CA', 'bio' => 'Specializes in mood disorders, anxiety, and adult psychiatry with evidence-based treatments.'],
        ];

        $days = [1, 2, 3, 4, 5];

        foreach ($doctors as $data) {
            $email = strtolower(str_replace(' ', '.', $data['name'])) . '@dokhub.com';
            $doctor = Doctor::firstOrCreate(
                ['email' => $email],
                [...$data, 'status' => 'approved', 'phone' => '+1-555-' . rand(1000, 9999), 'languages' => ['English']]
            );

            if ($doctor->schedules()->count() === 0) {
                foreach ($days as $day) {
                    $doctor->schedules()->create([
                        'day_of_week'           => $day,
                        'start_time'            => '09:00',
                        'end_time'              => '17:00',
                        'slot_duration_minutes' => 30,
                        'is_active'             => true,
                    ]);
                }
            }
        }

        Doctor::firstOrCreate(
            ['email' => 'pending.doctor@dokhub.com'],
            [
                'name'             => 'Michael Brown',
                'specialization'   => 'Radiology',
                'qualification'    => 'MD, FRCR',
                'experience_years' => 5,
                'consultation_fee' => 130,
                'location'         => 'Denver, CO',
                'bio'              => 'Diagnostic radiologist with expertise in MRI and CT imaging.',
                'status'           => 'pending',
                'phone'            => '+1-555-9999',
                'languages'        => ['English'],
            ]
        );
    }
}

