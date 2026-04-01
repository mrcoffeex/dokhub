<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SpecializationController extends Controller
{
    public function index(): Response
    {
        // Count how many doctors currently use each specialization name (stored as JSON array)
        $doctorCounts = DB::select(
            "SELECT jsonb_array_elements_text(specialization::jsonb) AS spec, COUNT(*) AS cnt
             FROM doctors
             GROUP BY spec"
        );
        $countMap = collect($doctorCounts)->pluck('cnt', 'spec')->map(fn ($v) => (int) $v);

        $specializations = Specialization::orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => array_merge($s->toArray(), [
                'doctors_count' => $countMap->get($s->name, 0),
            ]));

        return Inertia::render('Admin/Specializations/Index', [
            'specializations' => $specializations,
            'stats' => [
                'total'    => $specializations->count(),
                'active'   => $specializations->where('is_active', true)->count(),
                'inactive' => $specializations->where('is_active', false)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:specializations,name'],
        ]);

        $maxOrder = Specialization::max('sort_order') ?? -1;

        Specialization::create([
            'name'       => $validated['name'],
            'is_active'  => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', "Specialization \"{$validated['name']}\" added.");
    }

    public function update(Request $request, Specialization $specialization): RedirectResponse
    {
        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:100', 'unique:specializations,name,' . $specialization->id],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $specialization->update($validated);

        return back()->with('success', 'Specialization updated.');
    }

    public function destroy(Specialization $specialization): RedirectResponse
    {
        // Guard: prevent deletion if any doctor currently lists this specialization
        $inUse = DB::table('doctors')
            ->whereRaw("specialization::jsonb @> ?::jsonb", [json_encode([$specialization->name])])
            ->exists();

        if ($inUse) {
            return back()->withErrors([
                'delete' => "\"{$specialization->name}\" is assigned to one or more doctors and cannot be deleted. Deactivate it instead.",
            ]);
        }

        $specialization->delete();

        return back()->with('success', "Specialization \"{$specialization->name}\" deleted.");
    }
}
