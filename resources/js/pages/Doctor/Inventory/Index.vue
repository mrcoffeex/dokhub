<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DoctorLayout from '@/layouts/DoctorLayout.vue';
import type { InventoryItem, InventoryCategory, InventoryMovementType, InventoryStats, PaginatedData } from '@/types';
import { toast } from 'vue-sonner';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps<{
    items: PaginatedData<InventoryItem>;
    stats: InventoryStats;
    filters: { search?: string; category?: string; stock_status?: string };
}>();

// ---- Filters ----
const searchInput = ref(props.filters.search ?? '');
const categoryFilter = ref(props.filters.category ?? 'all');
const stockFilter = ref(props.filters.stock_status ?? '');

const { can, isOwner } = usePermissions();

let searchDebounce: ReturnType<typeof setTimeout> | null = null;
function onSearch() {
    if (searchDebounce) clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
}

function applyFilters() {
    router.get('/doctor/inventory', {
        search: searchInput.value || undefined,
        category: categoryFilter.value !== 'all' ? categoryFilter.value : undefined,
        stock_status: stockFilter.value || undefined,
    }, { preserveScroll: true, replace: true });
}

// ---- Categories ----
const categories: { value: InventoryCategory | 'all'; label: string; color: string }[] = [
    { value: 'all',         label: 'All',          color: 'gray' },
    { value: 'medicine',    label: 'Medicine',     color: 'blue' },
    { value: 'equipment',   label: 'Equipment',    color: 'purple' },
    { value: 'supplies',    label: 'Supplies',     color: 'amber' },
    { value: 'consumables', label: 'Consumables',  color: 'emerald' },
    { value: 'other',       label: 'Other',        color: 'gray' },
];

const categoryColorMap: Record<string, string> = {
    medicine:    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    equipment:   'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    supplies:    'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    consumables: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    other:       'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
};

