<?php

use App\Http\Controllers\DoctorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/doctor-register', DoctorRegistrationController::class);
