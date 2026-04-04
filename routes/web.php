<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorReviewController;
use App\Http\Controllers\DoctorAppointmentsController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DoctorDashboardController;
use App\Http\Controllers\DoctorDiagnosisController;
use App\Http\Controllers\DoctorPatientsController;
use App\Http\Controllers\DoctorPatientVitalsController;
use App\Http\Controllers\DoctorInventoryController;
use App\Http\Controllers\DoctorInventoryMovementsController;
use App\Http\Controllers\DoctorPatientRecordsController;
use App\Http\Controllers\DoctorPatientTransferController;
use App\Http\Controllers\DoctorPrescriptionsController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\DoctorSubUsersController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\InsuranceController;
use App\Http\Controllers\Admin\PaymentLogsController;
use App\Http\Controllers\Admin\SystemLogsController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\SpecializationController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\DoctorBillingController;
use App\Http\Controllers\DoctorPosterController;
use App\Http\Controllers\DoctorAnalyticsController;
use App\Http\Controllers\PayMongoWebhookController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public landing / home
Route::get('/', function () {
    $avgRating = \App\Models\DoctorReview::approved()->avg('rating');

    return Inertia::render('Home', [
        'featuredSpecializations' => \App\Models\Specialization::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(8)
            ->pluck('name'),
        'stats' => [
            'doctors'          => \App\Models\Doctor::approved()->count(),
            'appointments'     => \App\Models\Appointment::count(),
            'specializations'  => \App\Models\Specialization::active()->count(),
            'avg_rating'       => $avgRating ? number_format((float) $avgRating, 1) : null,
        ],
    ]);
})->name('home');

// Legal pages
Route::inertia('/terms-of-service', 'legal/TermsOfService')->name('legal.terms');
Route::inertia('/privacy-policy', 'legal/PrivacyPolicy')->name('legal.privacy');

// Doctor registration routes
Route::prefix('auth/signup')->name('auth.signup.')->group(function () {
    Route::post('doctor', \App\Http\Controllers\DoctorRegistrationController::class)->name('doctor.register');
    Route::get('doctor', function () {
        return Inertia::render('auth/DoctorRegister', [
            'stats' => [
                'doctors'  => \App\Models\Doctor::where('status', 'approved')->count(),
                'patients' => \App\Models\Patient::count(),
                'rating'   => round(\App\Models\DoctorReview::where('is_approved', true)->avg('rating') ?? 0, 1),
            ],
            'specializations'  => \App\Models\Specialization::active()->orderBy('sort_order')->orderBy('name')->pluck('name'),
            'insurances'       => \App\Models\Insurance::active()->orderBy('sort_order')->orderBy('name')->pluck('name'),
            'googlePending'    => session('google_pending_doctor'),
        ]);
    })->name('doctor');
});

Route::inertia('/auth/doctor-signup-success', 'auth/DoctorSignupSuccess')->name('auth.doctor-success');

// PayMongo webhook — no auth, CSRF excluded in bootstrap/app.php
Route::post('paymongo/webhook', [PayMongoWebhookController::class, 'handle'])->name('paymongo.webhook');

// Google OAuth
Route::get('auth/google/{intent}', [SocialAuthController::class, 'redirect'])
    ->where('intent', 'login|doctor')
    ->name('auth.google.redirect');
Route::get('auth/google/callback', [SocialAuthController::class, 'callback'])->name('auth.google.callback');

