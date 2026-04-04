<?php

namespace App\Mail;

use App\Models\Doctor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Doctor $doctor
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your DokHub account has been approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.doctor-approved',
        );
    }
}
