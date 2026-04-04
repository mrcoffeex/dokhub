<?php

namespace App\Sms;

use App\Models\Appointment;

/**
 * SMS message bodies for appointment lifecycle events.
 * Each static method returns a plain-text string ready to send.
 */
class AppointmentSms
{
    public static function received(Appointment $appointment): string
    {
        $doctor = $appointment->doctor->name ?? 'your doctor';
        $date   = \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y');
        $time   = \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A');

        return "DokHub: Hi {$appointment->patient_name}, your appointment with Dr. {$doctor} on {$date} at {$time} has been received (Ref# {$appointment->reference}). We'll notify you once confirmed.";
    }

    public static function confirmed(Appointment $appointment): string
    {
        $doctor = $appointment->doctor->name ?? 'your doctor';
        $date   = \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y');
        $time   = \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A');

        return "DokHub: Hi {$appointment->patient_name}, your appointment with Dr. {$doctor} is CONFIRMED for {$date} at {$time} (Ref# {$appointment->reference}). Please arrive on time.";
    }

    public static function cancelled(Appointment $appointment): string
    {
        $doctor = $appointment->doctor->name ?? 'your doctor';
        $date   = \Carbon\Carbon::parse($appointment->appointment_date)->format('M j, Y');
        $reason = $appointment->cancellation_reason
            ? " Reason: {$appointment->cancellation_reason}."
            : '';

        return "DokHub: Hi {$appointment->patient_name}, your appointment with Dr. {$doctor} on {$date} (Ref# {$appointment->reference}) has been CANCELLED.{$reason} Please rebook if needed.";
    }

    public static function completed(Appointment $appointment): string
    {
        $doctor = $appointment->doctor->name ?? 'your doctor';

        return "DokHub: Hi {$appointment->patient_name}, your appointment with Dr. {$doctor} (Ref# {$appointment->reference}) is now completed. Thank you for choosing DokHub!";
    }
}