// Public doctor listing & booking (no auth required)
Route::prefix('doctors')->name('doctors.')->group(function () {
    Route::get('/', [DoctorController::class, 'index'])->name('index');
    Route::get('/{doctor}', [DoctorController::class, 'show'])->name('show');
    Route::post('/{doctor}/book', [AppointmentController::class, 'store'])->name('book');
    Route::get('/{doctor}/book', fn($doctor) => redirect()->route('doctors.show', $doctor))->name('book.get');
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
        if ($user->role === 'sub_user') {
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
    Route::patch('doctor/profile/credentials', [DoctorProfileController::class, 'updateCredentials'])->name('doctor.profile.credentials');
    Route::post('doctor/profile/avatar', [DoctorProfileController::class, 'uploadAvatar'])->name('doctor.profile.avatar');
    Route::delete('doctor/profile/avatar', [DoctorProfileController::class, 'deleteAvatar'])->name('doctor.profile.avatar.delete');
    Route::get('doctor/schedule', [DoctorScheduleController::class, 'edit'])->name('doctor.schedule.edit');
    Route::patch('doctor/schedule', [DoctorScheduleController::class, 'update'])->name('doctor.schedule.update');

    // Billing
    Route::get('doctor/billing', [DoctorBillingController::class, 'index'])->name('doctor.billing');
    Route::post('doctor/billing/checkout', [DoctorBillingController::class, 'checkout'])->name('doctor.billing.checkout');
    Route::get('doctor/billing/return', [DoctorBillingController::class, 'checkoutReturn'])->name('doctor.billing.return');
    Route::get('doctor/poster', [DoctorPosterController::class, 'show'])->name('doctor.poster');

    // Analytics
    Route::get('doctor/analytics', [DoctorAnalyticsController::class, 'index'])->name('doctor.analytics');
    Route::get('doctor/analytics/export', [DoctorAnalyticsController::class, 'export'])->name('doctor.analytics.export');

    // Sub-users (owner only, enforced in controller)
    Route::get('doctor/sub-users', [DoctorSubUsersController::class, 'index'])->name('doctor.sub-users');
    Route::post('doctor/sub-users', [DoctorSubUsersController::class, 'store'])->name('doctor.sub-users.store');
    Route::patch('doctor/sub-users/{subUser}', [DoctorSubUsersController::class, 'update'])->name('doctor.sub-users.update');
    Route::patch('doctor/sub-users/{subUser}/toggle', [DoctorSubUsersController::class, 'toggleActive'])->name('doctor.sub-users.toggle');
    Route::delete('doctor/sub-users/{subUser}', [DoctorSubUsersController::class, 'destroy'])->name('doctor.sub-users.destroy');

    // Inventory
    Route::get('doctor/inventory', [DoctorInventoryController::class, 'index'])->name('doctor.inventory');
    Route::post('doctor/inventory', [DoctorInventoryController::class, 'store'])->name('doctor.inventory.store');
    Route::put('doctor/inventory/{inventory}', [DoctorInventoryController::class, 'update'])->name('doctor.inventory.update');
    Route::delete('doctor/inventory/{inventory}', [DoctorInventoryController::class, 'destroy'])->name('doctor.inventory.destroy');
    Route::post('doctor/inventory/{inventory}/movements', [DoctorInventoryMovementsController::class, 'store'])->name('doctor.inventory.movements.store');

    // Patients (Pro only)
    Route::middleware('pro')->group(function () {
        Route::get('doctor/patients', [DoctorPatientsController::class, 'index'])->name('doctor.patients');
        Route::get('doctor/patients/create', [DoctorPatientsController::class, 'create'])->name('doctor.patients.create');
        Route::post('doctor/patients', [DoctorPatientsController::class, 'store'])->name('doctor.patients.store');
        Route::get('doctor/patients/{patient}', [DoctorPatientsController::class, 'show'])->name('doctor.patients.show');
        Route::patch('doctor/patients/{patient}', [DoctorPatientsController::class, 'update'])->name('doctor.patients.update');

        // Diagnoses
        Route::post('doctor/patients/{patient}/diagnoses', [DoctorDiagnosisController::class, 'store'])->name('doctor.diagnoses.store');
        Route::put('doctor/patients/{patient}/diagnoses/{diagnosis}', [DoctorDiagnosisController::class, 'update'])->name('doctor.diagnoses.update');
        Route::delete('doctor/patients/{patient}/diagnoses/{diagnosis}', [DoctorDiagnosisController::class, 'destroy'])->name('doctor.diagnoses.destroy');

        // Vitals
        Route::post('doctor/patients/{patient}/vitals', [DoctorPatientVitalsController::class, 'store'])->name('doctor.vitals.store');
        Route::put('doctor/patients/{patient}/vitals/{vital}', [DoctorPatientVitalsController::class, 'update'])->name('doctor.vitals.update');
        Route::delete('doctor/patients/{patient}/vitals/{vital}', [DoctorPatientVitalsController::class, 'destroy'])->name('doctor.vitals.destroy');

        // Prescriptions
        Route::get('doctor/patients/{patient}/prescriptions/create', [DoctorPrescriptionsController::class, 'create'])->name('doctor.prescriptions.create');
        Route::post('doctor/patients/{patient}/prescriptions', [DoctorPrescriptionsController::class, 'store'])->name('doctor.prescriptions.store');
        Route::get('doctor/prescriptions/{prescription}', [DoctorPrescriptionsController::class, 'show'])->name('doctor.prescriptions.show');

        // Patient transfer
        Route::post('doctor/patients/{patient}/transfer', [DoctorPatientTransferController::class, 'store'])->name('doctor.patients.transfer');

        // Patient records
        Route::post('doctor/patients/{patient}/records', [DoctorPatientRecordsController::class, 'store'])->name('doctor.records.store');
        Route::delete('doctor/patients/{patient}/records/{record}', [DoctorPatientRecordsController::class, 'destroy'])->name('doctor.records.destroy');
        Route::get('doctor/patients/{patient}/records/{record}/download', [DoctorPatientRecordsController::class, 'download'])->name('doctor.records.download');
    });
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
            Route::get('/{doctor}/id-docs/{filename}', [Admin\DoctorController::class, 'serveIdDocument'])->name('id-docs.serve')->where('filename', '[^/]+');
        });

        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [Admin\AppointmentController::class, 'index'])->name('index');
            Route::patch('/{appointment}/status', [Admin\AppointmentController::class, 'updateStatus'])->name('update-status');
        });

        Route::get('/spam-detection', [Admin\SpamDetectionController::class, 'index'])->name('spam-detection');

        Route::get('/specializations', [SpecializationController::class, 'index'])->name('specializations.index');
        Route::post('/specializations', [SpecializationController::class, 'store'])->name('specializations.store');
        Route::patch('/specializations/{specialization}', [SpecializationController::class, 'update'])->name('specializations.update');
        Route::delete('/specializations/{specialization}', [SpecializationController::class, 'destroy'])->name('specializations.destroy');

        Route::get('/insurances', [InsuranceController::class, 'index'])->name('insurances.index');
        Route::post('/insurances', [InsuranceController::class, 'store'])->name('insurances.store');
        Route::patch('/insurances/{insurance}', [InsuranceController::class, 'update'])->name('insurances.update');
        Route::delete('/insurances/{insurance}', [InsuranceController::class, 'destroy'])->name('insurances.destroy');

        Route::get('/payment-logs', [PaymentLogsController::class, 'index'])->name('payment-logs.index');

        Route::get('/system-logs', [SystemLogsController::class, 'index'])->name('system-logs.index');

        Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
        Route::put('/pricing', [PricingController::class, 'update'])->name('pricing.update');

        // Analytics
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/export', [AdminAnalyticsController::class, 'export'])->name('analytics.export');

        Route::get('/profile', function (Request $request) {
            return Inertia::render('Admin/Profile', [
                'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
                'status' => $request->session()->get('status'),
            ]);
        })->name('profile');
    });

require __DIR__.'/settings.php';
