@extends('frontend.layouts.app')

@section('title', 'Lacak Pengiriman')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="fw-bold mb-4 text-center">Status Pengiriman Pesanan</h2>

            @if($id_pesanan && $pesanan)

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Detail Pengiriman untuk Pesanan: <strong>{{ $id_pesanan }}</strong></h5>
                    <p class="mb-0 text-muted">Nomor Resi: {{ $pesanan->nomor_resi ?? 'N/A' }}</p>
                </div>
                <div class="card-body">
                    @if(!empty($trackingData))
                    <ul class="timeline">
                        @foreach(collect($trackingData)->sortByDesc('timestamp') as $track)
                        <li class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <p class="fw-semibold mb-0">{{ $track->status }}</p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i> {{ \Carbon\Carbon::parse($track->timestamp)->format('d M Y, H:i') }}
                                    @if(isset($track->lokasi) && $track->lokasi)
                                    <br><i class="fas fa-map-marker-alt me-1"></i> {{ $track->lokasi }}
                                    @endif
                                </small>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-center text-muted py-4">
                        <i class="fas fa-box-open fa-2x mb-2"></i><br>
                        Belum ada informasi pengiriman untuk pesanan ini atau nomor resi tidak valid.
                    </p>
                    @endif
                </div>
            </div>
            @else
            <div class="alert alert-warning text-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Pesanan tidak ditemukan atau ID pesanan tidak valid.
                <a href="{{ route('pesanan.saya.index') }}" class="alert-link ms-2">Kembali ke Daftar Pesanan</a>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Basic Timeline CSS */
    .timeline {
        list-style-type: none;
        padding-left: 0;
        position: relative;
    }

    .timeline:before {
        content: '';
        position: absolute;
        left: 7px;
        /* Adjust based on marker size */
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        margin-bottom: 20px;
        position: relative;
        padding-left: 30px;
        /* Space for marker and line */
    }

    .timeline-marker {
        position: absolute;
        left: 0;
        top: 5px;
        /* Adjust vertical alignment */
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #0d6efd;
        /* Primary color */
        border: 2px solid white;
        z-index: 1;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 0.25rem;
        border: 1px solid #dee2e6;
    }
</style>
@endsection