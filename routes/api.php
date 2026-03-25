<?php

use App\Http\Controllers\DoctorRegistrationController;
use Illuminate\Support\Facades\Route;

Route::post('/doctor-register', DoctorRegistrationController::class);

// Medicine autocomplete — Philippine local DB first, RxNorm supplements if needed
Route::get('/medicines/search', function (\Illuminate\Http\Request $request) {
    $q = trim($request->string('q')->value());
    if (strlen($q) < 2) {
        return response()->json([]);
    }

    // 1. Query local medicines table (includes PH brand names — fast, no network)
    $local = \App\Models\Medicine::whereRaw('name ILIKE ?', ["%{$q}%"])
        ->orderByRaw(
            "CASE WHEN name ILIKE ? THEN 0 WHEN name ILIKE ? THEN 1 ELSE 2 END",
            [$q, $q . '%']
        )
        ->limit(12)
        ->get(['name', 'type']);

    $items = $local->map(fn ($m) => ['name' => $m->name, 'type' => $m->type])->toArray();
    $seen  = array_fill_keys(array_map('strtolower', array_column($items, 'name')), true);

    // 2. Supplement with RxNorm only when the local DB has fewer than 8 results
    if (count($items) < 8) {
        $cacheKey = 'med_rxnorm_v2_' . md5(strtolower($q));

        $rxnorm = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($q) {
            try {
                $responses = \Illuminate\Support\Facades\Http::pool(fn ($pool) => [
                    $pool->as('approx')->timeout(5)->get('https://rxnav.nlm.nih.gov/REST/approximateTerm.json', [
                        'term'       => $q,
                        'maxEntries' => 12,
                    ]),
                    $pool->as('drugs')->timeout(5)->get('https://rxnav.nlm.nih.gov/REST/drugs.json', [
                        'name' => $q,
                    ]),
                ]);

                $brandTtys = ['BN', 'SBD', 'BPCK'];
                $typeMap   = [];
                foreach ($responses['drugs']->json('drugGroup.conceptGroup', []) as $group) {
                    $type = in_array($group['tty'] ?? '', $brandTtys) ? 'brand' : 'generic';
                    foreach ($group['conceptProperties'] ?? [] as $concept) {
                        $typeMap[$concept['rxcui']] = ['name' => $concept['name'], 'type' => $type];
                    }
                }

                $remote = [];
                foreach ($responses['approx']->json('approximateGroup.candidate', []) as $c) {
                    $name = $c['name'] ?? '';
                    if (!$name) continue;
                    $type      = isset($typeMap[$c['rxcui'] ?? '']) ? $typeMap[$c['rxcui']]['type'] : 'generic';
                    $remote[]  = ['name' => $name, 'type' => $type];
                }
                foreach ($typeMap as $entry) {
                    if ($entry['type'] === 'brand') {
                        $remote[] = ['name' => $entry['name'], 'type' => 'brand'];
                    }
                }

                return $remote;
            } catch (\Exception $e) {
                return [];
            }
        });

        foreach ($rxnorm ?? [] as $entry) {
            $lower = strtolower($entry['name']);
            if (isset($seen[$lower])) continue;
            $seen[$lower] = true;
            $items[]      = $entry;
            if (count($items) >= 12) break;
        }
    }

    return response()->json(array_slice($items, 0, 12));
})->middleware('throttle:60,1');
