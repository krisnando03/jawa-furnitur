@extends('frontend.layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h2 class="fw-bold mb-4">Pesanan Saya</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <ul class="nav nav-tabs mb-3" id="pesananTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab == 'semua' ? 'active' : '' }}" href="{{ route('pesanan.saya.index', ['status' => 'semua']) }}" role="tab" aria-selected="{{ $activeTab == 'semua' }}">
                        <i class="fas fa-list-alt me-1"></i> Semua
                        @if(isset($statusCounts['semua']) && $statusCounts['semua'] > 0)
                        <span class="badge bg-secondary ms-1">{{ $statusCounts['semua'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab == 'belum-bayar' ? 'active' : '' }}" href="{{ route('pesanan.saya.index', ['status' => 'belum-bayar']) }}" role="tab" aria-selected="{{ $activeTab == 'belum-bayar' }}">
                        <i class="fas fa-wallet me-1"></i> Belum Bayar
                        @if(isset($statusCounts['belum-bayar']) && $statusCounts['belum-bayar'] > 0)
                        <span class="badge bg-warning text-dark ms-1">{{ $statusCounts['belum-bayar'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab == 'dikemas' ? 'active' : '' }}" href="{{ route('pesanan.saya.index', ['status' => 'dikemas']) }}" role="tab" aria-selected="{{ $activeTab == 'dikemas' }}">
                        <i class="fas fa-box-open me-1"></i> Dikemas
                        @if(isset($statusCounts['dikemas']) && $statusCounts['dikemas'] > 0)
                        <span class="badge bg-info text-dark ms-1">{{ $statusCounts['dikemas'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab == 'dikirim' ? 'active' : '' }}" href="{{ route('pesanan.saya.index', ['status' => 'dikirim']) }}" role="tab" aria-selected="{{ $activeTab == 'dikirim' }}">
                        <i class="fas fa-truck me-1"></i> Dikirim
                        @if(isset($statusCounts['dikirim']) && $statusCounts['dikirim'] > 0)
                        <span class="badge bg-primary ms-1">{{ $statusCounts['dikirim'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab == 'selesai' ? 'active' : '' }}" href="{{ route('pesanan.saya.index', ['status' => 'selesai']) }}" role="tab" aria-selected="{{ $activeTab == 'selesai' }}">
                        <i class="fas fa-check-circle me-1"></i> Selesai
                        @if(isset($statusCounts['selesai']) && $statusCounts['selesai'] > 0)
                        <span class="badge bg-success ms-1">{{ $statusCounts['selesai'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="pesananTabContent">
                <div class="tab-pane fade show active" role="tabpanel">
                    @if($semuaPesanan->isEmpty())
                    <div class="card shadow-sm">
                        <div class="card-body text-center text-muted fs-5 py-5">
                            <i class="fas fa-file-invoice-dollar fa-3x mb-3"></i><br>
                            Anda belum memiliki pesanan dengan status ini.
                        </div>
                    </div>
                    @else
                    @foreach($semuaPesanan as $pesanan)
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Nomor Pesanan: {{ $pesanan->nomor_pesanan }}</strong> <br>
                                <small class="text-muted">Tanggal: {{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
                            </div>
                            <span class="badge 
                                        @if($pesanan->status_pesanan == 'menunggu_pembayaran') bg-warning text-dark 
                                        @elseif($pesanan->status_pesanan == 'diproses') bg-info text-dark
                                        @elseif($pesanan->status_pesanan == 'dikirim') bg-primary
                                        @elseif($pesanan->status_pesanan == 'selesai') bg-success
                                        @elseif($pesanan->status_pesanan == 'dibatalkan') bg-danger
                                        @else bg-secondary @endif">
                                {{ ucwords(str_replace('_', ' ', $pesanan->status_pesanan)) }}
                            </span>
                        </div>
                        <div class="card-body">
                            @if($pesanan->detailPesanan->isNotEmpty())
                            @foreach($pesanan->detailPesanan as $item)
                            <div class="d-flex">
                                @if($item->produk && $item->produk->gambar_produk_url)
                                <img src="{{ $item->produk->gambar_produk_url }}" alt="{{ $item->nama_produk_saat_order }}" class="img-fluid rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-image fa-lg text-muted"></i>
                                </div>
                                @endif
                                <div class="flex-grow-1 d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ $item->nama_produk_saat_order }}</h6>
                                        <small class="text-muted">{{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan_saat_order, 0, ',', '.') }}</small>
                                    </div>
                                    <span class="text-dark fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endforeach
                            @else
                            <p class="text-muted">Tidak ada detail item untuk pesanan ini.</p>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total Pesanan:</strong>
                                <strong class="text-primary">Rp{{ number_format($pesanan->total_pembayaran, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            @if($pesanan->status_pesanan == 'menunggu_pembayaran')
                            <a href="{{ route('transaksi.pembayaran', ['transaksiId' => $pesanan->id]) }}" class="btn btn-sm btn-success">Bayar Sekarang</a>
                            @elseif($pesanan->status_pesanan == 'dikirim' && $pesanan->nomor_resi && $pesanan->alamatPengiriman && $pesanan->alamatPengiriman->latitude && $pesanan->alamatPengiriman->longitude)
                            <a href="{{ route('pesanan.lacak.peta', ['nomor_pesanan' => $pesanan->nomor_pesanan]) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-map-marked-alt me-1"></i>Lacak di Peta
                            </a>
                            @elseif($pesanan->status_pesanan == 'selesai')
                            <a href="{{ route('pesanan.beliLagi', ['id' => $pesanan->id]) }}" class="btn btn-sm btn-outline-primary">Beli Lagi</a>
                            @endif
                            <a href="{{ route('pesanan.saya.detail', ['id' => $pesanan->id]) }}" class="btn btn-sm btn-outline-secondary">Lihat Detail</a>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection