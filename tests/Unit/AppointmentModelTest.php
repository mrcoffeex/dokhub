<?php

use App\Models\Appointment;
use App\Models\Doctor;

describe('Appointment::generateReference()', function () {
    it('returns a string matching the YEAR-XXXX-XXXX format', function () {
        $ref = Appointment::generateReference();
        expect($ref)->toMatch('/^\d{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/');
    });

    it('starts with the current year', function () {
        $ref = Appointment::generateReference();
        expect($ref)->toStartWith((string) now()->year);
    });

    it('generates unique references on repeated calls', function () {
        $refs = collect(range(1, 10))->map(fn() => Appointment::generateReference())->unique();
        expect($refs)->toHaveCount(10);
    });
});

describe('Appointment relationships', function () {
    it('belongs to a doctor', function () {
        $appointment = Appointment::factory()->create();
        expect($appointment->doctor)->toBeInstanceOf(Doctor::class);
    });
});

describe('Appointment casts', function () {
    it('casts appointment_date to a Carbon date', function () {
        $appointment = Appointment::factory()->create([
            'appointment_date' => '2026-06-15',
        ]);
        expect($appointment->appointment_date)->toBeInstanceOf(\DateTimeInterface::class);
        expect($appointment->appointment_date->format('Y-m-d'))->toBe('2026-06-15');
    });
});
