<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPosterController extends Controller
{
    use ResolvesCurrentDoctor;

    public function show(Request $request): Response
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        $doctor->load('schedules');

        return Inertia::render('Doctor/Poster', [
            'doctor' => $doctor,
        ]);
    }
}
