<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\Doctor;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorInventoryMovementsController extends Controller
{
    use ResolvesCurrentDoctor;

    public function store(Request $request, InventoryItem $inventory)
    {
        $this->assertPermission($request, 'inventory.movements');
        $doctor = $this->getDoctor($request);
        abort_unless($inventory->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'type'     => ['required', 'in:restock,usage,adjustment,expired'],
            'quantity' => ['required', 'integer', 'min:1'],
            'note'     => ['nullable', 'string', 'max:500'],
        ]);

        // Determine sign: restock = positive, others = negative
        $delta = in_array($validated['type'], ['restock'])
            ? $validated['quantity']
            : -$validated['quantity'];

        DB::transaction(function () use ($inventory, $doctor, $validated, $delta) {
            InventoryMovement::create([
                'inventory_item_id' => $inventory->id,
                'doctor_id'         => $doctor->id,
                'type'              => $validated['type'],
                'quantity'          => $delta,
                'note'              => $validated['note'] ?? null,
            ]);

            $inventory->increment('current_stock', $delta);
        });

        return back()->with('success', 'Stock updated for ' . $inventory->name . '.');
    }
}
