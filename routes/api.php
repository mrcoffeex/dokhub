<?php

use App\Http\Controllers\DoctorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/doctor-register', DoctorRegistrationController::class);

// Medicine autocomplete via NIH RxNorm API (no auth required, throttled)
Route::get('/medicines/search', function (\Illuminate\Http\Request $request) {
    $q = trim($request->string('q')->value());
    if (strlen($q) < 2) {
        return response()->json([]);
    }

    try {
        $response = \Illuminate\Support\Facades\Http::timeout(5)
            ->get('https://rxnav.nlm.nih.gov/REST/approximateTerm.json', [
                'term'       => $q,
                'maxEntries' => 12,
            ]);

        $candidates = $response->json('approximateGroup.candidate', []);

        $names = collect($candidates)
            ->pluck('name')
            ->unique()
            ->values()
            ->take(10);

        return response()->json($names);
    } catch (\Exception $e) {
        return response()->json([]);
    }
})->middleware('throttle:60,1');
