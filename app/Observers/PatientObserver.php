<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\Appointment;

class PatientObserver
{
    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        $oldEmail = $patient->getOriginal('email');
        $oldName  = $patient->getOriginal('name');
        $newEmail = $patient->email;
        $newName  = $patient->name;

        // If email changed, update appointment records that referenced the old email
        if ($oldEmail && $newEmail && $newEmail !== $oldEmail) {
            Appointment::where('doctor_id', $patient->doctor_id)
                ->where('patient_email', $oldEmail)
                ->update(['patient_email' => $newEmail, 'patient_name' => $newName]);

            return;
        }

        // If only the name changed, update appointment names for matching email
        if ($oldEmail && $newName !== $oldName) {
            Appointment::where('doctor_id', $patient->doctor_id)
                ->where('patient_email', $oldEmail)
                ->update(['patient_name' => $newName]);

            return;
        }

        // Fallback: if no email existed previously, try matching by name
        if (!$oldEmail && $oldName && $newName !== $oldName) {
            Appointment::where('doctor_id', $patient->doctor_id)
                ->where('patient_name', $oldName)
                ->update(['patient_name' => $newName]);
        }
    }
}
