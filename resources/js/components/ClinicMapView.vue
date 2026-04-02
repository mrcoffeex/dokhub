<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import markerIconUrl from 'leaflet/dist/images/marker-icon.png';
import markerIconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadowUrl from 'leaflet/dist/images/marker-shadow.png';

(L.Icon.Default.prototype as any)._getIconUrl = undefined;
L.Icon.Default.mergeOptions({
    iconUrl: markerIconUrl,
    iconRetinaUrl: markerIconRetinaUrl,
    shadowUrl: markerShadowUrl,
});

const props = defineProps<{
    lat: number;
    lng: number;
    doctorName: string;
    address?: string | null;
}>();

const mapEl = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;

function openDirections() {
    window.open(
        `https://www.google.com/maps?q=${props.lat},${props.lng}`,
        '_blank',
        'noopener,noreferrer',
    );
}

onMounted(() => {
    if (!mapEl.value) return;

    map = L.map(mapEl.value, { zoomControl: true, scrollWheelZoom: false }).setView(
        [props.lat, props.lng],
        16,
    );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    // Custom orange pin icon
    const pinIcon = L.divIcon({
        html: `<div style="width:36px;height:36px;background:#ea580c;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:3px solid #fff;box-shadow:0 2px 8px rgba(234,88,12,.45)"></div>`,
        className: '',
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -38],
    });

    const popupContent = `<div style="font-size:13px;line-height:1.5"><strong>Dr. ${props.doctorName}</strong>${props.address ? `<br><span style="color:#6b7280">${props.address}</span>` : ''}</div>`;

    L.marker([props.lat, props.lng], { icon: pinIcon })
        .addTo(map)
        .bindPopup(popupContent)
        .openPopup();
});

onUnmounted(() => {
    map?.remove();
    map = null;
});
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-gray-100">
        <!-- Map -->
        <div ref="mapEl" class="h-44 w-full" />

        <!-- Footer bar -->
        <div class="flex items-center justify-between border-t border-gray-100 bg-white px-4 py-2.5">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <svg class="h-3.5 w-3.5 shrink-0 text-orange-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                <span class="line-clamp-1">{{ address ?? 'Clinic location' }}</span>
            </div>
            <button
                type="button"
                @click="openDirections"
                class="ml-3 flex shrink-0 items-center gap-1 rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-orange-700"
            >
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
                Get Directions
            </button>
        </div>
    </div>
</template>
