@extends('frontend.layouts.app')

@section('title', 'Pembayaran Pesanan - ' . $pesanan->nomor_pesanan)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Pembayaran Pesanan #{{ $pesanan->nomor_pesanan }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if(isset($errorMidtrans) && $errorMidtrans)
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $errorMidtrans }}
                    </div>
                    @endif

                    <h5 class="mb-3">Ringkasan Pesanan</h5>
                    <ul class="list-group mb-4">
                        @foreach($pesanan->detailPesanan as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                {{ $item->nama_produk_saat_order }} ({{ $item->jumlah }}x)
                            </div>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </li>
                        @endforeach
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Subtotal Produk</span>
                            <span>Rp {{ number_format($pesanan->subtotal_produk, 0, ',', '.') }}</span>
                        </li>
                        @if($pesanan->diskon > 0)
                        <li class="list-group-item d-flex justify-content-between text-success">
                            <span>Diskon</span>
                            <span>- Rp {{ number_format($pesanan->diskon, 0, ',', '.') }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between fw-bold fs-5 bg-light">
                            <span>Total Pembayaran</span>
                            <span>Rp {{ number_format($pesanan->total_pembayaran, 0, ',', '.') }}</span>
                        </li>
                    </ul>

                    <h5 class="mb-3">Pilih Metode Pembayaran</h5>

                    {{-- Tombol Bayar dengan Midtrans --}}
                    @if($snapToken && $midtransClientKey)
                    <button id="pay-via-midtrans-button" class="btn btn-success btn-lg d-block w-100 mb-3">
                        <i class="fas fa-credit-card me-2"></i>Bayar Sekarang via Midtrans
                    </button>
                    @else
                    <div class="alert alert-warning">
                        Pembayaran online (Midtrans) saat ini tidak tersedia. Anda dapat memilih metode Bayar di Tempat (COD) jika tersedia, atau hubungi kami.
                    </div>
                    @endif

                    {{-- Tombol Bayar di Tempat (COD) --}}
                    {{-- Selalu tampilkan opsi COD jika status pesanan memungkinkan --}}
                    @if(in_array($pesanan->status_pesanan, [\App\Models\Pesanan::STATUS_MENUNGGU_PEMBAYARAN, \App\Models\Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY]))
                    <form action="{{ route('pesanan.prosesCheckoutAkhir', ['id' => $pesanan->id]) }}" method="POST" class="d-block">
                        @csrf
                        <input type="hidden" name="metode_pembayaran_pilihan" value="Cash On Delivery (COD)">
                        <button type="submit" class="btn btn-info btn-lg d-block w-100">
                            <i class="fas fa-handshake me-2"></i>Konfirmasi Bayar di Tempat (COD)
                        </button>
                    </form>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('pesanan.saya.detail', $pesanan->id) }}" class="text-muted">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Detail Pesanan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($snapToken && $midtransClientKey)
<script type="text/javascript" src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ $midtransClientKey }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var payButton = document.getElementById('pay-via-midtrans-button');
        if (payButton) {
            payButton.addEventListener('click', function() {
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        window.location.href = "{{ route('pesanan.saya.detail', $pesanan->id) }}?payment_status=success&transaction_id=" + result.transaction_id + "&order_id=" + result.order_id;
                    },
                    onPending: function(result) {
                        window.location.href = "{{ route('pesanan.saya.detail', $pesanan->id) }}?payment_status=pending&transaction_id=" + result.transaction_id + "&order_id=" + result.order_id;
                    },
                    onError: function(result) {
                        window.location.href = "{{ route('pesanan.saya.detail', $pesanan->id) }}?payment_status=error&message=" + encodeURIComponent(result.status_message || 'Pembayaran gagal.') + "&order_id=" + (result.order_id || '{{$pesanan->nomor_pesanan}}');
                    },
                    onClose: function() {
                        console.log('Midtrans popup closed by user');
                    }
                });
            });
        }
    });
</script>
@endif
@endpush