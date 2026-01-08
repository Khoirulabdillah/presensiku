@extends('layouts.app')

@section('title', 'Pengaturan Lokasi Kantor')

@section('content')

<div class="bg-white shadow-xl rounded-2xl w-full max-w-6xl mx-auto p-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-6">Pengaturan Lokasi Kantor</h3>

    @if(session('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none'">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <form action="{{ route('admin.office-settings.update') }}" method="POST" id="office-settings-form">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Map Section -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Peta Lokasi Kantor</label>
                <div id="map" class="w-full h-96 rounded-lg border-2 border-gray-300"></div>
            </div>

            <!-- Form Inputs -->
            <div class="space-y-4">
                <div>
                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="number" step="any" id="latitude" name="latitude" value="{{ $officeSetting->latitude ?? '-7.7956' }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    @error('latitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="number" step="any" id="longitude" name="longitude" value="{{ $officeSetting->longitude ?? '110.3695' }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    @error('longitude')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="radius" class="block text-sm font-medium text-gray-700">Radius (meter)</label>
                    <input type="number" id="radius" name="radius" value="{{ $officeSetting->radius ?? 50 }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           min="1" required>
                    @error('radius')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_masuk" class="block text-sm font-medium text-gray-700">Jam Masuk</label>
                    <input type="time" id="jam_masuk" name="jam_masuk" value="{{ $officeSetting->jam_masuk ? \Carbon\Carbon::parse($officeSetting->jam_masuk)->format('H:i') : '08:00' }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('jam_masuk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jam_pulang" class="block text-sm font-medium text-gray-700">Jam Pulang</label>
                    <input type="time" id="jam_pulang" name="jam_pulang" value="{{ $officeSetting->jam_pulang ? \Carbon\Carbon::parse($officeSetting->jam_pulang)->format('H:i') : '17:00' }}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('jam_pulang')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-200">
                        <i class="fas fa-save mr-2"></i>Simpan Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map
    const map = L.map('map').setView([{{ $officeSetting->latitude ?? -7.7956 }}, {{ $officeSetting->longitude ?? 110.3695 }}], 15);

    // Add OpenStreetMap tiles (with tileerror handler and retina support)
    const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
        detectRetina: true,
        subdomains: ['a','b','c']
    });

    tiles.addTo(map);

    // Log tile loading errors to help diagnose missing tiles
    tiles.on('tileerror', function(error) {
        console.warn('Leaflet tile error:', error);
    });

    // Add marker
    const marker = L.marker([{{ $officeSetting->latitude ?? -7.7956 }}, {{ $officeSetting->longitude ?? 110.3695 }}], {
        draggable: true
    }).addTo(map);

    // Update inputs when marker is dragged
    marker.on('dragend', function(event) {
        const position = marker.getLatLng();
        // Keep higher precision when updating inputs so coordinates are not overly truncated
        document.getElementById('latitude').value = position.lat.toFixed(12);
        document.getElementById('longitude').value = position.lng.toFixed(12);
    });

    // Update marker position when inputs change
    document.getElementById('latitude').addEventListener('input', updateMarker);
    document.getElementById('longitude').addEventListener('input', updateMarker);

    function updateMarker() {
        const lat = parseFloat(document.getElementById('latitude').value);
        const lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng]);
        }
    }
    // Ensure map correctly renders tiles when container becomes visible
    setTimeout(() => {
        try {
            map.invalidateSize();
        } catch (e) {
            console.warn('Error invalidating Leaflet map size', e);
        }
    }, 200);

    window.addEventListener('resize', () => map.invalidateSize());
});
</script>

@endsection