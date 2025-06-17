<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Produk;
use App\Models\AlamatPengiriman; // Pastikan model ini ada
use App\Models\Voucher;         // Pastikan model ini ada
use App\Models\Pesanan;         // Pastikan model ini ada
use App\Models\DetailPesanan;   // Pastikan model ini ada
use App\Models\Keranjang;       // Import model Keranjang
use App\Models\Pelanggan;       // Untuk mendapatkan ID Pelanggan
use Illuminate\Support\Facades\Storage; // Untuk manajemen fileuse Illuminate\Support\Facades\Log; // Import Log
use App\Models\Notifikasi;      // Import model Notifikasi
use App\Services\ShippingService; // Jangan lupa import
use App\Services\MidtransService; // Import MidtransService
use Illuminate\Support\Facades\Log;
use App\Models\KendaraanPengirim; // Import model KendaraanPengirim

// ... (metode store dan buyNow yang sudah ada)
class PesananController extends Controller
{
    protected ShippingService $shippingService;
    protected MidtransService $midtransService;

    public function __construct(ShippingService $shippingService, MidtransService $midtransService) // Inject di sini
    {
        $this->shippingService = $shippingService;
        $this->midtransService = $midtransService;
    }
    /**
     * Menyimpan pesanan baru ke database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Cek Autentikasi Pengguna
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk membuat pesanan.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // 2. Validasi Input
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required_without:cart_items|integer|exists:produk,id', // Required if cart_items is not present
            'jumlah_beli' => 'required_without:cart_items|integer|min:1', // Required if cart_items is not present
            'alamat_pengiriman_id' => 'required|integer|exists:alamat_pengiriman,id', // Asumsi tabel alamat_pengiriman
            'voucher_kode' => 'nullable|string|exists:vouchers,kode', // Asumsi tabel vouchers dan kolom kode
            'pesan_untuk_penjual' => 'nullable|string|max:500',
            // 'total_pembayaran' => 'required|numeric|min:0', // Akan dihitung ulang di server
        ]);

        if ($validator->fails()) {
            // Determine redirect based on source (Buy Now or Cart)
            if ($request->has('produk_id')) { // Likely from Buy Now confirmation
                return redirect()->back()->withErrors($validator)->withInput();
            } else { // Likely from Cart confirmation
                // Redirect back to cart confirmation page, maybe need to pass selected items again?
                // Or simpler, redirect to cart index with error
                return redirect()->route('keranjang.index')->withErrors($validator)->withInput();
            }
        }

        // 3. Ambil Data yang Diperlukan
        $itemsToOrder = [];
        $subtotalProduk = 0;
        $totalBerat = 0; // Tambahkan kalkulasi berat jika perlu

        if ($request->has('produk_id')) { // Dari Buy Now
            $produk = Produk::find($request->input('produk_id'));
            $jumlahBeli = (int) $request->input('jumlah_beli');
            if (!$produk || $produk->stok < $jumlahBeli) {
                return redirect()->back()->with('error', 'Stok produk tidak mencukupi atau produk tidak ditemukan.')->withInput();
            }
            $itemsToOrder[] = (object)[
                'id_produk' => $produk->id,
                'nama_produk_saat_order' => $produk->nama_produk,
                'harga_satuan_saat_order' => $produk->harga,
                'jumlah' => $jumlahBeli,
                'subtotal' => $produk->harga * $jumlahBeli,
                'produk' => $produk, // Keep product object for stock update
            ];
            $subtotalProduk = $itemsToOrder[0]->subtotal;
        } elseif ($request->has('cart_items')) { // Dari Keranjang
            $cartItemsData = $request->input('cart_items'); // Array of {id, product_id, quantity, price_at_order}
            $cartItemIds = collect($cartItemsData)->pluck('id')->toArray();
            $selectedCartItems = Keranjang::whereIn('id', $cartItemIds)
                ->where('id_pelanggan', $id_pelanggan)
                ->with('produk')
                ->get();

            if ($selectedCartItems->isEmpty() || $selectedCartItems->count() !== count($cartItemIds)) {
                return redirect()->route('keranjang.index')->with('error', 'Beberapa item keranjang tidak valid atau tidak ditemukan.')->withInput();
            }

            foreach ($selectedCartItems as $item) {
                if (!$item->produk || $item->produk->stok < $item->jumlah) {
                    return redirect()->route('keranjang.index')->with('error', 'Stok produk "' . ($item->produk->nama_produk ?? 'Produk tidak tersedia') . '" tidak mencukupi atau produk tidak valid.')->withInput();
                }
                $itemsToOrder[] = (object)[
                    'id_produk' => $item->id_produk,
                    'nama_produk_saat_order' => $item->produk->nama_produk,
                    'harga_satuan_saat_order' => $item->harga_saat_dibeli, // Gunakan harga saat ditambahkan ke keranjang
                    'jumlah' => $item->jumlah,
                    'subtotal' => $item->subtotal_harga, // Gunakan subtotal dari keranjang
                    'produk' => $item->produk, // Keep product object for stock update
                    'cart_item_id' => $item->id, // Keep cart item ID for deletion
                ];
                $subtotalProduk += $item->subtotal_harga;
            }
        } else {
            // Should not happen due to validation, but as a fallback
            return redirect()->route('home')->with('error', 'Tidak ada item yang dipilih untuk dipesan.');
        }

        // 4. Ambil Alamat Pengiriman
        $alamatPengiriman = AlamatPengiriman::where('id', $request->input('alamat_pengiriman_id'))
            ->where('id_pelanggan', $id_pelanggan)
            ->first();
        if (!$alamatPengiriman) {
            // Redirect based on source
            $redirectRoute = $request->has('produk_id') ?
                route('transaksi.detail', ['produkId' => $request->input('produk_id'), 'jumlah' => $request->input('jumlah_beli')]) :
                route('keranjang.index'); // Or a specific cart confirmation route if implemented differently

            return redirect($redirectRoute)->with('error', 'Alamat pengiriman tidak valid atau bukan milik Anda.')->withInput();
        }

        // 5. Kalkulasi Harga dan Diskon (Server-side)
        $diskon = 0;
        $idVoucher = null;

        if ($request->filled('voucher_kode')) {
            $voucher = Voucher::where('kode', $request->input('voucher_kode'))
                ->where('aktif', true) // Asumsi ada kolom 'aktif'
                ->where('tanggal_mulai', '<=', now())
                ->where('tanggal_berakhir', '>=', now()) // Asumsi ada kolom tanggal berlaku
                ->first();

            if ($voucher) {
                if ($subtotalProduk >= $voucher->min_pembelian) {
                    if ($voucher->tipe_diskon == 'persen') { // Asumsi ada kolom 'tipe_diskon'
                        $diskon = ($subtotalProduk * $voucher->nilai_diskon) / 100; // Asumsi 'nilai_diskon'
                    } elseif ($voucher->tipe_diskon == 'tetap') {
                        $diskon = $voucher->nilai_diskon;
                    }
                    // Pastikan diskon tidak melebihi subtotal
                    $diskon = min($diskon, $subtotalProduk);
                    $idVoucher = $voucher->id;
                } else {
                    // Voucher ada tapi tidak memenuhi syarat min pembelian
                    $redirectRoute = $request->has('produk_id') ?
                        route('transaksi.detail', ['produkId' => $request->input('produk_id'), 'jumlah' => $request->input('jumlah_beli')]) :
                        route('keranjang.index'); // Or a specific cart confirmation route

                    return redirect($redirectRoute)->with('error', 'Total belanja tidak memenuhi syarat minimal untuk voucher ' . $voucher->kode . '.')->withInput();
                }
            } else {
                $redirectRoute = $request->has('produk_id') ?
                    route('transaksi.detail', ['produkId' => $request->input('produk_id'), 'jumlah' => $request->input('jumlah_beli')]) :
                    route('keranjang.index'); // Or a specific cart confirmation route

                // Voucher tidak ditemukan atau tidak aktif/valid
                return redirect()->back()->with('error', 'Kode voucher tidak valid atau sudah tidak berlaku.')->withInput();
            }
        }

        // Kalkulasi Ongkos Kirim dan Estimasi
        $ongkosKirim = 0;
        $estimasiPengiriman = 'Estimasi tidak tersedia'; // Default

        \Illuminate\Support\Facades\Log::info('PesananController@store: Checking address for shipping calculation.', [
            'alamat_id' => $alamatPengiriman->id ?? null,
            'latitude' => $alamatPengiriman->latitude ?? null,
            'longitude' => $alamatPengiriman->longitude ?? null,
        ]);
        if ($alamatPengiriman && $alamatPengiriman->latitude && $alamatPengiriman->longitude) {
            try {
                $distance = $this->shippingService->calculateDistance($alamatPengiriman->latitude, $alamatPengiriman->longitude);
                if ($distance !== null) {
                    $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                    $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Tidak dapat menghitung jarak untuk alamat ID: " . $alamatPengiriman->id . " pada PesananController@store");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('PesananController@store: Error calculating shipping: ' . $e->getMessage());
            }
        } else {
            \Illuminate\Support\Facades\Log::warning('PesananController@store: Alamat pengiriman tidak valid atau tidak memiliki koordinat untuk kalkulasi ongkir.');
        }

        $totalPembayaran = $subtotalProduk - $diskon + $ongkosKirim;

        // 6. Proses Penyimpanan ke Database (Gunakan Transaksi)
        try {
            DB::beginTransaction();

            // Buat Pesanan
            $pesanan = Pesanan::create([
                'id_pelanggan' => $id_pelanggan,
                'nomor_pesanan' => 'INV-' . strtoupper(Str::random(4)) . time(), // Contoh nomor pesanan
                'id_alamat_pengiriman' => $alamatPengiriman->id,
                'id_voucher' => $idVoucher,
                'subtotal_produk' => $subtotalProduk,
                'diskon' => $diskon,
                'ongkos_kirim' => $ongkosKirim,
                'estimasi_pengiriman' => $estimasiPengiriman,
                'total_pembayaran' => $totalPembayaran,
                'status_pesanan' => 'menunggu_pembayaran', // Status awal
                'catatan_pembeli' => $request->input('pesan_untuk_penjual'),
                'metode_pembayaran' => $request->input('metode_pembayaran_pilihan') ?? null, // Jika ada pilihan metode di form checkout awal
                'tanggal_pesanan' => now(),
            ]);

            // Buat Detail Pesanan dan Kurangi Stok
            $cartItemIdsToDelete = [];
            foreach ($itemsToOrder as $item) {
                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_produk' => $item->id_produk,
                    'nama_produk_saat_order' => $item->nama_produk_saat_order,
                    'harga_satuan_saat_order' => $item->harga_satuan_saat_order,
                    'jumlah' => $item->jumlah,
                    'subtotal' => $item->subtotal,
                ]);

                // Update Stok Produk
                $item->produk->stok -= $item->jumlah;
                $item->produk->save();

                if (isset($item->cart_item_id)) {
                    $cartItemIdsToDelete[] = $item->cart_item_id;
                }
            }

            // Hapus item dari keranjang jika pesanan dibuat dari keranjang
            if (!empty($cartItemIdsToDelete)) {
                Keranjang::whereIn('id', $cartItemIdsToDelete)->delete();
            }

            DB::commit();

            // Buat notifikasi untuk pelanggan
            Notifikasi::create([
                'id_pelanggan' => $id_pelanggan,
                'tipe_notifikasi' => 'pembayaran_pending',
                'judul' => 'Pesanan Dibuat - Menunggu Pembayaran',
                'pesan' => "Pesanan Anda #{$pesanan->nomor_pesanan} telah berhasil dibuat. Segera lakukan pembayaran.",
                'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
                'id_pesanan_terkait' => $pesanan->id,
            ]);


            // 7. Redirect ke Halaman Pembayaran
            // Anda mungkin ingin menyimpan ID pesanan di session untuk halaman pembayaran
            Session::put('id_pesanan_aktif', $pesanan->id);

            // Jika metode pembayaran adalah online/gateway, langsung arahkan ke halaman pembayaran gateway
            // Untuk saat ini, semua diarahkan ke halaman pembayaran internal dulu
            // Di halaman 'transaksi.pembayaran', akan ada opsi untuk bayar via Midtrans

            return redirect()->route('transaksi.pembayaran', ['transaksiId' => $pesanan->id]) // transaksiId adalah id pesanan
                ->with('success', 'Pesanan berhasil dibuat. Silakan lanjutkan ke pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Determine redirect based on source
            $redirectRoute = $request->has('produk_id') ?
                route('transaksi.detail', ['produkId' => $request->input('produk_id'), 'jumlah' => $request->input('jumlah_beli')]) :
                route('keranjang.index'); // Or a specific cart confirmation route

            // Log error $e->getMessage()
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi. ' . $e->getMessage())->withInput();
        }
    }
    /**
     * Membuat pesanan langsung dari tombol "Buy Now" dan mengarahkan ke detail pesanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function buyNow(Request $request)
    {
        // 1. Cek Autentikasi Pengguna
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk membuat pesanan.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // 2. Validasi Input
        $validator = Validator::make($request->all(), [
            'produk_id' => 'required|integer|exists:produk,id',
            'jumlah_beli' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 3. Ambil Data yang Diperlukan
        $produk = Produk::find($request->input('produk_id'));
        if (!$produk) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }

        $jumlahBeli = (int) $request->input('jumlah_beli');

        // 4. Cek Stok Produk
        if ($produk->stok < $jumlahBeli) {
            return redirect()->back()->with('error', 'Stok produk tidak mencukupi untuk jumlah yang diminta.');
        }

        // 5. Dapatkan Alamat Pengiriman Utama Pengguna
        $alamatPengiriman = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)
            ->where('is_utama', true)
            ->first();
        if (!$alamatPengiriman) {
            // Jika tidak ada alamat utama, ambil alamat terakhir yang ditambahkan
            $alamatPengiriman = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$alamatPengiriman) {
            // Jika pengguna belum punya alamat sama sekali
            // Simpan detail produk di session untuk dilanjutkan setelah menambah alamat
            Session::put('buy_now_pending_product', ['produk_id' => $produk->id, 'jumlah' => $jumlahBeli]);
            return redirect()->route('profile.show') // Arahkan ke profil untuk menambah alamat
                ->with('warning', 'Anda belum memiliki alamat pengiriman. Silakan tambahkan alamat terlebih dahulu untuk melanjutkan.');
        }

        // 6. Kalkulasi Harga (Tidak ada voucher/pesan untuk "Buy Now" langsung)
        $subtotalProduk = $produk->harga * $jumlahBeli;
        $diskon = 0;
        $idVoucher = null;
        // Kalkulasi Ongkos Kirim dan Estimasi
        $ongkosKirim = 0;
        $estimasiPengiriman = 'Estimasi tidak tersedia'; // Default
        if ($alamatPengiriman && $alamatPengiriman->latitude && $alamatPengiriman->longitude) {
            try {
                $distance = $this->shippingService->calculateDistance($alamatPengiriman->latitude, $alamatPengiriman->longitude);
                if ($distance !== null) {
                    $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                    $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Tidak dapat menghitung jarak untuk alamat ID: " . $alamatPengiriman->id . " pada PesananController@buyNow");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error calculating shipping in PesananController@buyNow: ' . $e->getMessage());
            }
        }
        $totalPembayaran = $subtotalProduk - $diskon + $ongkosKirim;

        // 7. Proses Penyimpanan ke Database (Gunakan Transaksi)
        try {
            DB::beginTransaction();

            // Buat Pesanan
            $pesanan = Pesanan::create([
                'id_pelanggan' => $id_pelanggan,
                'nomor_pesanan' => 'INVBN-' . strtoupper(Str::random(4)) . time(), // Prefix INVBN untuk Buy Now
                'id_alamat_pengiriman' => $alamatPengiriman->id,
                'id_voucher' => $idVoucher,
                'subtotal_produk' => $subtotalProduk,
                'diskon' => $diskon,
                'ongkos_kirim' => $ongkosKirim,
                'estimasi_pengiriman' => $estimasiPengiriman,
                'total_pembayaran' => $totalPembayaran,
                'status_pesanan' => 'menunggu_pembayaran', // Langsung ke status menunggu pembayaran
                'catatan_pembeli' => null,
                'metode_pembayaran' => null,
                'tanggal_pesanan' => now(),
            ]);

            // Buat Detail Pesanan
            DetailPesanan::create([
                'id_pesanan' => $pesanan->id,
                'id_produk' => $produk->id,
                'nama_produk_saat_order' => $produk->nama_produk,
                'harga_satuan_saat_order' => $produk->harga,
                'jumlah' => $jumlahBeli,
                'subtotal' => $subtotalProduk,
            ]);

            // Update Stok Produk
            $produk->stok -= $jumlahBeli;
            $produk->save();

            DB::commit();

            // Buat notifikasi untuk pelanggan
            Notifikasi::create([
                'id_pelanggan' => $id_pelanggan,
                'tipe_notifikasi' => 'pembayaran_pending',
                'judul' => 'Pesanan Dibuat - Menunggu Pembayaran',
                'pesan' => "Pesanan Anda #{$pesanan->nomor_pesanan} telah berhasil dibuat. Segera lakukan pembayaran.",
                'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
                'id_pesanan_terkait' => $pesanan->id,
            ]);

            // 8. Redirect ke Halaman Detail Pesanan yang baru dibuat
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
                ->with('success', 'Pesanan Anda telah dibuat. Silakan lanjutkan ke pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error $e->getMessage()
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Membuat pesanan dari item yang dipilih di keranjang.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkoutFromCart(Request $request)
    {
        // 1. Cek Autentikasi Pengguna
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk membuat pesanan.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // 2. Validasi Input
        $validator = Validator::make($request->all(), [
            'selected_items' => 'required|array', // Array of cart item IDs
            'selected_items.*' => 'integer|exists:keranjang,id', // Each ID must exist in the keranjang table
            'applied_discount_code' => 'nullable|string|exists:vouchers,kode', // Kode voucher jika ada
            // Anda bisa tambahkan validasi untuk alamat pengiriman jika memungkinkan memilih alamat di halaman keranjang
            // 'alamat_pengiriman_id' => 'required|integer|exists:alamat_pengiriman,id',
        ]);

        if ($validator->fails()) {
            return redirect()->route('keranjang.index')->withErrors($validator)->withInput();
        }

        $selectedItemIds = $request->input('selected_items');
        $appliedDiscountCode = $request->input('applied_discount_code');

        // 3. Ambil Item Keranjang yang Dipilih
        $selectedCartItems = Keranjang::whereIn('id', $selectedItemIds)
            ->where('id_pelanggan', $id_pelanggan)
            ->with('produk') // Eager load produk untuk cek stok dan detail
            ->get();

        if ($selectedCartItems->isEmpty()) {
            return redirect()->route('keranjang.index')->with('error', 'Tidak ada item yang dipilih untuk checkout.');
        }

        // 4. Cek Stok untuk Setiap Item Terpilih
        foreach ($selectedCartItems as $item) {
            if (!$item->produk || $item->produk->stok < $item->jumlah) {
                // Jika stok tidak cukup atau produk dihapus
                return redirect()->route('keranjang.index')->with('error', 'Stok produk "' . ($item->produk->nama_produk ?? 'Produk tidak tersedia') . '" tidak mencukupi atau produk tidak valid.');
            }
        }

        // 5. Dapatkan Alamat Pengiriman Utama Pengguna (atau minta user pilih jika belum ada/tidak utama)
        $alamatPengiriman = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)
            ->where('is_utama', true)
            ->first();
        if (!$alamatPengiriman) {
            // Jika tidak ada alamat utama, ambil alamat terakhir yang ditambahkan
            $alamatPengiriman = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$alamatPengiriman) {
            // Jika pengguna belum punya alamat sama sekali
            // Simpan detail item keranjang terpilih di session untuk dilanjutkan setelah menambah alamat
            Session::put('checkout_pending_cart_items', $selectedItemIds);
            Session::put('checkout_pending_discount_code', $appliedDiscountCode);
            return redirect()->route('profile.show') // Arahkan ke profil untuk menambah alamat
                ->with('warning', 'Anda belum memiliki alamat pengiriman. Silakan tambahkan alamat terlebih dahulu untuk melanjutkan checkout.');
        }

        // 6. Kalkulasi Harga dan Diskon (Server-side)
        $subtotalProduk = $selectedCartItems->sum('subtotal_harga');
        $diskon = 0;
        $idVoucher = null;

        if ($appliedDiscountCode) {
            $voucher = Voucher::where('kode', $appliedDiscountCode)
                ->where('aktif', true)
                ->where('tanggal_mulai', '<=', now())
                ->where('tanggal_berakhir', '>=', now())
                ->first();

            if ($voucher && $subtotalProduk >= $voucher->min_pembelian) {
                if ($voucher->tipe_diskon == 'persen') {
                    $diskon = ($subtotalProduk * $voucher->nilai_diskon) / 100;
                } elseif ($voucher->tipe_diskon == 'tetap') {
                    $diskon = $voucher->nilai_diskon;
                }
                $diskon = min($diskon, $subtotalProduk); // Pastikan diskon tidak melebihi subtotal
                $idVoucher = $voucher->id;
            }
            // Jika voucher tidak valid atau tidak memenuhi syarat, diskon tetap 0
        }

        // Kalkulasi Ongkos Kirim dan Estimasi
        $ongkosKirim = 0;
        $estimasiPengiriman = 'Estimasi tidak tersedia'; // Default
        if ($alamatPengiriman && $alamatPengiriman->latitude && $alamatPengiriman->longitude) {
            try {
                $distance = $this->shippingService->calculateDistance($alamatPengiriman->latitude, $alamatPengiriman->longitude);
                if ($distance !== null) {
                    $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                    $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Tidak dapat menghitung jarak untuk alamat ID: " . $alamatPengiriman->id . " pada PesananController@checkoutFromCart");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error calculating shipping in PesananController@checkoutFromCart: ' . $e->getMessage());
            }
        }
        $totalPembayaran = $subtotalProduk - $diskon + $ongkosKirim;

        // 7. Proses Penyimpanan ke Database (Gunakan Transaksi)
        try {
            DB::beginTransaction();

            // Buat Pesanan
            $pesanan = Pesanan::create([
                'id_pelanggan' => $id_pelanggan,
                'nomor_pesanan' => 'INVCRT-' . strtoupper(Str::random(4)) . time(), // Prefix INVCRT untuk Cart Checkout
                'id_alamat_pengiriman' => $alamatPengiriman->id,
                'id_voucher' => $idVoucher,
                'subtotal_produk' => $subtotalProduk,
                'diskon' => $diskon,
                'ongkos_kirim' => $ongkosKirim,
                'estimasi_pengiriman' => $estimasiPengiriman,
                'total_pembayaran' => $totalPembayaran,
                'status_pesanan' => 'menunggu_pembayaran', // Langsung ke status menunggu pembayaran
                'catatan_pembeli' => null, // Catatan bisa ditambahkan di halaman checkout/pembayaran
                'metode_pembayaran' => $request->input('metode_pembayaran_pilihan') ?? null,
                'tanggal_pesanan' => now(),
            ]);

            // Buat Detail Pesanan dan Kurangi Stok
            foreach ($selectedCartItems as $item) {
                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_produk' => $item->id_produk,
                    'nama_produk_saat_order' => $item->produk->nama_produk,
                    'harga_satuan_saat_order' => $item->harga_saat_dibeli,
                    'jumlah' => $item->jumlah,
                    'subtotal' => $item->subtotal_harga,
                ]);

                // Update Stok Produk
                $item->produk->stok -= $item->jumlah;
                $item->produk->save();

                // Hapus item dari keranjang setelah berhasil diproses ke pesanan
                $item->delete();
            }

            DB::commit();

            // Hapus info diskon dari session setelah pesanan dibuat
            Session::forget('cart_discount');
            Session::forget('checkout_pending_cart_items'); // Hapus data pending jika ada
            Session::forget('checkout_pending_discount_code'); // Hapus data pending jika ada

            // Buat notifikasi untuk pelanggan
            Notifikasi::create([
                'id_pelanggan' => $id_pelanggan,
                'tipe_notifikasi' => 'pembayaran_pending',
                'judul' => 'Pesanan Dibuat - Menunggu Pembayaran',
                'pesan' => "Pesanan Anda #{$pesanan->nomor_pesanan} telah berhasil dibuat. Segera lakukan pembayaran.",
                'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
                'id_pesanan_terkait' => $pesanan->id,
            ]);

            // 8. Redirect ke Halaman Pembayaran untuk pesanan yang baru dibuat
            return redirect()->route('transaksi.pembayaran', ['transaksiId' => $pesanan->id]) // transaksiId adalah id pesanan
                ->with('success', 'Pesanan Anda telah dibuat. Silakan lanjutkan ke pembayaran.');
        } catch (\Exception $e) {
            DB::rollBack();
            // Log error $e->getMessage()
            return redirect()->route('keranjang.index')->with('error', 'Terjadi kesalahan saat membuat pesanan. Silakan coba lagi. ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update metode pembayaran untuk pesanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMetodePembayaran(Request $request)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melanjutkan.');
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);
        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $validator = Validator::make($request->all(), [
            'pesanan_id' => 'required|integer|exists:pesanan,id',
            'metode_pembayaran' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $pesanan = Pesanan::where('id', $request->input('pesanan_id'))
            ->where('id_pelanggan', $id_pelanggan)
            ->first();

        if (!$pesanan) {
            return redirect()->route('pesanan.saya.index')->with('error', 'Pesanan tidak ditemukan atau bukan milik Anda.');
        }

        if ($pesanan->status_pesanan !== 'menunggu_pembayaran') {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])->with('error', 'Metode pembayaran tidak dapat diubah untuk status pesanan saat ini.');
        }

        $metodeDipilih = $request->input('metode_pembayaran');
        $pesanan->metode_pembayaran = $metodeDipilih;
        $pesanan->save();

        if (strtolower($metodeDipilih) == 'midtrans') {
            // Jika memilih Midtrans, arahkan kembali ke halaman pembayaran untuk memicu Snap.js
            return redirect()->route('transaksi.pembayaran', ['transaksiId' => $pesanan->id])
                ->with('info', 'Silakan lanjutkan pembayaran dengan Midtrans.');
        }
        return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id]) // Untuk COD atau Bank Transfer
            ->with('success', 'Metode pembayaran berhasil dipilih: ' . $pesanan->metode_pembayaran);
    }

    /**
     * Memproses checkout akhir setelah metode pembayaran dipilih.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id (id pesanan)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function prosesCheckoutAkhir(Request $request, $id)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melanjutkan.');
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);
        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $pesanan = Pesanan::where('id', $id)
            ->where('id_pelanggan', $id_pelanggan)
            ->first();

        if (!$pesanan) {
            return redirect()->route('pesanan.saya.index')->with('error', 'Pesanan tidak ditemukan atau bukan milik Anda.');
        }

        // Metode ini sekarang spesifik untuk COD, dipanggil dari form di halaman pembayaran
        $metodePembayaranPilihan = $request->input('metode_pembayaran_pilihan'); // Harusnya 'Cash On Delivery (COD)'

        if (strtolower($metodePembayaranPilihan) !== 'cash on delivery (cod)') {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
                ->with('error', 'Metode pembayaran tidak valid untuk proses ini.');
        }

        if ($pesanan->status_pesanan !== Pesanan::STATUS_MENUNGGU_PEMBAYARAN && $pesanan->status_pesanan !== Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY) {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
                ->with('error', 'Pesanan tidak dapat diproses untuk COD pada status saat ini.');
        }

        $pesanan->metode_pembayaran = 'Cash On Delivery (COD)';
        $pesanan->payment_gateway_name = null; // Pastikan tidak ada sisa gateway
        $pesanan->snap_token = null; // Hapus snap token jika ada
        $pesanan->status_pesanan = Pesanan::STATUS_DIPROSES; // Langsung diproses untuk COD

        $pesanan->save();

        // Buat notifikasi jika statusnya diproses (untuk COD)
        Notifikasi::create([
            'id_pelanggan' => $id_pelanggan,
            'tipe_notifikasi' => 'pesanan_diproses_cod',
            'judul' => 'Pesanan COD Diproses',
            'pesan' => "Pesanan Anda #{$pesanan->nomor_pesanan} dengan metode COD telah dikonfirmasi dan akan segera kami proses.",
            'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
            'id_pesanan_terkait' => $pesanan->id,
        ]);

        return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
            ->with('success', 'Pesanan COD Anda telah dikonfirmasi dan akan segera diproses.');
    }

    /**
     * Menampilkan halaman konfirmasi checkout dari keranjang.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function confirmCartCheckout(Request $request)
    {
        // 1. Cek Autentikasi Pengguna
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melanjutkan checkout.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // 2. Validasi Input (selected_items dari keranjang)
        $validator = Validator::make($request->all(), [
            'selected_items' => 'required|array', // Array of cart item IDs
            'selected_items.*' => 'integer|exists:keranjang,id', // Each ID must exist in the keranjang table
            'applied_discount_code' => 'nullable|string|exists:vouchers,kode', // Kode voucher jika ada
        ]);

        if ($validator->fails()) {
            return redirect()->route('keranjang.index')->withErrors($validator)->withInput();
        }

        $selectedItemIds = $request->input('selected_items');
        $appliedDiscountCode = $request->input('applied_discount_code');

        // 3. Ambil Item Keranjang yang Dipilih
        $selectedCartItems = Keranjang::whereIn('id', $selectedItemIds)
            ->where('id_pelanggan', $id_pelanggan)
            ->with('produk') // Eager load produk untuk cek stok dan detail
            ->get();

        if ($selectedCartItems->isEmpty()) {
            return redirect()->route('keranjang.index')->with('error', 'Tidak ada item yang dipilih untuk checkout.');
        }

        // 4. Cek Stok (Opsional di sini, bisa juga di proses store akhir)
        // foreach ($selectedCartItems as $item) {
        //     if (!$item->produk || $item->produk->stok < $item->jumlah) {
        //         return redirect()->route('keranjang.index')->with('error', 'Stok produk "' . ($item->produk->nama_produk ?? 'Produk tidak tersedia') . '" tidak mencukupi atau produk tidak valid.');
        //     }
        // }

        // 5. Ambil Data Pendukung (Alamat, Voucher)
        $daftarAlamat = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)->orderBy('is_utama', 'desc')->get();
        $alamatPengirimanUtama = $daftarAlamat->where('is_utama', true)->first() ?? $daftarAlamat->first(); // Ambil utama atau yang pertama

        $daftarVoucher = Voucher::where('aktif', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->get();

        // Pass applied discount code to the view to pre-select the voucher dropdown
        $appliedVoucherObject = null;
        if ($appliedDiscountCode) {
            $appliedVoucherObject = $daftarVoucher->firstWhere('kode', $appliedDiscountCode);
        }

        // Kalkulasi Ongkos Kirim dan Estimasi untuk ditampilkan di halaman konfirmasi
        $ongkosKirim = 0;
        $estimasiPengiriman = 'Estimasi tidak tersedia'; // Default
        if ($alamatPengirimanUtama && $alamatPengirimanUtama->latitude && $alamatPengirimanUtama->longitude) {
            try {
                $distance = $this->shippingService->calculateDistance($alamatPengirimanUtama->latitude, $alamatPengirimanUtama->longitude);
                if ($distance !== null) {
                    $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                    $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Tidak dapat menghitung jarak untuk alamat ID: " . $alamatPengirimanUtama->id . " pada PesananController@confirmCartCheckout");
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error calculating shipping in PesananController@confirmCartCheckout: ' . $e->getMessage());
            }
        }

        // 6. Render Konfirmasi View
        return view('frontend.pesanan.detail', [
            'selectedCartItems' => $selectedCartItems, // Pass selected cart items
            'daftarAlamat' => $daftarAlamat,
            'alamatPengiriman' => $alamatPengirimanUtama, // Pass default selected address
            'daftarVoucher' => $daftarVoucher,
            'appliedVoucher' => $appliedVoucherObject, // Pass applied voucher object
            'ongkosKirim' => $ongkosKirim, // Kirim ongkos kirim
            'estimasiPengiriman' => $estimasiPengiriman, // Kirim estimasi pengiriman
        ]);
    }
    /**
     * Menghitung ongkos kirim dan estimasi secara dinamis berdasarkan ID alamat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateDynamicShipping(Request $request)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return response()->json(['error' => 'Sesi tidak valid. Silakan login kembali.'], 401);
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);
        if (!$id_pelanggan) {
            return response()->json(['error' => 'Sesi tidak valid.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'alamat_pengiriman_id' => 'required|integer|exists:alamat_pengiriman,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $alamatPengiriman = AlamatPengiriman::where('id', $request->input('alamat_pengiriman_id'))
            ->where('id_pelanggan', $id_pelanggan)
            ->first();

        if (!$alamatPengiriman || !$alamatPengiriman->latitude || !$alamatPengiriman->longitude) {
            return response()->json(['ongkos_kirim' => 0, 'estimasi_pengiriman' => 'Koordinat alamat tidak valid.', 'error_detail' => 'Alamat tidak ditemukan atau koordinat tidak lengkap.'], 200); // Mengembalikan 0 jika alamat tidak valid
        }

        $distance = $this->shippingService->calculateDistance($alamatPengiriman->latitude, $alamatPengiriman->longitude);
        $ongkosKirim = ($distance !== null) ? $this->shippingService->calculateShippingCost($distance) : 0;
        $estimasiPengiriman = ($distance !== null) ? $this->shippingService->estimateDeliveryTime($distance) : 'Estimasi tidak tersedia';

        return response()->json([
            'ongkos_kirim' => $ongkosKirim,
            'estimasi_pengiriman' => $estimasiPengiriman,
            'formatted_ongkos_kirim' => 'Rp ' . number_format($ongkosKirim, 0, ',', '.'),
        ]);
    }

    /**
     * Memproses unggahan bukti barang diterima.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id (id pesanan)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadBuktiTerima(Request $request, $id)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melanjutkan.');
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        $pesanan = Pesanan::where('id', $id)
            ->where('id_pelanggan', $id_pelanggan)
            ->firstOrFail();

        if ($pesanan->status_pesanan != 'dikirim') {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])->with('error', 'Tidak dapat mengunggah bukti terima untuk status pesanan saat ini.');
        }

        $validator = Validator::make($request->all(), [
            'bukti_terima_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('bukti_terima_file')) {
            // Simpan file bukti terima
            $file = $request->file('bukti_terima_file');
            $fileName = 'bukti_terima_' . $pesanan->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('bukti_terima', $fileName, 'public'); // Simpan di storage/app/public/bukti_terima

            $pesanan->bukti_diterima = $path;  // Simpan path di kolom baru 'bukti_diterima' (tambahkan di tabel pesanan)
            $pesanan->status_pesanan = 'menunggu_konfirmasi_terima'; // Ubah status
            $pesanan->save();

            // Buat notifikasi (opsional)
            Notifikasi::create([
                'id_pelanggan' => $id_pelanggan,
                'tipe_notifikasi' => 'bukti_terima_diunggah',
                'judul' => 'Bukti Terima Diunggah',
                'pesan' => "Bukti penerimaan untuk pesanan #{$pesanan->nomor_pesanan} telah diunggah. Menunggu konfirmasi.",
                'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
                'id_pesanan_terkait' => $pesanan->id,
            ]);

            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
                ->with('success', 'Bukti penerimaan barang berhasil diunggah.  Pesanan menunggu konfirmasi penerimaan.');
        }

        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunggah bukti.')->withInput();
    }
    public function konfirmasiTerima($id)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melanjutkan.');
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        $pesanan = Pesanan::where('id', $id)
            ->where('id_pelanggan', $id_pelanggan)
            ->firstOrFail();

        if ($pesanan->status_pesanan != 'menunggu_konfirmasi_terima') {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])->with('error', 'Tidak dapat mengkonfirmasi penerimaan untuk status pesanan saat ini.');
        }

        $pesanan->status_pesanan = 'selesai'; // Ubah status menjadi selesai
        $pesanan->save();

        // Notifikasi (opsional)
        Notifikasi::create([
            'id_pelanggan' => $id_pelanggan,
            'tipe_notifikasi' => 'pesanan_selesai',
            'judul' => 'Pesanan Selesai',
            'pesan' => "Pesanan #{$pesanan->nomor_pesanan} telah dikonfirmasi diterima dan selesai.",
            'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
            'id_pesanan_terkait' => $pesanan->id,
        ]);

        return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
            ->with('success', 'Pesanan telah dikonfirmasi diterima dan selesai.');
    }

    /**
     * Mempersiapkan data untuk pembelian ulang (re-order) dan mengarahkan ke halaman konfirmasi.
     *
     * @param  int  $id (ID pesanan yang akan dibeli lagi)
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function beliLagi($id)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            Session::put('intended_url', request()->fullUrl());
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melakukan pembelian ulang.');
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        $pesananLama = Pesanan::with('detailPesanan.produk')
            ->where('id', $id)
            ->where('id_pelanggan', $id_pelanggan)
            ->firstOrFail(); // Akan 404 jika pesanan tidak ditemukan atau bukan milik user

        if ($pesananLama->detailPesanan->isEmpty()) {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesananLama->id])->with('error', 'Pesanan ini tidak memiliki item untuk dibeli ulang.');
        }

        // Persiapkan data seolah-olah dari keranjang
        $selectedCartItems = collect();
        $beliLagiMessages = []; // Untuk mengumpulkan pesan sukses, peringatan, atau error

        foreach ($pesananLama->detailPesanan as $itemLama) {
            if ($itemLama->produk) { // Pastikan produk masih ada
                // Cek stok produk saat ini
                if ($itemLama->produk->stok < $itemLama->jumlah) {
                    $beliLagiMessages[] = ['type' => 'warning', 'text' => 'Stok produk "' . $itemLama->produk->nama_produk . '" tidak mencukupi (' . $itemLama->produk->stok . ' tersedia). Item ini tidak ditambahkan ke pesanan baru.'];
                    continue; // Lewati item ini
                }

                // Buat objek yang mirip dengan item keranjang
                $selectedCartItems->push((object)[
                    'id' => null, // Tidak ada ID keranjang asli
                    'id_produk' => $itemLama->id_produk,
                    'produk_slug' => $itemLama->produk->slug, // Tambahkan slug jika diperlukan oleh view detail
                    'jumlah' => $itemLama->jumlah,
                    'harga_saat_dibeli' => $itemLama->produk->harga, // Gunakan harga produk saat ini
                    'harga_satuan_saat_order' => $itemLama->produk->harga, // Untuk konsistensi dengan view detail
                    'subtotal_harga' => $itemLama->jumlah * $itemLama->produk->harga, // Subtotal dengan harga saat ini
                    'produk' => $itemLama->produk, // Objek produk
                ]);
            } else {
                $beliLagiMessages[] = ['type' => 'warning', 'text' => 'Produk "' . $itemLama->nama_produk_saat_order . '" sudah tidak tersedia dan tidak dapat ditambahkan ke pesanan baru.'];
            }
        }

        // Jika tidak ada item yang berhasil ditambahkan, tambahkan pesan khusus
        if ($selectedCartItems->isEmpty() && empty($beliLagiMessages)) {
            $beliLagiMessages[] = ['type' => 'error', 'text' => 'Tidak ada produk yang dapat dibeli ulang dari pesanan ini.'];
        } elseif ($selectedCartItems->isEmpty() && !empty($beliLagiMessages)) {
            // Pesan sudah ada dari loop, mungkin tambahkan ringkasan
            // $beliLagiMessages[] = ['type' => 'info', 'text' => 'Beberapa item tidak dapat ditambahkan. Silakan periksa detail di atas.'];
        }

        // Ambil data pendukung seperti alamat dan voucher
        $daftarAlamat = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)->orderBy('is_utama', 'desc')->get();
        $alamatPengirimanUtama = $daftarAlamat->where('is_utama', true)->first() ?? $daftarAlamat->first();

        $daftarVoucher = Voucher::where('aktif', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->get();

        $ongkosKirim = 0;
        $estimasiPengiriman = 'Estimasi tidak tersedia';
        if ($alamatPengirimanUtama && $alamatPengirimanUtama->latitude && $alamatPengirimanUtama->longitude) {
            try {
                $distance = $this->shippingService->calculateDistance($alamatPengirimanUtama->latitude, $alamatPengirimanUtama->longitude);
                if ($distance !== null) {
                    $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                    $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error calculating shipping in PesananController@beliLagi: ' . $e->getMessage());
            }
        }

        // Arahkan ke view 'frontend.pesanan.detail' dengan data yang sudah disiapkan
        // View ini akan berfungsi sebagai halaman konfirmasi pesanan
        return view('frontend.pesanan.detail', compact('selectedCartItems', 'daftarAlamat', 'alamatPengirimanUtama', 'daftarVoucher', 'ongkosKirim', 'estimasiPengiriman', 'beliLagiMessages'));
    }

    /**
     * Menangani notifikasi dari Midtrans.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function handleMidtransNotification(Request $request)
    {
        Log::info('Midtrans callback masuk ke controller');
        if (!$request->isMethod('post')) {
            return response()->json(['status' => 'error', 'message' => 'Invalid method'], 405);
        }
        $result = $this->midtransService->handleNotification($request->all());
        return response()->json($result, $result['status'] == 'ok' ? 200 : 500);
    }

    /**
     * Mengirim pesanan dan menugaskan kendaraan pengirim jika tersedia.
     *
     * @param  int  $id (ID pesanan yang akan dikirim)
     * @return \Illuminate\Http\RedirectResponse
     */
    public function kirimPesanan($id)
    {
        $pesanan = Pesanan::findOrFail($id);

        // Cek kendaraan yang available
        $kendaraan = KendaraanPengirim::where('status', 'available')->first();

        if ($kendaraan) {
            $pesanan->id_kendaraan_pengirim = $kendaraan->id;
            $pesanan->status_pesanan = 'dikirim';
            $pesanan->tanggal_pengiriman = now();
            $pesanan->save();

            // Update status kendaraan
            $kendaraan->status = 'on_delivery';
            $kendaraan->save();

            // ...notifikasi, dsb...
            return redirect()->back()->with('success', 'Pesanan berhasil dikirim dan kendaraan sudah diassign.');
        } else {
            // Tidak ada kendaraan available
            return redirect()->back()->with('error', 'Semua kendaraan sedang digunakan. Pesanan akan dikirim setelah kendaraan tersedia.');
        }
    }

    public function konfirmasiPesananSelesai($id)
    {
        $pesanan = Pesanan::with('kendaraanPengirim')->findOrFail($id);

        // Update status pesanan
        $pesanan->status_pesanan = 'selesai';
        $pesanan->save();

        // Update status kendaraan menjadi available lagi
        if ($pesanan->kendaraanPengirim) {
            $pesanan->kendaraanPengirim->status = 'available';
            $pesanan->kendaraanPengirim->save();
        }

        // ...notifikasi, redirect, dll
    }
}
