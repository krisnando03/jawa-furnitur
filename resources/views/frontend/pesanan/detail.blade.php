@extends('frontend.layouts.app')

@section('title', isset($pesanan) ? 'Detail Pesanan - ' . $pesanan->nomor_pesanan : 'Konfirmasi Pesanan')

@section('content')
<div class="container py-5">
    {{-- Jika ini adalah halaman konfirmasi SEBELUM pesanan dibuat, judulnya 'Konfirmasi Pesanan Anda' --}}
    {{-- Jika ini adalah halaman untuk MENAMPILKAN detail pesanan YANG SUDAH ADA, judulnya bisa 'Detail Pesanan Anda' --}}
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold">{{ isset($pesanan) ? 'Detail Pesanan Anda' : 'Konfirmasi Pesanan Anda' }}</h2>
                @if(isset($pesanan))
                <a href="{{ route('pesanan.saya.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Pesanan
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(!isset($pesanan)) {{-- Bagian ini hanya untuk mode konfirmasi (sebelum pesanan dibuat) --}}
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fas fa-info-circle fa-2x me-3"></i>
        <div>
            Silakan periksa kembali detail pesanan Anda di bawah ini sebelum melanjutkan ke pembayaran. Anda dapat memilih alamat pengiriman dan menggunakan voucher jika tersedia.
        </div>
    </div>
    @endif

    {{-- Tampilkan pesan dari proses "Beli Lagi" --}}
    @if(isset($beliLagiMessages) && !empty($beliLagiMessages))
    @foreach($beliLagiMessages as $message)
    <div class="alert 
                @if($message['type'] == 'error') alert-danger 
                @elseif($message['type'] == 'warning') alert-warning 
                @else alert-info 
                @endif 
                d-flex align-items-center" role="alert">
        <i class="fas @if($message['type'] == 'error') fa-times-circle @elseif($message['type'] == 'warning') fa-exclamation-triangle @else fa-info-circle @endif fa-2x me-3"></i>
        <div>
            {{ $message['text'] }}
        </div>
    </div>
    @endforeach
    @endif

    {{-- Informasi Pesanan Umum (Nomor Pesanan & Status) --}}
    @if(isset($pesanan))
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Pesanan: {{ $pesanan->nomor_pesanan }}</h5>
                    <small class="text-muted">Tanggal Pesan: {{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</small>
                </div>
                <span class="badge fs-6
                    @if($pesanan->status_pesanan == 'menunggu_pembayaran') bg-warning text-dark
                    @elseif($pesanan->status_pesanan == 'diproses') bg-info text-dark
                    @elseif($pesanan->status_pesanan == 'dikirim') bg-primary
                    @elseif($pesanan->status_pesanan == 'selesai') bg-success
                    @elseif($pesanan->status_pesanan == 'dibatalkan') bg-danger
                    @else bg-secondary @endif">
                    Status: {{ ucwords(str_replace('_', ' ', $pesanan->status_pesanan)) }}
                </span>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-7"> {{-- Kolom Kiri untuk Produk dan Catatan --}}
            {{-- Produk Dipesan --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Produk Dipesan</h5>
                </div>
                <div class="card-body">
                    @if(isset($produk) && isset($jumlahBeli) && !isset($pesanan)) {{-- Konfirmasi Buy Now (single product) --}}
                    {{-- Blok ini untuk halaman konfirmasi SEBELUM pesanan dibuat (single product) --}}
                    <div class="d-flex align-items-center">
                        <img src="{{ $produk->gambar_produk_url }}" alt="{{ $produk->nama_produk }}" class=" img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $produk->nama_produk }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $jumlahBeli }} x Rp {{ number_format($produk->harga, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 fw-semibold">Rp {{ number_format($produk->harga * $jumlahBeli, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    @elseif(isset($selectedCartItems) && $selectedCartItems->isNotEmpty() && !isset($pesanan)) {{-- Konfirmasi dari Keranjang (multiple products) --}}
                    {{-- Blok ini untuk halaman konfirmasi SEBELUM pesanan dibuat (multiple products dari keranjang) --}}
                    @foreach($selectedCartItems as $item)
                    <div class="d-flex align-items-center mb-3 pb-3 @if(!$loop->last) border-bottom @endif">
                        @if($item->produk && $item->produk->gambar_produk_url) {{-- Gunakan gambar_produk_url dari model Produk --}}
                        <img src="{{ $item->produk->gambar_produk_url }}" alt="{{ $item->produk->nama_produk ?? 'Produk tidak tersedia' }}" class="img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        @else
                        <img src="{{ asset('images/default-product.png') }}" alt="{{ $item->produk->nama_produk ?? 'Produk tidak tersedia' }}" class="img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan_saat_order, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 fw-semibold">Rp {{ number_format($item->subtotal_harga, 0, ',', '.') }}</p> {{-- Gunakan subtotal_harga dari item keranjang --}}
                        </div>
                    </div>
                    @endforeach
                    @elseif(isset($selectedCartItems) && $selectedCartItems->isEmpty() && !isset($pesanan))
                    {{-- Kasus Beli Lagi tetapi tidak ada item yang bisa ditambahkan --}}
                    <div class="alert alert-warning text-center">
                        Tidak ada item yang dapat ditambahkan ke pesanan baru dari pembelian sebelumnya.
                    </div>
                    @endif

                    {{-- Blok ini untuk halaman detail pesanan YANG SUDAH ADA (bisa multiple products) --}}
                    @if(isset($pesanan) && $pesanan->detailPesanan->isNotEmpty())
                    @foreach($pesanan->detailPesanan as $item)
                    <div class="d-flex align-items-center mb-3 pb-3 @if(!$loop->last) border-bottom @endif">
                        @if($item->produk && $item->produk->gambar_produk_url)
                        <img src="{{ $item->produk->gambar_produk_url }}" alt="{{ $item->nama_produk_saat_order }}" class="img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        @else
                        <img src="{{ asset('images/default-product.png') }}" alt="{{ $item->nama_produk_saat_order }}" class="img-fluid rounded me-3" style="width: 70px; height: 70px; object-fit: cover;">
                        @endif
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $item->nama_produk_saat_order }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $item->jumlah }} x Rp {{ number_format($item->harga_satuan_saat_order ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-end">
                            <p class="mb-0 fw-semibold">Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}</p> {{-- Gunakan subtotal dari DetailPesanan --}}
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>

            {{-- Pesan untuk Penjual / Catatan Pembeli --}}
            @if(!isset($pesanan))
            {{-- Form input pesan untuk halaman konfirmasi --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Pesan untuk Penjual (Opsional)</h5>
                </div>
                <div class="card-body">
                    <textarea name="pesan_untuk_penjual_form" class="form-control" rows="3" placeholder="Tulis catatan untuk penjual di sini..."></textarea>
                </div>
            </div>
            @elseif(isset($pesanan) && $pesanan->catatan_pembeli)
            {{-- Menampilkan catatan jika pesanan sudah ada --}}
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

        <div class="col-md-5"> {{-- Kolom Kanan untuk Alamat, Voucher, Rincian Pembayaran --}}
            {{-- Alamat Pengiriman --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Alamat Pengiriman</h5>
                </div>
                <div class="card-body">
                    @if(!isset($pesanan) && isset($daftarAlamat) && $daftarAlamat->isNotEmpty())
                    {{-- Opsi pilih alamat untuk halaman konfirmasi --}}
                    <div class="mb-3">
                        <label for="selected_alamat_id" class="form-label">Pilih Alamat:</label>
                        <select class="form-select" id="selected_alamat_id" name="alamat_pengiriman_id_form">
                            @foreach($daftarAlamat as $alamat)
                            <option value="{{ $alamat->id }}" data-alamat-detail="{{ json_encode($alamat) }}" {{ (isset($alamatPengiriman) && $alamatPengiriman->id == $alamat->id) || $alamat->is_utama ? 'selected' : '' }}>
                                {{ $alamat->label_alamat ?? 'Alamat' }} - {{ $alamat->nama_penerima }} ({{ Str::limit($alamat->alamat_lengkap, 25) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="detailAlamatTerpilih">
                        @if(isset($alamatPengiriman) && $alamatPengiriman)
                        <p class="mb-1"><strong>{{ $alamatPengiriman->nama_penerima ?? 'Nama Penerima Belum Diatur' }}</strong></p>
                        <p class="mb-1">{{ $alamatPengiriman->nomor_telepon ?? 'Nomor Telepon Belum Diatur' }}</p>
                        <p class="mb-0">{{ $alamatPengiriman->alamat_lengkap ?? 'Detail Alamat Belum Diatur' }}</p>
                        <p class="mb-0">{{ $alamatPengiriman->kota ?? '' }}{{ isset($alamatPengiriman->provinsi) ? ', '.$alamatPengiriman->provinsi : '' }} {{ $alamatPengiriman->kode_pos ?? '' }}</p>
                        @else
                        <p class="text-muted">Pilih alamat untuk melihat detail.</p>
                        @endif
                    </div>
                    <a href="{{ route('profile.show') }}#tambahAlamatForm" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-plus me-1"></i> Tambah Alamat Baru
                    </a>
                    @elseif(isset($pesanan) && $pesanan->alamatPengiriman)
                    {{-- Menampilkan alamat jika pesanan sudah ada --}}
                    <p class="mb-1"><strong>{{ $pesanan->alamatPengiriman->nama_penerima ?? 'Nama Penerima Belum Diatur' }}</strong></p>
                    <p class="mb-1">{{ $pesanan->alamatPengiriman->nomor_telepon ?? 'Nomor Telepon Belum Diatur' }}</p>
                    <p class="mb-0">{{ $pesanan->alamatPengiriman->alamat_lengkap ?? 'Detail Alamat Belum Diatur' }}</p>
                    <p class="mb-0">
                        {{ $pesanan->alamatPengiriman->kota ?? '' }}
                        {{ isset($pesanan->alamatPengiriman->provinsi) ? ', '.$pesanan->alamatPengiriman->provinsi : '' }}
                        {{ $pesanan->alamatPengiriman->kode_pos ?? '' }}
                    </p>
                    @else
                    <p class="text-danger">Alamat pengiriman belum dipilih atau diatur.
                        <a href="{{ route('profile.show') }}#tambahAlamatForm" class="btn btn-sm btn-warning ms-2">Tambah Alamat Sekarang</a>
                    </p>
                    @endif {{-- Alamat pengiriman hanya ditampilkan di blok alamat --}}
                </div>
            </div>

            {{-- Voucher --}}
            @if(!isset($pesanan))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Gunakan Voucher</h5>
                </div>

                <div id="detailAlamatTerpilih">
                    <div class="card-body">
                        @if(isset($daftarVoucher) && $daftarVoucher->isNotEmpty())
                        <select class="form-select" id="selected_voucher_kode" name="voucher_kode_form">
                            <option value="">-- Tidak menggunakan voucher --</option>
                            @foreach($daftarVoucher as $v)
                            <option value="{{ $v->kode }}" data-voucher-detail="{{ json_encode($v) }}" {{ (isset($appliedVoucher) && $appliedVoucher->kode == $v->kode) ? 'selected' : '' }}>
                                {{ $v->nama_voucher }} ({{ Str::limit($v->deskripsi, 30) }})
                            </option>
                            @endforeach
                        </select>
                        <div id="detailVoucherTerpilih" class="mt-2 small text-muted">
                            {{-- Detail voucher akan ditampilkan di sini oleh JS --}}
                        </div>
                        @else
                        <p class="text-muted">Tidak ada voucher yang tersedia saat ini.</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Rincian Pembayaran --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Rincian Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @if(!isset($pesanan)) {{-- Rincian untuk halaman konfirmasi --}}
                            {{-- Rincian untuk halaman konfirmasi, diupdate oleh JS --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                @php
                                // Calculate total items for display based on context
                                $totalItems = isset($jumlahBeli) ? $jumlahBeli : (isset($selectedCartItems) ? $selectedCartItems->sum('jumlah') : 0);
                                @endphp
                                <span>Subtotal Produk ({{ $totalItems }} item)</span>
                                @php
                                // Digunakan untuk tampilan awal subtotal, sama dengan $initialSubtotalForJs
                                $initialDisplaySubtotal = isset($produk) ? ($produk->harga * ($jumlahBeli ?? 1)) : (isset($selectedCartItems) ? $selectedCartItems->sum('subtotal_harga') : 0);
                                @endphp
                                <span id="subtotalProdukDisplay">Rp {{ number_format($initialDisplaySubtotal, 0, ',', '.') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center text-success" id="diskonDisplayRow" style="display: none;">
                                <span>Diskon Voucher <span id="kodeVoucherDisplay"></span></span>
                                <span id="nilaiDiskonDisplay">- Rp 0</span>
                            </li>
                            @if(isset($ongkosKirim))
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Ongkos Kirim</span>
                                <strong id="ongkos-kirim-display">Rp {{ number_format($ongkosKirim, 0, ',', '.') }}</strong>
                            </li>
                            @endif
                            <li class="list-group-item d-flex justify-content-between align-items-center fw-bold fs-5">
                                <span>Total Pembayaran</span>
                                @php
                                // Calculate initial subtotal for JS based on context
                                $initialSubtotalForJs = isset($produk) ? ($produk->harga * $jumlahBeli) : (isset($selectedCartItems) ? $selectedCartItems->sum('subtotal_harga') : 0);
                                @endphp
                                <span id="totalPembayaranDisplay">Rp {{ number_format($initialSubtotalForJs, 0, ',', '.') }}</span>
                            </li>
                            {{-- Tampilkan Estimasi Pengiriman --}}
                            @if(isset($estimasiPengiriman) && $estimasiPengiriman !== 'Estimasi tidak tersedia')
                            <li class="list-group-item text-center text-muted">
                                <small><i class="fas fa-truck me-1"></i> Estimasi tiba: {{ $estimasiPengiriman }}</small>
                            </li>
                            @elseif(isset($ongkosKirim) && $ongkosKirim == 0 && (!isset($alamatPengiriman) || !$alamatPengiriman->latitude))
                            <li class="list-group-item text-center text-muted">
                                <small><i class="fas fa-exclamation-circle me-1"></i> Ongkos kirim akan dihitung setelah alamat lengkap dengan koordinat.</small>
                            </li>
                            @endif
                            @elseif(isset($pesanan))
                            {{-- Rincian untuk halaman detail pesanan yang sudah ada --}}
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
                            @endif
                        </ul>
                    </div>
                    {{-- Tombol Aksi di Footer Card Rincian Pembayaran --}}
                    @if(!isset($pesanan))
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success btn-lg w-100" id="submitOrderBtn"
                            @if(isset($selectedCartItems) && $selectedCartItems->isEmpty()) disabled @endif>
                            Buat Pesanan & Lanjut ke Pembayaran
                        </button>
                        @if(isset($selectedCartItems) && $selectedCartItems->isEmpty()) <small class="d-block text-center text-danger mt-2">Tidak ada item untuk dipesan.</small> @endif
                    </div>
                    @elseif(isset($pesanan))
                    <div class="card-footer">
                        @if($pesanan->status_pesanan == 'menunggu_pembayaran')
                        <a href="{{ route('transaksi.pembayaran', ['transaksiId' => $pesanan->id]) }}" class="btn btn-success btn-lg w-100">Bayar Sekarang</a>
                        @elseif($pesanan->status_pesanan == 'dikirim' && $pesanan->nomor_resi && $pesanan->alamatPengiriman && $pesanan->alamatPengiriman->latitude && $pesanan->alamatPengiriman->longitude)
                        <a href="{{ route('pesanan.lacak.peta', ['nomor_pesanan' => $pesanan->nomor_pesanan]) }}" class="btn btn-info btn-lg w-100" target="_blank">
                            <i class="fas fa-map-marked-alt me-2"></i>Lacak Pengiriman di Peta
                        </a>
                        @elseif($pesanan->status_pesanan == 'selesai')
                        <p class="text-center text-success mb-0"><i class="fas fa-check-circle me-2"></i>Pesanan Selesai</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Form untuk membuat pesanan (hanya tampil di halaman konfirmasi) --}}
        @if(!isset($pesanan))
        <form action="{{ route('pesanan.store') }}" method="POST" id="orderForm">
            @csrf
            {{-- Input hidden untuk alamat dan voucher akan diisi oleh JS --}}
            <input type="hidden" name="alamat_pengiriman_id" id="form_alamat_pengiriman_id" value="{{ $alamatPengiriman->id ?? '' }}">
            <input type="hidden" name="voucher_kode" id="form_voucher_kode" value="{{ $appliedVoucher->kode ?? '' }}"> {{-- Pre-fill voucher jika dari keranjang --}}
            <input type="hidden" name="pesan_untuk_penjual" id="form_pesan_untuk_penjual" value=""> {{-- Diisi oleh JS dari textarea --}}
            {{-- Input hidden untuk ongkos kirim dan estimasi --}}
            <input type="hidden" name="ongkos_kirim" id="ongkos_kirim_input" value="{{ $ongkosKirim ?? 0 }}">
            <input type="hidden" name="estimasi_pengiriman" id="estimasi_pengiriman_input" value="{{ $estimasiPengiriman ?? '' }}">

            {{-- Input hidden untuk item pesanan --}}
            @if(isset($produk)) {{-- Jika dari Buy Now (single product) --}}
            <input type="hidden" name="produk_id" value="{{ $produk->id }}">
            <input type="hidden" name="jumlah_beli" value="{{ $jumlahBeli }}">
            @elseif(isset($selectedCartItems)) {{-- Jika dari Keranjang (multiple products) --}}
            @foreach($selectedCartItems as $item)
            <input type="hidden" name="cart_items[{{ $loop->index }}][id]" value="{{ $item->id }}"> {{-- Optional: pass cart item ID --}}
            <input type="hidden" name="cart_items[{{ $loop->index }}][product_id]" value="{{ $item->id_produk }}">
            <input type="hidden" name="cart_items[{{ $loop->index }}][quantity]" value="{{ $item->jumlah }}">
            <input type="hidden" name="cart_items[{{ $loop->index }}][price_at_order]" value="{{ $item->harga_saat_dibeli }}"> {{-- Pass price at the time added to cart --}}
            @endforeach
            @endif

            {{-- Total pembayaran akan dihitung ulang di server, jadi tidak perlu dikirim dari sini --}}

            {{-- Tombol submit sekarang ada di dalam card-footer Rincian Pembayaran --}}
            {{-- <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Buat Pesanan & Lanjut ke Pembayaran</button>
            </div> --}}
        </form>
        @else
        {{-- Tombol kembali sudah ada di header halaman jika $pesanan ada --}}
        @endif
    </div>
    @endsection

    @push('scripts')
    {{-- Pastikan CSRF token tersedia untuk AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAlamat = document.getElementById('selected_alamat_id');
            const detailAlamatDiv = document.getElementById('detailAlamatTerpilih');
            const formAlamatIdInput = document.getElementById('form_alamat_pengiriman_id');

            // Elemen untuk ongkir dan estimasi yang diupdate oleh JS
            const ongkirDisplayElement = document.getElementById('ongkos-kirim-display'); // Untuk menampilkan ongkir
            const estimasiDisplayElement = document.querySelector('.list-group-item small i.fa-truck')?.closest('li'); // Elemen <li> estimasi

            const selectVoucher = document.getElementById('selected_voucher_kode');
            const pesanUntukPenjualTextarea = document.querySelector('textarea[name="pesan_untuk_penjual_form"]');
            const detailVoucherDiv = document.getElementById('detailVoucherTerpilih');
            const formVoucherKodeInput = document.getElementById('form_voucher_kode');

            const subtotalProdukDisplay = document.getElementById('subtotalProdukDisplay');
            const diskonDisplayRow = document.getElementById('diskonDisplayRow');
            const kodeVoucherDisplay = document.getElementById('kodeVoucherDisplay');
            const nilaiDiskonDisplay = document.getElementById('nilaiDiskonDisplay');
            // const ongkosKirimDisplay = document.getElementById('ongkos-kirim-display');
            const totalPembayaranDisplay = document.getElementById('totalPembayaranDisplay');

            const formPesanUntukPenjualInput = document.getElementById('form_pesan_untuk_penjual');
            // initialSubtotal sudah mencakup logika untuk kedua skenario (Buy Now / Cart)
            const initialSubtotal = parseFloat("{{ isset($produk) ? ($produk->harga * ($jumlahBeli ?? 1)) : (isset($selectedCartItems) ? $selectedCartItems->sum('subtotal_harga') : 0) }}");

            const orderForm = document.getElementById('orderForm');
            const submitOrderBtn = document.getElementById('submitOrderBtn');

            // Hidden input untuk menyimpan nilai ongkir dan estimasi yang akan disubmit
            const ongkosKirimHiddenInput = document.createElement('input');
            ongkosKirimHiddenInput.type = 'hidden';
            ongkosKirimHiddenInput.name = 'ongkos_kirim'; // Sesuaikan dengan nama yang diharapkan di backend (PesananController@store)
            ongkosKirimHiddenInput.id = 'ongkos_kirim_input_hidden';
            ongkosKirimHiddenInput.value = "{{ $ongkosKirim ?? 0 }}"; // Nilai awal dari PHP
            if (orderForm) orderForm.appendChild(ongkosKirimHiddenInput);

            const estimasiPengirimanHiddenInput = document.createElement('input');
            estimasiPengirimanHiddenInput.type = 'hidden';
            estimasiPengirimanHiddenInput.name = 'estimasi_pengiriman'; // Sesuaikan dengan nama yang diharapkan di backend
            estimasiPengirimanHiddenInput.id = 'estimasi_pengiriman_input_hidden';
            estimasiPengirimanHiddenInput.value = "{{ $estimasiPengiriman ?? 'Estimasi tidak tersedia' }}"; // Nilai awal dari PHP
            if (orderForm) orderForm.appendChild(estimasiPengirimanHiddenInput);

            function updateDetailAlamat(selectedOption) {
                if (selectedOption && selectedOption.dataset.alamatDetail) {
                    const alamat = JSON.parse(selectedOption.dataset.alamatDetail);
                    detailAlamatDiv.innerHTML = `
                <p class="mb-1"><strong>${alamat.nama_penerima}</strong></p>
                <p class="mb-1">${alamat.nomor_telepon}</p>
                <p class="mb-0">${alamat.alamat_lengkap}</p>
                <p class="mb-0">${alamat.kota}, ${alamat.provinsi} ${alamat.kode_pos}</p>
            `;
                    if (formAlamatIdInput) formAlamatIdInput.value = alamat.id;
                } else {
                    detailAlamatDiv.innerHTML = '<p class="text-muted">Pilih alamat untuk melihat detail.</p>';
                    if (formAlamatIdInput) formAlamatIdInput.value = '';
                    if (ongkirDisplayElement) ongkirDisplayElement.textContent = 'Rp 0';
                    if (ongkosKirimHiddenInput) ongkosKirimHiddenInput.value = 0;
                    if (estimasiDisplayElement) estimasiDisplayElement.style.display = 'none';
                    hitungDanTampilkanTotal(0); // Update total dengan ongkir 0

                }
            }

            // function updateTotalPembayaran() {
            //     let subtotalProduk = parseFloat(document.getElementById('subtotal_produk_value').value) || 0;
            //     let diskon = parseFloat(document.getElementById('diskon_value').value) || 0;
            //     let ongkosKirim = parseFloat(document.getElementById('ongkos_kirim_value').value) || 0; // Ambil dari input hidden

            //     let totalPembayaran = subtotalProduk - diskon + ongkosKirim;

            //     document.getElementById('subtotal-produk-display').textContent = 'Rp ' + subtotalProduk.toLocaleString('id-ID');
            //     document.getElementById('diskon-display').textContent = '- Rp ' + diskon.toLocaleString('id-ID');
            //     document.getElementById('ongkos-kirim-display').textContent = 'Rp ' + ongkosKirim.toLocaleString('id-ID'); // Tampilkan juga di ringkasan
            //     document.getElementById('total-pembayaran-display').textContent = 'Rp ' + totalPembayaran.toLocaleString('id-ID');

            //     // Jika Anda memiliki input hidden untuk total_pembayaran yang akan di-submit
            //     // const totalPembayaranInput = document.getElementById('total_pembayaran_input');
            //     // if (totalPembayaranInput) {
            //     //     totalPembayaranInput.value = totalPembayaran;
            //     // }
            // }
            // Panggil updateTotalPembayaran saat halaman dimuat dan setiap kali ada perubahan
            // document.addEventListener('DOMContentLoaded', function() {
            //     // ... (event listener Anda yang lain) ...
            //     updateTotalPembayaran(); // Panggil saat load untuk inisialisasi
            // });
            async function fetchAndUpdateShipping(alamatId) {
                if (!alamatId) {
                    if (ongkirDisplayElement) ongkirDisplayElement.textContent = 'Rp 0';
                    if (ongkosKirimHiddenInput) ongkosKirimHiddenInput.value = 0;
                    if (estimasiDisplayElement) estimasiDisplayElement.style.display = 'none';
                    if (estimasiPengirimanHiddenInput) estimasiPengirimanHiddenInput.value = 'Pilih alamat untuk estimasi';
                    hitungDanTampilkanTotal(0);
                    return;
                }
                // Tampilkan loading
                if (ongkirDisplayElement) ongkirDisplayElement.textContent = 'Menghitung...';
                if (estimasiDisplayElement && estimasiDisplayElement.querySelector('small')) estimasiDisplayElement.querySelector('small').textContent = 'Menghitung...';

                try {
                    const response = await fetch("{{ route('pesanan.calculateDynamicShipping') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            alamat_pengiriman_id: alamatId
                        })
                    });
                    const data = await response.json();

                    if (response.ok) {
                        if (ongkirDisplayElement) ongkirDisplayElement.textContent = data.formatted_ongkos_kirim;
                        if (ongkosKirimHiddenInput) ongkosKirimHiddenInput.value = data.ongkos_kirim;

                        if (estimasiDisplayElement && estimasiDisplayElement.querySelector('small')) {
                            const estimasiTextElement = estimasiDisplayElement.querySelector('small');
                            if (data.estimasi_pengiriman && data.estimasi_pengiriman !== 'Estimasi tidak tersedia') {
                                estimasiTextElement.innerHTML = `<i class="fas fa-truck me-1"></i> Estimasi tiba: ${data.estimasi_pengiriman}`;
                                estimasiDisplayElement.style.display = ''; // Tampilkan elemen estimasi
                                if (estimasiPengirimanHiddenInput) estimasiPengirimanHiddenInput.value = data.estimasi_pengiriman;

                            } else if (data.error_detail) {
                                estimasiTextElement.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${data.estimasi_pengiriman || data.error_detail}`;
                                estimasiDisplayElement.style.display = ''; // Tampilkan elemen estimasi
                                if (estimasiPengirimanHiddenInput) estimasiPengirimanHiddenInput.value = data.estimasi_pengiriman || data.error_detail;
                            } else {
                                estimasiDisplayElement.style.display = 'none'; // Sembunyikan jika tidak ada estimasi valid
                                if (estimasiPengirimanHiddenInput) estimasiPengirimanHiddenInput.value = 'Estimasi tidak tersedia';
                            }
                        }
                        hitungDanTampilkanTotal(data.ongkos_kirim); // Hitung ulang total dengan ongkir baru

                    } else {
                        console.error('Error fetching shipping:', data.error || data.message || 'Unknown error');
                        if (ongkirDisplayElement) ongkirDisplayElement.textContent = 'Error';
                        if (ongkosKirimHiddenInput) ongkosKirimHiddenInput.value = 0; // Atau nilai ongkir sebelumnya
                        if (estimasiDisplayElement) estimasiDisplayElement.style.display = 'none';
                        if (estimasiPengirimanHiddenInput) estimasiPengirimanHiddenInput.value = 'Error';
                        hitungDanTampilkanTotal(parseFloat(ongkosKirimHiddenInput ? ongkosKirimHiddenInput.value : 0));
                    }
                } catch (error) {
                    console.error('Network error or other issue:', error);
                    if (ongkirDisplayElement) ongkirDisplayElement.textContent = 'Error';
                    if (ongkosKirimHiddenInput) ongkosKirimHiddenInput.value = 0;
                    if (estimasiDisplayElement) estimasiDisplayElement.style.display = 'none';
                    if (estimasiPengirimanHiddenInput) estimasiPengirimanHiddenInput.value = 'Error';
                    hitungDanTampilkanTotal(parseFloat(ongkosKirimHiddenInput ? ongkosKirimHiddenInput.value : 0));
                }
            }

            function hitungDanTampilkanTotal() {
                let diskon = 0;
                let kodeVoucher = '';
                let voucherDeskripsi = '';

                if (selectVoucher && selectVoucher.value !== '') {
                    const selectedVoucherOption = selectVoucher.options[selectVoucher.selectedIndex];
                    const voucher = JSON.parse(selectedVoucherOption.dataset.voucherDetail);

                    if (initialSubtotal >= voucher.min_pembelian) { // Menggunakan initialSubtotal
                        if (voucher.tipe_diskon === 'persen') {
                            diskon = (initialSubtotal * voucher.nilai_diskon) / 100; // Menggunakan initialSubtotal
                            if (voucher.maks_diskon && diskon > voucher.maks_diskon) {
                                diskon = voucher.maks_diskon;
                            }
                        } else if (voucher.tipe_diskon === 'tetap') { // Pastikan tipe_diskon 'tetap' sesuai dengan controller
                            diskon = parseFloat(voucher.nilai_diskon);
                        }
                        diskon = Math.min(diskon, initialSubtotal); // Diskon tidak boleh > subtotal
                        kodeVoucher = `(${voucher.kode})`;
                        voucherDeskripsi = voucher.deskripsi;
                        if (formVoucherKodeInput) formVoucherKodeInput.value = voucher.kode;
                    } else {
                        if (formVoucherKodeInput) formVoucherKodeInput.value = ''; // Reset jika tidak memenuhi syarat
                        voucherDeskripsi = '<span class="text-danger">Minimal pembelian tidak terpenuhi untuk voucher ini.</span>';
                    }
                } else {
                    if (formVoucherKodeInput) formVoucherKodeInput.value = '';
                }

                detailVoucherDiv.innerHTML = voucherDeskripsi;

                diskonDisplayRow.style.display = diskon > 0 ? 'flex' : 'none';
                kodeVoucherDisplay.textContent = kodeVoucher;
                nilaiDiskonDisplay.textContent = `- Rp ${new Intl.NumberFormat('id-ID').format(diskon)}`;

                // Ambil ongkir dari hidden input yang diupdate oleh fetchAndUpdateShipping
                const ongkirSaatIni = parseFloat(ongkosKirimHiddenInput ? ongkosKirimHiddenInput.value : 0);
                const totalAkhir = initialSubtotal - diskon + ongkirSaatIni;
                totalPembayaranDisplay.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(totalAkhir)}`;
            }

            if (selectAlamat) {
                selectAlamat.addEventListener('change', async function() { // Jadikan async
                    updateDetailAlamat(this.options[this.selectedIndex]);
                    await fetchAndUpdateShipping(this.value); // Tunggu fetch selesai sebelum lanjut
                });
                // Panggil sekali saat load untuk alamat yang terpilih default
                updateDetailAlamat(selectAlamat.options[selectAlamat.selectedIndex]);
                // Panggil juga fetchAndUpdateShipping saat load jika sudah ada alamat terpilih
                if (selectAlamat.value) fetchAndUpdateShipping(selectAlamat.value);

            }

            if (selectVoucher) {
                selectVoucher.addEventListener('change', function() {
                    hitungDanTampilkanTotal();
                });
            }

            if (pesanUntukPenjualTextarea && formPesanUntukPenjualInput) {
                pesanUntukPenjualTextarea.addEventListener('input', function() {
                    formPesanUntukPenjualInput.value = this.value;
                });
            }

            if (submitOrderBtn && orderForm) {
                submitOrderBtn.addEventListener('click', function() {
                    orderForm.submit();
                });
            }

            // Panggil sekali saat load untuk kalkulasi awal
            // Jika sudah ada alamat terpilih saat load (dari PHP), panggil fetchAndUpdateShipping
            // Jika tidak, panggil hitungDanTampilkanTotal dengan ongkir 0
            if (selectAlamat && selectAlamat.value) {
                // fetchAndUpdateShipping sudah dipanggil di atas
            } else {
                hitungDanTampilkanTotal(0); // Ongkir awal 0 jika tidak ada alamat
            }
        });
    </script>
    @endpush