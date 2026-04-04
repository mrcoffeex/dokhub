<?php

use App\Models\Doctor;
use App\Models\User;

describe('edit page', function () {
    it('renders the profile page for an authenticated doctor', function () {
        actingAsDoctor();

        $this->get('/doctor/profile')
            ->assertOk()
            ->assertInertia(fn($page) => $page->component('Doctor/Profile'));
    });

    it('redirects unauthenticated users', function () {
        $this->get('/doctor/profile')->assertRedirect('/login');
    });
});

describe('profile update', function () {
    it('updates basic profile fields', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/profile', [
            'name'             => 'Dr. Updated Name',
            'slug'             => $doctor->slug,
            'phone'            => '+639999999999',
            'qualification'    => 'MD',
            'bio'              => 'Updated bio.',
            'experience_years' => 10,
            'consultation_fee' => 500,
            'location'         => 'Makati City',
            'specialization'   => ['Cardiology'],
            'insurance'        => [],
            'languages'        => ['English'],
            'appointment_modes' => ['in_person'],
        ])->assertRedirect();

        $this->assertDatabaseHas('doctors', [
            'id'   => $doctor->id,
            'name' => 'Dr. Updated Name',
            'phone'=> '+639999999999',
        ]);
    });

    it('syncs the linked user name when doctor name is updated', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/profile', [
            'name'             => 'New Doctor Name',
            'slug'             => $doctor->slug,
            'phone'            => null,
            'qualification'    => null,
            'bio'              => null,
            'experience_years' => 0,
            'consultation_fee' => 0,
            'location'         => null,
            'specialization'   => [],
            'insurance'        => [],
            'languages'        => [],
            'appointment_modes' => ['in_person'],
        ]);

        $this->assertDatabaseHas('users', [
            'id'   => $doctor->user_id,
            'name' => 'New Doctor Name',
        ]);
    });

    it('rejects a slug that contains uppercase letters', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/profile', [
            'name' => $doctor->name,
            'slug' => 'UPPERCASE-SLUG',
            'appointment_modes' => ['in_person'],
        ])->assertSessionHasErrors('slug');
    });

    it('rejects a duplicate slug from another doctor', function () {
        $doctor = actingAsDoctor();
        $other  = Doctor::factory()->approved()->create(['slug' => 'taken-slug']);

        $this->patch('/doctor/profile', [
            'name' => $doctor->name,
            'slug' => 'taken-slug',
            'appointment_modes' => ['in_person'],
        ])->assertSessionHasErrors('slug');
    });
});

describe('appointment_modes update', function () {
    it('saves a single mode', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/profile', [
            'name'              => $doctor->name,
            'slug'              => $doctor->slug,
            'appointment_modes' => ['online'],
        ])->assertRedirect();

        $this->assertDatabaseHas('doctors', ['id' => $doctor->id]);
        expect($doctor->fresh()->appointment_modes)->toBe(['online']);
    });

    it('saves both modes', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/profile', [
            'name'              => $doctor->name,
            'slug'              => $doctor->slug,
            'appointment_modes' => ['in_person', 'online'],
        ])->assertRedirect();

        expect($doctor->fresh()->appointment_modes)->toBe(['in_person', 'online']);
    });

    it('rejects invalid mode values', function () {
        $doctor = actingAsDoctor();

        $this->patch('/doctor/profile', [
            'name'              => $doctor->name,
            'slug'              => $doctor->slug,
            'appointment_modes' => ['telegram'],
        ])->assertSessionHasErrors('appointment_modes.0');
    });
});