// ---- Stock status helpers ----
function stockStatus(item: InventoryItem): { label: string; cls: string } {
    if (item.current_stock <= 0)                                      return { label: 'Out of stock',  cls: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' };
    if (item.min_stock > 0 && item.current_stock <= item.min_stock)  return { label: 'Low stock',     cls: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' };
    return                                                                   { label: 'In stock',      cls: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' };
}

function expiryStatus(item: InventoryItem): { label: string; cls: string } | null {
    if (!item.expiry_date) return null;
    const d = new Date(item.expiry_date + 'T00:00:00');
    const now = new Date();
    if (d < now)                                        return { label: 'Expired',        cls: 'text-red-600 dark:text-red-400' };
    if (d <= new Date(now.getTime() + 30 * 86400000))  return { label: 'Exp. soon',       cls: 'text-amber-600 dark:text-amber-400' };
    return null;
}

function formatDate(d: string | null): string {
    if (!d) return '—';
    return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatCurrency(val: string | number | null): string {
    if (val === null || val === undefined || val === '') return '—';
    return '$' + Number(val).toFixed(2);
}

// ---- Add / Edit item modal ----
const showItemModal = ref(false);
const editingItemId = ref<number | null>(null);

const itemForm = useForm({
    name:          '',
    category:      'medicine' as InventoryCategory,
    sku:           '',
    unit:          'unit',
    description:   '',
    current_stock: 0,
    min_stock:     0,
    cost_price:    '' as string | number,
    selling_price: '' as string | number,
    expiry_date:   '',
});

function openAddModal() {
    editingItemId.value = null;
    itemForm.reset();
    itemForm.category = 'medicine';
    itemForm.unit = 'unit';
    showItemModal.value = true;
}

function openEditModal(item: InventoryItem) {
    editingItemId.value = item.id;
    itemForm.name          = item.name;
    itemForm.category      = item.category;
    itemForm.sku           = item.sku ?? '';
    itemForm.unit          = item.unit;
    itemForm.description   = item.description ?? '';
    itemForm.current_stock = item.current_stock;
    itemForm.min_stock     = item.min_stock;
    itemForm.cost_price    = item.cost_price ?? '';
    itemForm.selling_price = item.selling_price ?? '';
    itemForm.expiry_date   = item.expiry_date ?? '';
    showItemModal.value    = true;
}

function submitItem() {
    if (editingItemId.value) {
        itemForm.put(`/doctor/inventory/${editingItemId.value}`, {
            preserveScroll: true,
            onSuccess: () => {
                showItemModal.value = false;
                toast.success('Item updated', { duration: 3000 });
            },
            onError: () => toast.error('Could not update item', { duration: 4000 }),
        });
    } else {
        itemForm.post('/doctor/inventory', {
            preserveScroll: true,
            onSuccess: () => {
                showItemModal.value = false;
                toast.success('Item added to inventory', { duration: 3000 });
            },
            onError: () => toast.error('Could not add item', { duration: 4000 }),
        });
    }
}

function deleteItem(item: InventoryItem) {
    if (!confirm(`Remove "${item.name}" from inventory?`)) return;
    router.delete(`/doctor/inventory/${item.id}`, {
        preserveScroll: true,
        onSuccess: () => toast.success('Item removed', { duration: 3000 }),
        onError:   () => toast.error('Could not remove item', { duration: 4000 }),
    });
}

// ---- Adjust stock modal ----
const adjustItem = ref<InventoryItem | null>(null);
const movementForm = useForm({
    type:     'restock' as InventoryMovementType,
    quantity: 1,
    note:     '',
});

const movementTypes: { value: InventoryMovementType; label: string; delta: '+' | '-' }[] = [
    { value: 'restock',    label: 'Restock (add)',   delta: '+' },
    { value: 'usage',      label: 'Usage (deduct)',  delta: '-' },
    { value: 'adjustment', label: 'Adjustment',      delta: '-' },
    { value: 'expired',    label: 'Expired/Disposed',delta: '-' },
];

function openAdjustModal(item: InventoryItem) {
    adjustItem.value = item;
    movementForm.reset();
    movementForm.type = 'restock';
    movementForm.quantity = 1;
}

function submitMovement() {
    if (!adjustItem.value) return;
    movementForm.post(`/doctor/inventory/${adjustItem.value.id}/movements`, {
        preserveScroll: true,
        onSuccess: () => {
            adjustItem.value = null;
            toast.success('Stock updated', { duration: 3000 });
        },
        onError: () => toast.error('Could not update stock', { duration: 4000 }),
    });
}

const totalCostValue = computed(() => {
    const v = Number(props.stats.total_cost_value);
    return isNaN(v) ? '0.00' : v.toFixed(2);
});
</script>

<template>
    <Head title="Inventory" />
    <DoctorLayout>
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Inventory</h1>
        </template>

        <!-- Stats row -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Total Items</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</p>
            </div>
            <div
                class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
                :class="stats.out_of_stock ? 'border-red-100 bg-red-50 dark:border-red-900/30 dark:bg-red-900/20' : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900'"
                @click="stockFilter = stockFilter === 'out' ? '' : 'out'; applyFilters()"
            >
                <p class="text-xs font-medium" :class="stats.out_of_stock ? 'text-red-500' : 'text-gray-400 dark:text-gray-500'">Out of Stock</p>
                <p class="mt-1 text-2xl font-bold" :class="stats.out_of_stock ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">{{ stats.out_of_stock }}</p>
            </div>
            <div
                class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
                :class="stats.low_stock ? 'border-amber-100 bg-amber-50 dark:border-amber-900/30 dark:bg-amber-900/20' : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900'"
                @click="stockFilter = stockFilter === 'low' ? '' : 'low'; applyFilters()"
            >
                <p class="text-xs font-medium" :class="stats.low_stock ? 'text-amber-500' : 'text-gray-400 dark:text-gray-500'">Low Stock</p>
                <p class="mt-1 text-2xl font-bold" :class="stats.low_stock ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white'">{{ stats.low_stock }}</p>
            </div>
            <div
                class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
                :class="stats.expiring_soon ? 'border-orange-100 bg-orange-50 dark:border-orange-900/30 dark:bg-orange-900/20' : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900'"
                @click="stockFilter = stockFilter === 'expiring' ? '' : 'expiring'; applyFilters()"
            >
                <p class="text-xs font-medium" :class="stats.expiring_soon ? 'text-orange-500' : 'text-gray-400 dark:text-gray-500'">Expiring Soon</p>
                <p class="mt-1 text-2xl font-bold" :class="stats.expiring_soon ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white'">{{ stats.expiring_soon }}</p>
            </div>
            <div
                class="cursor-pointer rounded-2xl border p-4 shadow-sm transition hover:shadow-md"
                :class="stats.expired ? 'border-red-100 bg-red-50 dark:border-red-900/30 dark:bg-red-900/20' : 'border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900'"
                @click="stockFilter = stockFilter === 'expired' ? '' : 'expired'; applyFilters()"
            >
                <p class="text-xs font-medium" :class="stats.expired ? 'text-red-500' : 'text-gray-400 dark:text-gray-500'">Expired</p>
                <p class="mt-1 text-2xl font-bold" :class="stats.expired ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">{{ stats.expired }}</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500">Stock Value</p>
                <p class="mt-1 text-2xl font-bold text-indigo-600 dark:text-indigo-400">${{ totalCostValue }}</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <!-- Search + category filter -->
            <div class="flex flex-1 flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative max-w-xs flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="searchInput"
                        @input="onSearch"
                        type="search"
                        placeholder="Search items, SKU…"
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-orange-400 focus:ring-1 focus:ring-orange-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                    />
                </div>

                <!-- Category pills -->
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="cat in categories"
                        :key="cat.value"
                        @click="categoryFilter = cat.value; applyFilters()"
                        class="rounded-full px-3 py-1 text-xs font-semibold transition"
                        :class="categoryFilter === cat.value
                            ? 'bg-orange-600 text-white'
                            : 'bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700'"
                    >
                        {{ cat.label }}
                    </button>
                </div>

                <button
                    v-if="stockFilter"
                    @click="stockFilter = ''; applyFilters()"
                    class="flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300"
                >
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ stockFilter }} filter
                </button>
            </div>

            <button
                v-if="isOwner"
                @click="openAddModal"
                class="flex shrink-0 items-center gap-2 rounded-xl bg-orange-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-700"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Item
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <!-- Empty state -->
            <div v-if="items.data.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="mt-3 text-sm font-medium text-gray-600 dark:text-gray-400">No inventory items found</p>
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Add your first item to get started</p>
                <button v-if="isOwner" @click="openAddModal" class="mt-4 rounded-xl bg-orange-600 px-5 py-2 text-sm font-semibold text-white hover:bg-orange-700">
                    Add First Item
                </button>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Item</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:table-cell">Category</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stock</th>
                        <th class="hidden px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 md:table-cell">Min</th>
                        <th class="hidden px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 lg:table-cell">Expiry</th>
                        <th class="hidden px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 lg:table-cell">Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    <tr
                        v-for="item in items.data"
                        :key="item.id"
                        class="group transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                    >
                        <!-- Name + SKU + status -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">{{ item.name }}</p>
                                    <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                        <span v-if="item.sku" class="text-xs text-gray-400 dark:text-gray-500">{{ item.sku }}</span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="stockStatus(item).cls">
                                            {{ stockStatus(item).label }}
                                        </span>
                                        <span v-if="expiryStatus(item)" class="text-[10px] font-semibold" :class="expiryStatus(item)!.cls">
                                            {{ expiryStatus(item)!.label }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <!-- Category (sm+) -->
                        <td class="hidden px-4 py-3 sm:table-cell">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize" :class="categoryColorMap[item.category]">
                                {{ item.category }}
                            </span>
                        </td>
                        <!-- Stock -->
                        <td class="px-4 py-3 text-center">
                            <p class="text-sm font-bold" :class="item.current_stock <= 0 ? 'text-red-600 dark:text-red-400' : item.min_stock > 0 && item.current_stock <= item.min_stock ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-gray-100'">
                                {{ item.current_stock }}
                            </p>
                            <p class="text-[10px] text-gray-400">{{ item.unit }}</p>
                        </td>
                        <!-- Min threshold (md+) -->
                        <td class="hidden px-4 py-3 text-center md:table-cell">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.min_stock }}</p>
                        </td>
                        <!-- Expiry (lg+) -->
                        <td class="hidden px-4 py-3 lg:table-cell">
                            <p v-if="item.expiry_date" class="text-xs" :class="expiryStatus(item)?.cls ?? 'text-gray-500 dark:text-gray-400'">
                                {{ formatDate(item.expiry_date) }}
                            </p>
                            <p v-else class="text-xs text-gray-300 dark:text-gray-600">—</p>
                        </td>
                        <!-- Cost (lg+) -->
                        <td class="hidden px-4 py-3 text-right lg:table-cell">
                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ formatCurrency(item.cost_price) }}</p>
                        </td>
                        <!-- Actions -->
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <button
                                    v-if="can('inventory.movements')"
                                    @click="openAdjustModal(item)"
                                    title="Adjust stock"
                                    class="flex items-center gap-1 rounded-lg border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                    <span class="hidden sm:inline">Stock</span>
                                </button>
                                <button
                                    v-if="isOwner"
                                    @click="openEditModal(item)"
                                    title="Edit"
                                    class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-orange-300 hover:text-orange-600 dark:border-gray-700"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    v-if="isOwner"
                                    @click="deleteItem(item)"
                                    title="Delete"
                                    class="rounded-lg border border-gray-200 p-1.5 text-gray-400 transition hover:border-red-300 hover:text-red-500 dark:border-gray-700"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="items.last_page > 1" class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <p class="text-xs">Showing {{ items.from }}–{{ items.to }} of {{ items.total }}</p>
            <div class="flex gap-1">
                <template v-for="link in items.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                        :class="link.active
                            ? 'border-orange-300 bg-orange-50 text-orange-700 dark:border-orange-700 dark:bg-orange-900/20 dark:text-orange-300'
                            : 'border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800'"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </DoctorLayout>

    <!-- Add / Edit Item modal -->
    <Teleport to="body">
        <div
            v-if="showItemModal"
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 pt-12"
            @click.self="showItemModal = false"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">
                        {{ editingItemId ? 'Edit Item' : 'Add Inventory Item' }}
                    </h2>
                    <button @click="showItemModal = false" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitItem" class="space-y-4 p-6">
                    <!-- Name -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Name <span class="text-red-500">*</span></label>
                        <input
                            v-model="itemForm.name"
                            type="text"
                            placeholder="e.g. Amoxicillin 500mg"
                            class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                            :class="itemForm.errors.name ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100'"
                        />
                        <p v-if="itemForm.errors.name" class="mt-1 text-xs text-red-500">{{ itemForm.errors.name }}</p>
                    </div>

                    <!-- Category + Unit -->
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Category <span class="text-red-500">*</span></label>
                            <select
                                v-model="itemForm.category"
                                class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                :class="itemForm.errors.category ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100'"
                            >
                                <option value="medicine">Medicine</option>
                                <option value="equipment">Equipment</option>
                                <option value="supplies">Supplies</option>
                                <option value="consumables">Consumables</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Unit <span class="text-red-500">*</span></label>
                            <input
                                v-model="itemForm.unit"
                                type="text"
                                placeholder="e.g. tablets, vials, boxes"
                                class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                :class="itemForm.errors.unit ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100'"
                            />
                        </div>
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">SKU / Barcode</label>
                        <input
                            v-model="itemForm.sku"
                            type="text"
                            placeholder="Optional"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        />
                    </div>

                    <!-- Stock -->
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div v-if="!editingItemId">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Initial Stock <span class="text-red-500">*</span></label>
                            <input
                                v-model.number="itemForm.current_stock"
                                type="number"
                                min="0"
                                class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                :class="itemForm.errors.current_stock ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100'"
                            />
                            <p v-if="itemForm.errors.current_stock" class="mt-1 text-xs text-red-500">{{ itemForm.errors.current_stock }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Low-stock Threshold</label>
                            <input
                                v-model.number="itemForm.min_stock"
                                type="number"
                                min="0"
                                class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                                :class="itemForm.errors.min_stock ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100'"
                            />
                        </div>
                    </div>

                    <!-- Prices -->
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Cost Price</label>
                            <input
                                v-model="itemForm.cost_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Selling Price</label>
                            <input
                                v-model="itemForm.selling_price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                            />
                        </div>
                    </div>

                    <!-- Expiry date -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Expiry Date</label>
                        <input
                            v-model="itemForm.expiry_date"
                            type="date"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        />
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Description</label>
                        <textarea
                            v-model="itemForm.description"
                            rows="2"
                            placeholder="Optional notes…"
                            class="w-full resize-none rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        />
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button
                            type="submit"
                            :disabled="itemForm.processing"
                            class="flex-1 rounded-xl bg-orange-600 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                        >
                            {{ itemForm.processing ? 'Saving…' : (editingItemId ? 'Update Item' : 'Add Item') }}
                        </button>
                        <button
                            type="button"
                            @click="showItemModal = false"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>

    <!-- Adjust Stock modal -->
    <Teleport to="body">
        <div
            v-if="adjustItem"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="adjustItem = null"
        >
            <div class="w-full max-w-sm rounded-2xl bg-white shadow-xl dark:bg-gray-900">
                <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-800">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white">Adjust Stock</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">{{ adjustItem.name }} · current: <span class="font-semibold">{{ adjustItem.current_stock }} {{ adjustItem.unit }}</span></p>
                    </div>
                    <button @click="adjustItem = null" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitMovement" class="space-y-4 p-6">
                    <!-- Type -->
                    <div class="grid grid-cols-2 gap-2">
                        <label
                            v-for="mt in movementTypes"
                            :key="mt.value"
                            class="flex cursor-pointer items-center gap-2 rounded-xl border px-3 py-2.5 text-sm transition"
                            :class="movementForm.type === mt.value
                                ? (mt.delta === '+' ? 'border-emerald-400 bg-emerald-50 dark:border-emerald-600 dark:bg-emerald-900/20' : 'border-red-300 bg-red-50 dark:border-red-700 dark:bg-red-900/20')
                                : 'border-gray-100 hover:border-gray-200 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800'"
                        >
                            <input type="radio" :value="mt.value" v-model="movementForm.type" class="sr-only" />
                            <span
                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold"
                                :class="mt.delta === '+' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'"
                            >{{ mt.delta }}</span>
                            <span class="text-xs font-medium leading-tight" :class="movementForm.type === mt.value ? (mt.delta === '+' ? 'text-emerald-800 dark:text-emerald-200' : 'text-red-700 dark:text-red-300') : 'text-gray-700 dark:text-gray-300'">{{ mt.label }}</span>
                        </label>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Quantity <span class="text-red-500">*</span></label>
                        <input
                            v-model.number="movementForm.quantity"
                            type="number"
                            min="1"
                            class="w-full rounded-xl border px-3 py-2 text-sm outline-none"
                            :class="movementForm.errors.quantity ? 'border-red-400' : 'border-gray-200 focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100'"
                        />
                        <p v-if="movementForm.errors.quantity" class="mt-1 text-xs text-red-500">{{ movementForm.errors.quantity }}</p>
                    </div>

                    <!-- Note -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Note</label>
                        <input
                            v-model="movementForm.note"
                            type="text"
                            placeholder="Optional reason…"
                            class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-orange-400 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        />
                    </div>

                    <div class="flex gap-2 pt-1">
                        <button
                            type="submit"
                            :disabled="movementForm.processing"
                            class="flex-1 rounded-xl bg-orange-600 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-700 disabled:opacity-50"
                        >
                            {{ movementForm.processing ? 'Updating…' : 'Update Stock' }}
                        </button>
                        <button
                            type="button"
                            @click="adjustItem = null"
                            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
