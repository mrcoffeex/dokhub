<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesCurrentDoctor;
use App\Models\Doctor;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DoctorInventoryController extends Controller
{
    use ResolvesCurrentDoctor;

    public function index(Request $request): Response
    {
        $doctor = $this->getDoctor($request);

        $query = InventoryItem::where('doctor_id', $doctor->id);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$term}%")
                ->orWhere('sku', 'ilike', "%{$term}%")
                ->orWhere('category', 'ilike', "%{$term}%")
            );
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'low'     => $query->whereColumn('current_stock', '<=', 'min_stock')->where('min_stock', '>', 0),
                'out'     => $query->where('current_stock', '<=', 0),
                'expiring' => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays(30))->whereDate('expiry_date', '>=', now()),
                'expired' => $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now()),
                default   => null,
            };
        }

        $items = $query->latest()->paginate(20)->withQueryString();

        // Stats
        $allItems = InventoryItem::where('doctor_id', $doctor->id);
        $stats = [
            'total'          => (clone $allItems)->count(),
            'out_of_stock'   => (clone $allItems)->where('current_stock', '<=', 0)->count(),
            'low_stock'      => (clone $allItems)->whereColumn('current_stock', '<=', 'min_stock')->where('min_stock', '>', 0)->where('current_stock', '>', 0)->count(),
            'expiring_soon'  => (clone $allItems)->whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays(30))->whereDate('expiry_date', '>=', now())->count(),
            'expired'        => (clone $allItems)->whereNotNull('expiry_date')->whereDate('expiry_date', '<', now())->count(),
            'total_cost_value' => (clone $allItems)->selectRaw('COALESCE(SUM(current_stock * cost_price), 0) as val')->value('val'),
        ];

        return Inertia::render('Doctor/Inventory/Index', [
            'items'   => $items,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'category', 'stock_status']),
        ]);
    }

    public function store(Request $request)
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:200'],
            'category'      => ['required', 'in:medicine,equipment,supplies,consumables,other'],
            'sku'           => ['nullable', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:50'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'min_stock'     => ['required', 'integer', 'min:0'],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'expiry_date'   => ['nullable', 'date'],
        ]);

        $item = InventoryItem::create([...$validated, 'doctor_id' => $doctor->id]);

        // Record initial stock as a movement
        if ($item->current_stock > 0) {
            InventoryMovement::create([
                'inventory_item_id' => $item->id,
                'doctor_id'         => $doctor->id,
                'type'              => 'restock',
                'quantity'          => $item->current_stock,
                'note'              => 'Initial stock',
            ]);
        }

        return back()->with('success', "{$item->name} added to inventory.");
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);
        abort_unless($inventory->doctor_id === $doctor->id, 403);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:200'],
            'category'      => ['required', 'in:medicine,equipment,supplies,consumables,other'],
            'sku'           => ['nullable', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:50'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'min_stock'     => ['required', 'integer', 'min:0'],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'expiry_date'   => ['nullable', 'date'],
        ]);

        $inventory->update($validated);

        return back()->with('success', "{$inventory->name} updated.");
    }

    public function destroy(Request $request, InventoryItem $inventory)
    {
        $this->assertOwner($request);
        $doctor = $this->getDoctor($request);
        abort_unless($inventory->doctor_id === $doctor->id, 403);

        $name = $inventory->name;
        $inventory->delete();

        return back()->with('success', "{$name} removed from inventory.");
    }

    public function movements(Request $request, InventoryItem $inventory): Response
    {
        $doctor = $this->getDoctor($request);
        abort_unless($inventory->doctor_id === $doctor->id, 403);

        $movements = $inventory->movements()->latest()->paginate(30);

        return Inertia::render('Doctor/Inventory/Movements', [
            'item'      => $inventory,
            'movements' => $movements,
        ]);
    }
}
