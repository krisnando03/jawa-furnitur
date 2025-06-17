@extends('frontend.layouts.app')

@section('title', 'Detail Pesanan - ' . $pesanan->nomor_pesanan)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">Detail Pesanan</h2>
                <a href="{{ route('pesanan.saya.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Pesanan
                </a>
            </div>
        </div>
    </div>

    {{-- Pesan status dari redirect Midtrans --}}
    @if(request()->query('payment_status') == 'success')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>Pembayaran berhasil! Pesanan Anda akan segera diproses.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif(request()->query('payment_status') == 'pending')
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-hourglass-half me-2"></i>Pembayaran Anda pending. Mohon selesaikan pembayaran atau tunggu konfirmasi.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif(request()->query('payment_status') == 'error')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-times-circle me-2"></i>Pembayaran gagal. {{ request()->query('message') ? 'Pesan: ' . request()->query('message') : 'Silakan coba lagi atau hubungi dukungan.' }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif(request()->query('payment_status') == 'finish' || request()->query('payment_status') == 'unfinish')
    {{-- User kembali dari halaman Midtrans, status akan diupdate via notifikasi server --}}
    @endif
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Pesanan: {{ $pesanan->nomor_pesanan }}</h5>
                <small class="text-muted">Tanggal Pesan: {{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
                @if(in_array($pesanan->status_pesanan, ['menunggu_konfirmasi_pembayaran', 'diproses', 'dikirim', 'selesai']) && $pesanan->waktu_bukti_diunggah)
                <br><small class="text-muted">Tanggal Pembayaran: {{ \Carbon\Carbon::parse($pesanan->waktu_bukti_diunggah)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
                @endif
                @if(in_array($pesanan->status_pesanan, ['dikirim', 'selesai']) && $pesanan->tanggal_pengiriman)
                <br><small class="text-muted">Tanggal Pengiriman: {{ \Carbon\Carbon::parse($pesanan->tanggal_pengiriman)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
                @endif
            </div>
            <span class="badge fs-6
                @if($pesanan->status_pesanan == 'menunggu_pembayaran') bg-warning text-dark
                @elseif($pesanan->status_pesanan == 'menunggu_konfirmasi_pembayaran') bg-info text-dark
                @elseif($pesanan->status_pesanan == 'diproses') bg-info text-dark
                @elseif($pesanan->status_pesanan == 'dikirim') bg-primary
                @elseif($pesanan->status_pesanan == 'selesai') bg-success
                @elseif($pesanan->status_pesanan == 'dibatalkan') bg-danger
                @else bg-secondary @endif">
                Status: {{ ucwords(str_replace('_', ' ', $pesanan->status_pesanan)) }}
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Produk Dipesan</h5>
                </div>
                <div class="card-body">
                    @if($pesanan->status_pesanan == 'dikirim' && $pesanan->nomor_resi)
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="fas fa-shipping-fast fa-fw me-2"></i> Nomor Resi: <strong>{{ $pesanan->nomor_resi }}</strong>
                    </div>
                    @endif
                    @if($pesanan->status_pesanan == 'selesai')
                    <div class="alert alert-success text-center mb-3" role="alert">
                        <i class="fas fa-check-circle fa-fw me-2"></i>Pesanan Telah Selesai
                    </div>
                    @endif
                    @foreach($pesanan->detailPesanan as $item)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        @if($item->produk && $item->produk->gambar_produk)
                        <img src="{{ $item->produk->gambar_produk_url }}" alt="{{ $item->nama_produk_saat_order }}" class="img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        @else
                        <img src="{{ asset('images/default-product.png') }}" alt="{{ $item->nama_produk_saat_order }}" class="img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->nama_produk_saat_order }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan_saat_order, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            @if($pesanan->catatan_pembeli)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Catatan untuk Penjual</h5>
                </div>
                <div class="card-body">
                    <p class="fst-italic">{{ $pesanan->catatan_pembeli }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Alamat Pengiriman</h5>
                </div>
                <div class="card-body">
                    @if($pesanan->alamatPengiriman)
                    <p class="mb-1"><strong>{{ $pesanan->alamatPengiriman->nama_penerima }}</strong></p>
                    <p class="mb-1">{{ $pesanan->alamatPengiriman->nomor_telepon }}</p>
                    <p class="mb-0">{{ $pesanan->alamatPengiriman->alamat_lengkap }}</p>
                    <p class="mb-0">{{ $pesanan->alamatPengiriman->kota }}, {{ $pesanan->alamatPengiriman->provinsi }} {{ $pesanan->alamatPengiriman->kode_pos }}</p>
                    @else
                    <p class="text-muted">Alamat pengiriman tidak tersedia.</p>
                    @endif
                    @if($pesanan->metode_pembayaran)
                    <p class="card-text mb-1"><strong>Metode Pembayaran:</strong> {{ $pesanan->metode_pembayaran }}</p>
                    @endif
                    @if($pesanan->catatan_pembeli)
                    <p class="card-text mb-1"><strong>Catatan Pembeli:</strong> {{ $pesanan->catatan_pembeli }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Rincian Pembayaran</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Subtotal Produk</span>
                            <span>Rp {{ number_format($pesanan->subtotal_produk, 0, ',', '.') }}</span>
                        </li>
                        @if($pesanan->diskon > 0)
                        <li class="list-group-item d-flex justify-content-between text-success">
                            <span>Diskon @if($pesanan->voucher) ({{ $pesanan->voucher->kode }}) @endif</span>
                            <span>- Rp {{ number_format($pesanan->diskon, 0, ',', '.') }}</span>
                        </li>
                        @endif
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Ongkos Kirim</span>
                            <span>Rp {{ number_format($pesanan->ongkos_kirim, 0, ',', '.') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between fw-bold fs-5">
                            <span>Total Pembayaran</span>
                            <span>Rp {{ number_format($pesanan->total_pembayaran, 0, ',', '.') }}</span>
                        </li>
                        {{-- Tampilkan Estimasi Pengiriman --}}
                        @if($pesanan->estimasi_pengiriman && $pesanan->estimasi_pengiriman !== 'Estimasi tidak tersedia')
                        <li class="list-group-item text-center text-muted">
                            <small><i class="fas fa-truck me-1"></i> Estimasi tiba: {{ $pesanan->estimasi_pengiriman }}</small>
                        </li>
                        @elseif($pesanan->ongkos_kirim == 0 && $pesanan->alamatPengiriman && !$pesanan->alamatPengiriman->latitude)
                        <li class="list-group-item text-center text-muted">
                            <small><i class="fas fa-exclamation-circle me-1"></i> Ongkos kirim belum final, menunggu verifikasi alamat.</small>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="card-footer">
                    {{-- Tombol Aksi Utama berdasarkan Status Pesanan --}}
                    @if($pesanan->status_pesanan == 'menunggu_pembayaran')
                    @if(isset($midtransClientKey) && $pesanan->snap_token) {{-- Jika snap token sudah ada --}}
                    <button id="pay-via-midtrans-button" class="btn btn-success btn-lg d-block w-100 mb-3">
                        <i class="fas fa-credit-card me-2"></i>Bayar Sekarang via Midtrans
                    </button>
                    {{-- Tombol COD jika Midtrans juga tersedia --}}
                    <form action="{{ route('pesanan.prosesCheckoutAkhir', ['id' => $pesanan->id]) }}" method="POST" class="d-block">
                        @csrf
                        <input type="hidden" name="metode_pembayaran_pilihan" value="Cash On Delivery (COD)">
                        <button type="submit" class="btn btn-info btn-lg d-block w-100">
                            <i class="fas fa-handshake me-2"></i>Konfirmasi Bayar di Tempat (COD)
                        </button>
                    </form>
                    @else {{-- Jika snap token belum ada, atau metode belum dipilih, arahkan ke halaman pembayaran --}}
                    <a href="{{ route('transaksi.pembayaran', ['transaksiId' => $pesanan->id]) }}" class="btn btn-primary btn-lg d-block mb-2">
                        <i class="fas fa-credit-card me-2"></i>Lanjutkan ke Pembayaran
                    </a>
                    @endif
                    @elseif (($pesanan->status_pesanan == \App\Models\Pesanan::STATUS_PEMBAYARAN_PENDING_GATEWAY || $pesanan->status_pesanan == \App\Models\Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY) && $pesanan->payment_gateway_name == 'midtrans' && $pesanan->snap_token && isset($midtransClientKey))
                    <button id="pay-via-midtrans-button" class="btn btn-warning btn-lg d-block w-100 mb-2"> {{-- ID tombol sama dengan di pembayaran.blade.php --}}
                        <i class="fas fa-redo me-2"></i>
                        @if($pesanan->status_pesanan == \App\Models\Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY)
                        Coba Bayar Lagi via Midtrans
                        @else
                        Lanjutkan Pembayaran via Midtrans
                        @endif
                    </button>
                    {{-- Tombol COD jika pembayaran Midtrans sebelumnya gagal --}}
                    <form action="{{ route('pesanan.prosesCheckoutAkhir', ['id' => $pesanan->id]) }}" method="POST" class="d-block mt-2">
                        @csrf
                        <input type="hidden" name="metode_pembayaran_pilihan" value="Cash On Delivery (COD)">
                        <button type="submit" class="btn btn-info btn-lg d-block w-100">
                            <i class="fas fa-handshake me-2"></i>Pilih Bayar di Tempat (COD)
                        </button>
                        </button>
                        @elseif ($pesanan->status_pesanan == 'menunggu_konfirmasi_pembayaran')
                        <p class="text-center text-info mb-0"><i class="fas fa-spinner fa-spin me-2"></i>Menunggu Konfirmasi Pembayaran</p>
                        <p class="text-muted text-center small">Bukti pembayaran Anda sedang kami verifikasi. Mohon tunggu beberapa saat.</p>
                        {{-- <p class="text-muted">Silakan lakukan pembayaran sesuai metode yang dipilih dan unggah bukti pembayaran Anda.</p> --}}
                        @elseif($pesanan->status_pesanan == 'dikirim' && $pesanan->nomor_resi && $pesanan->alamatPengiriman && $pesanan->alamatPengiriman->latitude && $pesanan->alamatPengiriman->longitude)
                        <a href="{{ route('pesanan.lacak.peta', ['nomor_pesanan' => $pesanan->nomor_pesanan]) }}" class="btn btn-info btn-lg d-block mb-2">
                            <i class=" fas fa-map-marked-alt me-2"></i>Lacak Pengiriman di Peta
                        </a>
                        <form action="{{ route('pesanan.uploadBuktiTerima', ['id' => $pesanan->id]) }}" method="POST" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <div class="input-group">
                                <input class="form-control" type="file" id="bukti_terima_file" name="bukti_terima_file" required>
                                <button type="submit" class="btn btn-secondary">Kirim</button>
                            </div>
                        </form>
                        @elseif($pesanan->status_pesanan == 'selesai')
                        {{-- Pertimbangkan tombol "Beli Lagi" atau "Beri Ulasan" di sini --}}
                        @endif

                        {{-- Tombol Aksi Sekunder --}}
                        <div class="d-grid gap-2 d-md-flex justify-content-md-start @if($pesanan->status_pesanan != 'selesai' && $pesanan->status_pesanan != 'dibatalkan') mt-3 @endif">
                            {{-- Tombol Chat Penjual --}}
                            <a href="{{ route('pesan.index') }}?receiver=admin&order_id={{ $pesanan->nomor_pesanan }}" class="btn btn-outline-secondary flex-fill">
                                <i class="fas fa-comments me-2"></i>Chat Penjual
                            </a>

                            {{-- Tombol Batalkan Pesanan (jika status memungkinkan) --}}
                            {{-- Kondisi asli dari modal adalah 'menunggu_pembayaran', 'diproses' --}}
                            @if(in_array($pesanan->status_pesanan, [\App\Models\Pesanan::STATUS_MENUNGGU_PEMBAYARAN, \App\Models\Pesanan::STATUS_DIPROSES, \App\Models\Pesanan::STATUS_PEMBAYARAN_PENDING_GATEWAY, \App\Models\Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY]))
                            <button type="button" class="btn btn-outline-danger flex-fill" data-bs-toggle="modal" data-bs-target="#batalPesananModal">
                                Batalkan Pesanan
                            </button>
                            @endif
                        </div>
                        {{-- Tombol Konfirmasi Terima --}}
                        @if($pesanan->status_pesanan == 'menunggu_konfirmasi_terima')
                        <form action="{{ route('pesanan.konfirmasiTerima', ['id' => $pesanan->id]) }}" method="POST" class="d-block mt-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check-circle me-2"></i>Konfirmasi Barang Diterima
                            </button>
                        </form>
                        @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Pembatalan Pesanan dipindahkan ke dalam section content --}}
    @if(in_array($pesanan->status_pesanan, [\App\Models\Pesanan::STATUS_MENUNGGU_PEMBAYARAN, \App\Models\Pesanan::STATUS_DIPROSES, \App\Models\Pesanan::STATUS_PEMBAYARAN_PENDING_GATEWAY, \App\Models\Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY]))
    <!-- Modal Konfirmasi Pembatalan Pesanan -->
    <div class="modal fade" id="batalPesananModal" tabindex="-1" aria-labelledby="batalPesananModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="batalPesananModalLabel">Konfirmasi Pembatalan Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin membatalkan pesanan dengan nomor <strong>{{ $pesanan->nomor_pesanan }}</strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tidak, Kembali</button>
                    <form action="{{ route('pesanan.saya.batal') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
                        <button type="submit" class="btn btn-danger">Ya, Batalkan Pesanan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div> {{-- End of .container py-5 --}}
@endsection

@push('scripts')
@if( ( $pesanan->status_pesanan == \App\Models\Pesanan::STATUS_MENUNGGU_PEMBAYARAN ||
$pesanan->status_pesanan == \App\Models\Pesanan::STATUS_PEMBAYARAN_PENDING_GATEWAY ||
$pesanan->status_pesanan == \App\Models\Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY
) && $pesanan->snap_token && isset($midtransClientKey)
)
<script type="text/javascript" src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ $midtransClientKey }}"></script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        var payButton = document.getElementById('pay-via-midtrans-button');
        if (payButton) {
            payButton.addEventListener('click', function() {
                snap.pay('{{ $pesanan->snap_token }}', {
                    onSuccess: function(result) {
                        console.log('Midtrans Success:', result);
                        window.location.href = "{{ route('pesanan.saya.detail', $pesanan->id) }}?payment_status=success&transaction_id=" + result.transaction_id + "&order_id=" + result.order_id;
                    },
                    onPending: function(result) {
                        console.log('Midtrans Pending:', result);
                        window.location.href = "{{ route('pesanan.saya.detail', $pesanan->id) }}?payment_status=pending&transaction_id=" + result.transaction_id + "&order_id=" + result.order_id;
                    },
                    onError: function(result) {
                        console.error('Midtrans Error:', result);
                        window.location.href = "{{ route('pesanan.saya.detail', $pesanan->id) }}?payment_status=error&message=" + encodeURIComponent(result.status_message || 'Pembayaran gagal.') + "&order_id=" + (result.order_id || '{{$pesanan->nomor_pesanan}}');
                    },
                    onClose: function() {
                        console.log('Midtrans popup closed by user');
                        // Bisa tambahkan notifikasi bahwa pembayaran belum selesai
                    }
                });
            });
        }
    });
</script>
@endif
@endpush