<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Default center: Manila, Philippines
const DEFAULT_LAT = 14.5995;
const DEFAULT_LNG = 120.9842;

const props = defineProps<{
    lat: number | null;
    lng: number | null;
}>();

const emit = defineEmits<{
    (e: 'update:lat', value: number | null): void;
    (e: 'update:lng', value: number | null): void;
}>();

const mapEl = ref<HTMLDivElement | null>(null);
const searchQuery = ref('');
const searchError = ref('');
const isSearching = ref(false);
const isLocating = ref(false);
const locationError = ref('');
const pinnedAddress = ref('');

let map: L.Map | null = null;
let marker: L.Marker | null = null;
let accuracyCircle: L.Circle | null = null;

// Custom violet teardrop pin icon
function makePinIcon(pulse = false) {
    return L.divIcon({
        html: `
            <div style="position:relative;width:32px;height:40px">
                ${pulse ? `<span style="position:absolute;top:4px;left:4px;width:24px;height:24px;border-radius:50%;background:rgba(124,58,237,.35);animation:pin-pulse 1.4s ease-out infinite"></span>` : ''}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 40" style="width:32px;height:40px;filter:drop-shadow(0 3px 6px rgba(0,0,0,.35))">
                    <path d="M16 0C9.373 0 4 5.373 4 12c0 9 12 28 12 28s12-19 12-28C28 5.373 22.627 0 16 0z" fill="#7c3aed"/>
                    <circle cx="16" cy="12" r="5" fill="white"/>
                </svg>
            </div>`,
        className: '',
        iconSize: [32, 40],
        iconAnchor: [16, 40],
        popupAnchor: [0, -42],
    });
}

async function reverseGeocode(lat: number, lng: number) {
    try {
        const r = await fetch(
            `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=en`,
        );
        const d = await r.json();
        pinnedAddress.value = d.display_name
            ? d.display_name.split(',').slice(0, 3).join(',')
            : '';
    } catch {
        pinnedAddress.value = '';
    }
}

function placeMarker(lat: number, lng: number, fly = false) {
    if (!map) return;

    if (fly) {
        map.flyTo([lat, lng], 17, { duration: 1.2 });
    }

    if (marker) {
        marker.setLatLng([lat, lng]);
        marker.setIcon(makePinIcon(true));
        setTimeout(() => marker?.setIcon(makePinIcon(false)), 1500);
    } else {
        marker = L.marker([lat, lng], { draggable: true, icon: makePinIcon(true) }).addTo(map);
        setTimeout(() => marker?.setIcon(makePinIcon(false)), 1500);

        marker.on('dragstart', () => {
            marker?.setIcon(makePinIcon(true));
        });
        marker.on('dragend', () => {
            const pos = marker!.getLatLng();
            const lt = parseFloat(pos.lat.toFixed(7));
            const ln = parseFloat(pos.lng.toFixed(7));
            marker?.setIcon(makePinIcon(false));
            emit('update:lat', lt);
            emit('update:lng', ln);
            reverseGeocode(lt, ln);
        });
    }

    emit('update:lat', parseFloat(lat.toFixed(7)));
    emit('update:lng', parseFloat(lng.toFixed(7)));
    reverseGeocode(lat, lng);
}

// ── Address search ──────────────────────────────────────────────
async function findOnMap() {
    if (!searchQuery.value.trim()) return;
    isSearching.value = true;
    searchError.value = '';
    try {
        const phUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery.value)}&countrycodes=ph&limit=1&accept-language=en`;
        const d1 = await (await fetch(phUrl)).json();
        let result = d1[0];

        if (!result) {
            const globalUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery.value)}&limit=1&accept-language=en`;
            result = (await (await fetch(globalUrl)).json())[0];
        }

        if (result) {
            placeMarker(parseFloat(result.lat), parseFloat(result.lon), true);
        } else {
            searchError.value = 'Location not found. Try a more specific address.';
        }
    } catch {
        searchError.value = 'Search failed. Check your connection.';
    } finally {
        isSearching.value = false;
    }
}

// ── GPS / current location ──────────────────────────────────────
function useCurrentLocation() {
    if (!navigator.geolocation) {
        locationError.value = 'Geolocation is not supported by your browser.';
        return;
    }
    isLocating.value = true;
    locationError.value = '';

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            isLocating.value = false;
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            // Show accuracy radius
            accuracyCircle?.remove();
            if (pos.coords.accuracy < 5000 && map) {
                accuracyCircle = L.circle([lat, lng], {
                    radius: pos.coords.accuracy,
                    color: '#7c3aed',
                    fillColor: '#7c3aed',
                    fillOpacity: 0.08,
                    weight: 1,
                }).addTo(map);
            }

            placeMarker(lat, lng, true);
        },
        (err) => {
            isLocating.value = false;
            locationError.value =
                err.code === 1
                    ? 'Location access denied. Please allow it in your browser settings.'
                    : 'Could not get your location. Please try again.';
        },
        { enableHighAccuracy: true, timeout: 10000 },
    );
}

