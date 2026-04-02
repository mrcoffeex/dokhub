<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Insurance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceController extends Controller
{
    public function index(): Response
    {
        // Count how many doctors currently accept each insurance provider (stored as JSON array)
        $doctorCounts = DB::select(
            "SELECT jsonb_array_elements_text(insurance::jsonb) AS ins, COUNT(*) AS cnt
             FROM doctors
             WHERE insurance IS NOT NULL
             GROUP BY ins"
        );
        $countMap = collect($doctorCounts)->pluck('cnt', 'ins')->map(fn ($v) => (int) $v);

        $insurances = Insurance::orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($i) => array_merge($i->toArray(), [
                'doctors_count' => $countMap->get($i->name, 0),
            ]));

        return Inertia::render('Admin/Insurances/Index', [
            'insurances' => $insurances,
            'stats' => [
                'total'    => $insurances->count(),
                'active'   => $insurances->where('is_active', true)->count(),
                'inactive' => $insurances->where('is_active', false)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:insurances,name'],
        ]);

        $maxOrder = Insurance::max('sort_order') ?? -1;

        Insurance::create([
            'name'       => $validated['name'],
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', "Insurance \"{$validated['name']}\" added.");
    }

    public function update(Request $request, Insurance $insurance): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:100', 'unique:insurances,name,' . $insurance->id],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $insurance->update($validated);

        return back()->with('success', 'Insurance updated.');
    }

    public function destroy(Insurance $insurance): RedirectResponse
    {
        // Guard: prevent deletion if any doctor currently lists this insurance provider
        $inUse = DB::table('doctors')
            ->whereRaw("insurance::jsonb @> ?::jsonb", [json_encode([$insurance->name])])
            ->exists();

        if ($inUse) {
            return back()->withErrors([
                'delete' => "\"{$insurance->name}\" is accepted by one or more doctors and cannot be deleted. Deactivate it instead.",
            ]);
        }

        $insurance->delete();

        return back()->with('success', "Insurance \"{$insurance->name}\" deleted.");
    }
}
