<?php

namespace App\Observers;

use App\Models\Doctor;

class DoctorObserver
{
    /**
     * Handle the Doctor "updated" event.
     */
    public function updated(Doctor $doctor): void
    {
        // If name or email changed, keep linked user record in sync
        if ($doctor->wasChanged('name') || $doctor->wasChanged('email')) {
            if ($doctor->user) {
                $attributes = [];
                if ($doctor->wasChanged('name')) {
                    $attributes['name'] = $doctor->name;
                }
                if ($doctor->wasChanged('email')) {
                    $attributes['email'] = $doctor->email;
                }
                if (!empty($attributes)) {
                    $doctor->user->update($attributes);
                }
            }
        }
    }
}
