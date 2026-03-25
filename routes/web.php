<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorReviewController;
use App\Http\Controllers\DoctorAppointmentsController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\DoctorDiagnosisController;
use App\Http\Controllers\DoctorPatientsController;
use App\Http\Controllers\DoctorPrescriptionsController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DoctorRegistrationController;
use App\Http\Controllers\Admin;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Public landing / home
Route::inertia('/', 'Home')->name('home');

// Doctor registration routes
Route::prefix('auth/signup')->name('auth.signup.')->group(function () {
    Route::inertia('doctor', 'auth/DoctorRegister')->name('doctor');
});

Route::inertia('/auth/doctor-signup-success', 'auth/DoctorSignupSuccess')->name('auth.doctor-success');

// Public doctor listing & booking (no auth required)
Route::prefix('doctors')->name('doctors.')->group(function () {
    Route::get('/', [DoctorController::class, 'index'])->name('index');
    Route::get('/{doctor}', [DoctorController::class, 'show'])->name('show');
    Route::post('/{doctor}/book', [AppointmentController::class, 'store'])->name('book');
    Route::post('/{doctor}/reviews', [DoctorReviewController::class, 'store'])->name('reviews.store');
});

// Appointment lookup
Route::get('/my-appointment', fn() => inertia('Booking/Lookup'))->name('appointments.lookup');
Route::post('/my-appointment', [AppointmentController::class, 'lookup'])->name('appointments.lookup.search');

// Authenticated user dashboard (redirects based on role)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (Request $request) {
        $user = $request->user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if ($user->role === 'doctor') {
            return redirect()->route('doctor.dashboard');
        }
        return inertia('Dashboard');
    })->name('dashboard');

    Route::get('doctor-dashboard', DoctorDashboardController::class)->name('doctor.dashboard');
    Route::get('doctor/appointments', [DoctorAppointmentsController::class, 'index'])->name('doctor.appointments');
    Route::patch('doctor/appointments/{appointment}/status', [DoctorAppointmentsController::class, 'updateStatus'])->name('doctor.appointments.update-status');
    Route::post('doctor/appointments/{appointment}/add-patient', [DoctorAppointmentsController::class, 'addPatient'])->name('doctor.appointments.add-patient');
    Route::get('doctor/profile', [DoctorProfileController::class, 'edit'])->name('doctor.profile.edit');
    Route::patch('doctor/profile', [DoctorProfileController::class, 'update'])->name('doctor.profile.update');
    Route::post('doctor/profile/avatar', [DoctorProfileController::class, 'uploadAvatar'])->name('doctor.profile.avatar');
    Route::delete('doctor/profile/avatar', [DoctorProfileController::class, 'deleteAvatar'])->name('doctor.profile.avatar.delete');
    Route::get('doctor/schedule', [DoctorScheduleController::class, 'edit'])->name('doctor.schedule.edit');
    Route::patch('doctor/schedule', [DoctorScheduleController::class, 'update'])->name('doctor.schedule.update');

    // Patients
    Route::get('doctor/patients', [DoctorPatientsController::class, 'index'])->name('doctor.patients');
    Route::get('doctor/patients/{patient}', [DoctorPatientsController::class, 'show'])->name('doctor.patients.show');
    Route::patch('doctor/patients/{patient}', [DoctorPatientsController::class, 'update'])->name('doctor.patients.update');

    // Diagnoses
    Route::post('doctor/patients/{patient}/diagnoses', [DoctorDiagnosisController::class, 'store'])->name('doctor.diagnoses.store');
    Route::put('doctor/patients/{patient}/diagnoses/{diagnosis}', [DoctorDiagnosisController::class, 'update'])->name('doctor.diagnoses.update');
    Route::delete('doctor/patients/{patient}/diagnoses/{diagnosis}', [DoctorDiagnosisController::class, 'destroy'])->name('doctor.diagnoses.destroy');

    // Prescriptions
    Route::get('doctor/patients/{patient}/prescriptions/create', [DoctorPrescriptionsController::class, 'create'])->name('doctor.prescriptions.create');
    Route::post('doctor/patients/{patient}/prescriptions', [DoctorPrescriptionsController::class, 'store'])->name('doctor.prescriptions.store');
    Route::get('doctor/prescriptions/{prescription}', [DoctorPrescriptionsController::class, 'show'])->name('doctor.prescriptions.show');
});

// Admin routes
Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('doctors')->name('doctors.')->group(function () {
            Route::get('/', [Admin\DoctorController::class, 'index'])->name('index');
            Route::get('/create', [Admin\DoctorController::class, 'create'])->name('create');
            Route::post('/', [Admin\DoctorController::class, 'store'])->name('store');
            Route::get('/{doctor}/edit', [Admin\DoctorController::class, 'edit'])->name('edit');
            Route::put('/{doctor}', [Admin\DoctorController::class, 'update'])->name('update');
            Route::patch('/{doctor}/approve', [Admin\DoctorController::class, 'approve'])->name('approve');
            Route::patch('/{doctor}/suspend', [Admin\DoctorController::class, 'suspend'])->name('suspend');
            Route::post('/{doctor}/create-user-account', [Admin\DoctorController::class, 'createUserAccount'])->name('createUserAccount');
            Route::delete('/{doctor}', [Admin\DoctorController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [Admin\AppointmentController::class, 'index'])->name('index');
            Route::patch('/{appointment}/status', [Admin\AppointmentController::class, 'updateStatus'])->name('update-status');
        });

        Route::get('/spam-detection', [Admin\SpamDetectionController::class, 'index'])->name('spam-detection');

        Route::get('/settings', function (Request $request) {
            return Inertia::render('Admin/Settings', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => $request->session()->get('status'),
            ]);
        })->name('admin.settings');
    });

require __DIR__.'/settings.php';
