@extends('frontend.layouts.app')

@section('title', 'Lacak Pengiriman Peta - Pesanan ' . $pesanan->nomor_pesanan)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Pelacakan Pengiriman</h2>
                <a href="{{ route('pesanan.saya.detail', ['id' => $pesanan->id]) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Detail Pesanan
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Lokasi Terkini</h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Detail Pesanan</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>No. Pesanan:</strong> {{ $pesanan->nomor_pesanan }}</p>
                    <p class="mb-1"><strong>No. Resi:</strong> {{ $pesanan->nomor_resi }}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-primary">{{ ucwords(str_replace('_', ' ', $pesanan->status_pesanan)) }}</span></p>
                    @if($pesanan->estimasi_pengiriman && $pesanan->estimasi_pengiriman !== 'Estimasi tidak tersedia')
                    <p class="mb-0"><strong>Estimasi Tiba:</strong> {{ $pesanan->estimasi_pengiriman }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informasi Kendaraan Pengirim</h5>
                </div>
                <div class="card-body">
                    @if(isset($vehicleInfo))
                    <p class="mb-1"><i class="fas fa-truck fa-fw me-2 text-primary"></i><strong>Jenis:</strong> {{ $vehicleInfo['type'] }}</p>
                    <p class="mb-1"><i class="fas fa-id-card fa-fw me-2 text-primary"></i><strong>No. Polisi:</strong> {{ $vehicleInfo['plate_number'] }}</p>
                    <p class="mb-1"><i class="fas fa-user-tie fa-fw me-2 text-primary"></i><strong>Pengemudi:</strong> {{ $vehicleInfo['driver_name'] }}</p>
                    <p class="mb-0"><i class="fas fa-phone fa-fw me-2 text-primary"></i><strong>Kontak:</strong> {{ $vehicleInfo['driver_phone'] }}</p>
                    @else
                    <p class="text-muted">Informasi kendaraan tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($googleMapsApiKey && $googleMapsApiKey !== 'YOUR_GOOGLE_MAPS_API_KEY_DEFAULT')
<script async defer src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initMap"></script>
<script>
    let map;
    let directionsService;
    let directionsRenderer;

    // Koordinat dari PHP
    const warehouseLocation = { lat: {{ $warehouseCoords['lat'] }}, lng: {{ $warehouseCoords['lng'] }} };
    const destinationLocation = { lat: {{ $destinationCoords['lat'] }}, lng: {{ $destinationCoords['lng'] }} };
    // const vehicleCurrentLocation = { lat: {{ $vehicleInfo['current_lat'] ?? 'null' }}, lng: {{ $vehicleInfo['current_lng'] ?? 'null' }} }; // Jika ada lokasi kendaraan real-time

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: destinationLocation, // Pusatkan peta ke tujuan atau titik tengah
            zoom: 12,
            mapTypeControl: false,
            streetViewControl: false,
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: true // Kita akan buat marker kustom
        });

        calculateAndDisplayRoute(warehouseLocation, destinationLocation);
        addMarkers();
    }

    function calculateAndDisplayRoute(origin, destination) {
        directionsService.route({
            origin: origin,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING
        }, (response, status) => {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            } else {
                window.alert('Permintaan rute gagal karena ' + status);
            }
        });
    }

    function addMarkers() {
        const warehouseMarker = new google.maps.Marker({
            position: warehouseLocation,
            map: map,
            title: 'Gudang Pengiriman',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' // Ikon gudang
            }
        });

        const destinationMarker = new google.maps.Marker({
            position: destinationLocation,
            map: map,
            title: 'Alamat Penerima',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png' // Ikon tujuan
            }
        });

        // Contoh Marker Kendaraan (jika ada lokasi real-time)
        // if (vehicleCurrentLocation.lat && vehicleCurrentLocation.lng) {
        //     const vehicleMarker = new google.maps.Marker({
        //         position: vehicleCurrentLocation,
        //         map: map,
        //         title: 'Posisi Kendaraan Saat Ini',
        //         icon: {
        //             url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png', // Ikon kendaraan
        //             // scaledSize: new google.maps.Size(40, 40) // Sesuaikan ukuran jika perlu
        //         }
        //     });
        // }

        // Fit map to bounds
        const bounds = new google.maps.LatLngBounds();
        bounds.extend(warehouseLocation);
        bounds.extend(destinationLocation);
        // if (vehicleCurrentLocation.lat && vehicleCurrentLocation.lng) {
        //     bounds.extend(vehicleCurrentLocation);
        // }
        map.fitBounds(bounds);
        // Adjust zoom after fitBounds if it's too zoomed out/in
        google.maps.event.addListenerOnce(map, 'bounds_changed', function() {
            if (this.getZoom() > 15) { // Batasi zoom maksimal agar tidak terlalu dekat
                this.setZoom(15);
            }
            if (this.getZoom() < 5 && bounds.getNorthEast().equals(bounds.getSouthWest())) { // Jika hanya satu titik
                this.setZoom(15);
            }
        });
    }
</script>
@else
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mapDiv = document.getElementById('map');
        if (mapDiv) {
            mapDiv.innerHTML = '<div class="alert alert-warning text-center p-5">Kunci API Google Maps belum dikonfigurasi. Peta tidak dapat ditampilkan.</div>';
        }
    });
</script>
@endif
@endpush