// ── Clear ───────────────────────────────────────────────────────
function clearPin() {
    marker?.remove();
    marker = null;
    accuracyCircle?.remove();
    accuracyCircle = null;
    pinnedAddress.value = '';
    emit('update:lat', null);
    emit('update:lng', null);
}

// ── Lifecycle ───────────────────────────────────────────────────
onMounted(() => {
    if (!mapEl.value) return;

    const initLat = props.lat ?? DEFAULT_LAT;
    const initLng = props.lng ?? DEFAULT_LNG;
    const zoom = props.lat ? 17 : 13;

    map = L.map(mapEl.value, { zoomControl: false }).setView([initLat, initLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Zoom control bottom-right
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    if (props.lat && props.lng) {
        placeMarker(props.lat, props.lng);
    }

    map.on('click', (e: L.LeafletMouseEvent) => {
        placeMarker(e.latlng.lat, e.latlng.lng);
    });
});

onUnmounted(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div class="space-y-3">
        <!-- Search bar -->
        <div class="flex gap-2">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search clinic address…"
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-violet-400 focus:ring-2 focus:ring-violet-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                    @keydown.enter.prevent="findOnMap"
                />
            </div>
            <button
                type="button"
                @click="findOnMap"
                :disabled="isSearching || !searchQuery.trim()"
                class="flex shrink-0 items-center gap-1.5 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700 disabled:opacity-50"
                title="Search address"
            >
                <svg v-if="isSearching" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span class="hidden sm:inline">Find</span>
            </button>
        </div>

        <p v-if="searchError" class="text-xs text-red-500">{{ searchError }}</p>

        <!-- Map canvas + floating controls -->
        <div class="relative">
            <div ref="mapEl" class="h-80 w-full overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700" style="cursor: crosshair;" />

            <!-- Floating: "Click to pin" hint badge (top-left) -->
            <div
                v-if="!lat && !lng"
                class="pointer-events-none absolute left-3 top-3 z-[400] flex items-center gap-1.5 rounded-lg bg-white/90 px-2.5 py-1.5 text-xs font-medium text-gray-600 shadow-sm backdrop-blur-sm dark:bg-gray-900/90 dark:text-gray-300"
            >
                <svg class="h-3.5 w-3.5 text-violet-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                Click on map to drop pin
            </div>

            <!-- Floating: GPS button (top-right) -->
            <button
                type="button"
                :disabled="isLocating"
                @click="useCurrentLocation"
                class="absolute right-3 top-3 z-[400] flex items-center gap-1.5 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-md transition hover:bg-violet-50 hover:text-violet-700 disabled:opacity-60 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-violet-950/50"
                title="Use my current location"
            >
                <svg v-if="isLocating" class="h-4 w-4 animate-spin text-violet-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
                <svg v-else class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3" />
                    <path stroke-linecap="round" d="M12 2v2M12 20v2M2 12h2M20 12h2" />
                    <path stroke-linecap="round" d="M12 5a7 7 0 100 14A7 7 0 0012 5z" />
                </svg>
                <span>{{ isLocating ? 'Locating…' : 'My Location' }}</span>
            </button>
        </div>

        <!-- Location error -->
        <p v-if="locationError" class="flex items-center gap-1.5 text-xs text-amber-600">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
            {{ locationError }}
        </p>

        <!-- Pinned state: address + clear -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 translate-y-1"
            leave-active-class="transition ease-in duration-150"
            leave-to-class="opacity-0 translate-y-1"
        >
            <div v-if="lat && lng" class="flex items-start justify-between gap-3 rounded-xl border border-violet-200 bg-violet-50 px-3 py-2.5 dark:border-violet-800 dark:bg-violet-950/30">
                <div class="flex min-w-0 items-start gap-2">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-violet-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                    </svg>
                    <div class="min-w-0">
                        <p v-if="pinnedAddress" class="truncate text-xs font-medium text-violet-800 dark:text-violet-200">{{ pinnedAddress }}</p>
                        <p class="text-xs text-violet-500 dark:text-violet-400">{{ lat.toFixed(6) }}, {{ lng.toFixed(6) }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    @click="clearPin"
                    class="shrink-0 rounded-lg p-1 text-violet-400 transition hover:bg-violet-100 hover:text-red-500 dark:hover:bg-violet-900"
                    title="Remove pin"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p v-else class="text-xs text-gray-400 dark:text-gray-600">
                Click on the map, search an address, or use your current location to pin your clinic.
            </p>
        </Transition>
    </div>
</template>

<style>
/* Pin pulse keyframe – injected globally so Leaflet's DOM picks it up */
@keyframes pin-pulse {
    0%   { transform: scale(0.5); opacity: 1; }
    100% { transform: scale(2.5); opacity: 0; }
}
</style>
