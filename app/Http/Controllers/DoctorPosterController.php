<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPosterController extends Controller
{
    public function show(Request $request): Response
    {
        $doctor = $request->user()->doctor;

        abort_unless($doctor, 403);

        $doctor->load('schedules');

        return Inertia::render('Doctor/Poster', [
            'doctor' => $doctor,
        ]);
    }
}
