<?php

use App\Models\DoctorSchedule;

function sevenDaySchedule(array $overrides = []): array
{
    return collect(range(0, 6))->map(fn($day) => array_merge([
        'day_of_week'           => $day,
        'is_active'             => $day >= 1 && $day <= 5, // Mon–Fri active
        'start_time'            => '09:00',
        'end_time'              => '17:00',
        'slot_duration_minutes' => 30,
    ], $overrides))->all();
}

describe('schedule view', function () {
    it('renders the schedule page for an authenticated doctor', function () {
        actingAsDoctor();

        $this->get('/doctor/schedule')
            ->assertOk()
            ->assertInertia(fn($page) => $page->component('Doctor/Schedule'));
    });

    it('redirects unauthenticated users', function () {
        $this->get('/doctor/schedule')->assertRedirect('/login');
    });
});

describe('schedule update', function () {
    it('saves a full 7-day schedule', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/schedule', ['schedules' => sevenDaySchedule()])
            ->assertRedirect();

        expect(DoctorSchedule::where('doctor_id', $doctor->id)->count())->toBe(7);
    });

    it('replaces existing schedules on re-save', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/schedule', ['schedules' => sevenDaySchedule()]);
        $this->patch('/doctor/schedule', ['schedules' => sevenDaySchedule(['slot_duration_minutes' => 60])]);

        expect(DoctorSchedule::where('doctor_id', $doctor->id)->count())->toBe(7);
        expect(DoctorSchedule::where('doctor_id', $doctor->id)->first()->slot_duration_minutes)->toBe(60);
    });

    it('rejects fewer than 7 days', function () {
        actingAsDoctor();

        $partial = array_slice(sevenDaySchedule(), 0, 5);

        $this->patch('/doctor/schedule', ['schedules' => $partial])
            ->assertSessionHasErrors('schedules');
    });

    it('rejects an invalid slot duration', function () {
        actingAsDoctor();

        $schedule = sevenDaySchedule(['slot_duration_minutes' => 999]);

        $this->patch('/doctor/schedule', ['schedules' => $schedule])
            ->assertSessionHasErrors('schedules.0.slot_duration_minutes');
    });

    it('rejects end_time before start_time', function () {
        actingAsDoctor();

        $schedule = sevenDaySchedule([
            'start_time' => '17:00',
            'end_time'   => '09:00',
        ]);

        $this->patch('/doctor/schedule', ['schedules' => $schedule])
            ->assertSessionHasErrors();
    });
});
