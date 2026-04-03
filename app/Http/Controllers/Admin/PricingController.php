<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    public function index(): Response
    {
        $pricing = PricingSetting::current()->load('editor');

        return Inertia::render('Admin/Pricing/Index', [
            'pricing' => [
                'monthly_price_cents' => $pricing->monthly_price_cents,
                'annual_price_cents'  => $pricing->annual_price_cents,
                'annual_savings_cents' => $pricing->annualSavingsCents(),
                'updated_at'          => $pricing->updated_at?->toDateTimeString(),
                'updated_by'          => $pricing->editor?->name,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'monthly_price' => ['required', 'numeric', 'min:1', 'max:999999'],
            'annual_price'  => ['required', 'numeric', 'min:1', 'max:9999999'],
        ]);

        $monthly = (int) round($validated['monthly_price'] * 100);
        $annual  = (int) round($validated['annual_price']  * 100);

        PricingSetting::where('id', 1)->update([
            'monthly_price_cents' => $monthly,
            'annual_price_cents'  => $annual,
            'updated_by'          => $request->user()->id,
            'updated_at'          => now(),
        ]);

        return back()->with('success', 'Pricing updated successfully.');
    }
}
