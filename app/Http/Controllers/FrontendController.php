<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keranjang; // Pastikan model Keranjang di-import
use App\Models\Produk; // Import model Produk
use App\Models\Kategori; // Import model Kategori
use Illuminate\Support\Facades\Redirect; // Import Redirect
use App\Models\Voucher; // Import model Voucher
use App\Models\Pesanan; // Import model Pesanan
use Illuminate\Support\Facades\Session; // Import Session
use Illuminate\Support\Facades\DB;
use App\Models\Pesan;
use App\Models\Notifikasi;
use App\Models\AlamatPengiriman; // Import model AlamatPengiriman
use Illuminate\Support\Facades\Config; // Untuk mengambil konfigurasi
use App\Services\ShippingService; // Import ShippingService
use Illuminate\Support\Facades\Log; // Import Log
use Illuminate\Support\Facades\Validator; // Import Validator
use App\Models\KendaraanPengirim;


class FrontendController extends Controller
{
    protected ShippingService $shippingService;
    public function __construct(ShippingService $shippingService) // Inject di sini
    {
        $this->shippingService = $shippingService;
    }
    public function index(Request $request) // Tambahkan Request $request
    {
        // Ambil semua kategori dari database
        $semuaKategori = Kategori::orderBy('nama_kategori', 'asc')->get();

        // Mulai query untuk produk, eager load relasi kategori
        $query = Produk::query()->with('kategori');

        // 1. Filter berdasarkan Keyword Pencarian (dari search bar di header atau form filter di home)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where('nama_produk', 'like', '%' . $searchTerm . '%');
        }

        // 2. Filter berdasarkan Harga Minimum
        if ($request->filled('min_price')) {
            $minPrice = (float) $request->input('min_price');
            $query->where('harga', '>=', $minPrice);
        }

        // 3. Filter berdasarkan Harga Maksimum
        if ($request->filled('max_price')) {
            $maxPrice = (float) $request->input('max_price');
            $query->where('harga', '<=', $maxPrice);
        }

        // 4. Filter berdasarkan Kategori (menggunakan slug kategori tunggal)
        // Jika 'category_filter' ada dan tidak kosong, maka filter berdasarkan slug tersebut.
        // Jika 'category_filter' kosong (misalnya dari pilihan "Semua Kategori"), maka tidak ada filter kategori yang diterapkan.
        if ($request->filled('category_filter')) {
            $selectedCategorySlug = $request->input('category_filter');
            $query->whereHas('kategori', function ($q) use ($selectedCategorySlug) {
                $q->where('slug', $selectedCategorySlug);
            });
        }

        // 5. Filter berdasarkan Warna
        if ($request->filled('colors')) {
            $selectedColors = $request->input('colors'); // Ini akan jadi array
            $query->whereIn('warna', $selectedColors);
        }

        // 6. Pengurutan (Relevansi/Harga)
        if ($request->input('sort_by') == 'price_asc') {
            $query->orderBy('harga', 'asc');
        } elseif ($request->input('sort_by') == 'price_desc') {
            $query->orderBy('harga', 'desc');
        } else {
            // Default sort, misalnya berdasarkan produk terbaru
            $query->orderBy('created_at', 'desc');
        }

        // Ambil produk dengan pagination (12 produk per halaman)
        $products = $query->paginate(12);
        $alamatUtama = null;
        $pelangganId = null;

        if (Session::has('pelanggan')) {
            $pelangganData = Session::get('pelanggan');

            if (is_object($pelangganData)) {
                // Jika objek (dari login atau setelah ProfileController diperbaiki)
                if (isset($pelangganData->id_pelanggan)) {
                    $pelangganId = $pelangganData->id_pelanggan;
                }
            } elseif (is_array($pelangganData)) {
                // Jika array (dari ProfileController sebelum diperbaiki)
                if (isset($pelangganData['id_pelanggan'])) {
                    $pelangganId = $pelangganData['id_pelanggan'];
                }
            }

            if ($pelangganId) {
                // Ambil alamat utama dari tabel alamat_pengiriman
                $alamatUtama = AlamatPengiriman::where('id_pelanggan', $pelangganId)
                    ->where('is_utama', true)
                    ->first();
            }
        }
        return view('frontend.home', [
            'products' => $products,
            'request' => $request, // Kirim request untuk mengisi ulang form filter
            'kategoriList' => $semuaKategori, // Kirim daftar kategori ke view
            'alamatUtama' => $alamatUtama // Kirim data alamat ke view

        ]);
    }

    public function detail($id): \Illuminate\View\View
    {
        // Ambil produk dari database berdasarkan ID, eager load relasi kategori
        // Jika produk tidak ditemukan, akan otomatis melempar ModelNotFoundException (404 page)
        $produk = Produk::with('kategori')->findOrFail($id);

        // Ambil produk terkait:
        // - Dari kategori yang sama
        // - Kecuali produk yang sedang dilihat
        // - Batasi jumlahnya (misal 4 produk)
        // - Urutkan secara acak atau berdasarkan kriteria lain (misal terbaru)
        $relatedProducts = [];
        if ($produk->kategori) { // Pastikan produk memiliki kategori
            $relatedProducts = Produk::where('id_kategori', $produk->id_kategori)
                ->where('id', '!=', $produk->id) // Jangan tampilkan produk yang sama
                ->inRandomOrder() // Tampilkan secara acak
                ->take(4) // Ambil 4 produk terkait
                ->get();
        }
        // Cek kendaraan available
        $kendaraanAvailable = KendaraanPengirim::where('status', 'available')->exists();

        return view('frontend.produk.detail', [
            'produk' => $produk, // Mengirim objek produk utama dari database
            'relatedProducts' => $relatedProducts, // Mengirim array produk terkait
            'kendaraanAvailable' => $kendaraanAvailable // Mengirim status kendaraan
        ]);
    }

    /**
     * Menampilkan halaman keranjang belanja.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function keranjang(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Cek apakah user sudah login (session 'pelanggan' ada)
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            // Jika belum login, alihkan ke halaman login dengan pesan error
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melihat keranjang belanja.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $itemsKeranjang = Keranjang::where('id_pelanggan', $id_pelanggan)
            ->with('produk') // Eager load data produk
            ->get();

        // Ambil voucher yang aktif dan berlaku dari database
        $availableVouchers = Voucher::where('aktif', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->get();
        $discountInfo = Session::get('cart_discount', null);

        return view('frontend.produk.keranjang', [
            'itemsKeranjang' => $itemsKeranjang,
            'availableVouchers' => $availableVouchers, // Mengirimkan $availableVouchers
            'discountInfo' => $discountInfo
        ]);
    }



    /**
     * Menambahkan produk ke keranjang belanja (menggunakan session).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToCart(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Cek apakah user sudah login
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            // Jika belum login, alihkan ke halaman login dengan pesan error
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk menambahkan produk ke keranjang.');
        }

        // Asumsikan session 'pelanggan' menyimpan array dengan 'id_pelanggan' atau objek model Pelanggan
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan'); // Hapus sesi yang tidak valid
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $request->validate([
            'product_id' => 'required|integer|exists:produk,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        $produk = Produk::find($productId);

        if (!$produk) {
            return Redirect::back()->with('error', 'Produk tidak ditemukan.');
        }

        // Cek apakah produk sudah ada di keranjang pelanggan
        $itemKeranjang = Keranjang::where('id_pelanggan', $id_pelanggan)
            ->where('id_produk', $productId)
            ->first();

        if ($itemKeranjang) {
            // Jika sudah ada, update jumlahnya
            $itemKeranjang->jumlah += $quantity;
            $itemKeranjang->subtotal_harga = $itemKeranjang->jumlah * $itemKeranjang->harga_saat_dibeli;
            $itemKeranjang->subtotal_berat = $itemKeranjang->jumlah * $itemKeranjang->berat_satuan_saat_dibeli;
            $itemKeranjang->save();
        } else {
            // Jika belum ada, buat item baru
            Keranjang::create([
                'id_pelanggan' => $id_pelanggan,
                'id_produk' => $productId,
                'jumlah' => $quantity,
                'harga_saat_dibeli' => $produk->harga,
                'subtotal_harga' => $quantity * $produk->harga,
                'berat_satuan_saat_dibeli' => $produk->berat,
                'subtotal_berat' => $quantity * $produk->berat,
            ]);
        }
        return Redirect::back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    /**
     * Menghapus item dari keranjang belanja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Keranjang  $keranjang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hapusItemKeranjang(Request $request, Keranjang $keranjang): \Illuminate\Http\RedirectResponse
    {
        // Cek apakah user sudah login
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melakukan aksi ini.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Pastikan item keranjang yang akan dihapus milik pelanggan yang sedang login
        if ($keranjang->id_pelanggan != $id_pelanggan) {
            return Redirect::route('keranjang.index')->with('error', 'Aksi tidak diizinkan.');
        }

        $keranjang->delete();

        return Redirect::route('keranjang.index')->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /**
     * Mengupdate jumlah item di keranjang belanja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Keranjang  $keranjang
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateItemKeranjang(Request $request, Keranjang $keranjang): \Illuminate\Http\RedirectResponse
    {
        // Cek apakah user sudah login
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melakukan aksi ini.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Pastikan item keranjang yang akan diupdate milik pelanggan yang sedang login
        if ($keranjang->id_pelanggan != $id_pelanggan) {
            return Redirect::route('keranjang.index')->with('error', 'Aksi tidak diizinkan.');
        }

        $request->validate([
            'jumlah' => 'required|integer|min:1', // Validasi jumlah minimal 1
        ]);

        $jumlahBaru = $request->input('jumlah');

        $keranjang->jumlah = $jumlahBaru;
        $keranjang->subtotal_harga = $jumlahBaru * $keranjang->harga_saat_dibeli;
        $keranjang->subtotal_berat = $jumlahBaru * $keranjang->berat_satuan_saat_dibeli;
        $keranjang->save();

        return Redirect::route('keranjang.index')->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    /**
     * Menghapus beberapa item terpilih dari keranjang belanja.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hapusItemTerpilih(Request $request): \Illuminate\Http\RedirectResponse
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melakukan aksi ini.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $selectedIds = $request->input('selected_ids', []);

        if (empty($selectedIds)) {
            return Redirect::route('keranjang.index')->with('info', 'Tidak ada item yang dipilih untuk dihapus.');
        }

        // Hapus item yang dimiliki oleh pelanggan dan ada dalam daftar ID terpilih
        Keranjang::where('id_pelanggan', $id_pelanggan)
            ->whereIn('id', $selectedIds)
            ->delete();

        return Redirect::route('keranjang.index')->with('success', 'Item terpilih berhasil dihapus dari keranjang.');
    }

    /**
     * Menerapkan kode diskon ke keranjang.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function applyDiscount(Request $request): \Illuminate\Http\RedirectResponse
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk menggunakan diskon.');
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);
        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Get the discount code, default to empty string if not provided or null
        $code = strtoupper(trim($request->input('discount_code', '')));

        // If no code is provided (user selected "-- Tidak Menggunakan Diskon --" which has value=""),
        // then remove any existing discount.
        if (empty($code)) {
            if (Session::has('cart_discount')) {
                Session::forget('cart_discount');
                return Redirect::route('keranjang.index')->with('success', 'Diskon berhasil dihapus.');
            }
            return Redirect::route('keranjang.index'); // No discount to apply or remove
        }

        // Ambil voucher dari database
        $voucher = Voucher::where('kode', $code)
            ->where('aktif', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            ->first();

        // Jika voucher tidak ditemukan atau tidak valid
        if (!$voucher) {
            return Redirect::route('keranjang.index')->with('error', 'Kode diskon tidak valid atau sudah tidak berlaku.');
        }

        // Hitung total dari item yang ada di keranjang (bukan yang terpilih, karena diskon diterapkan ke potensi pembelian)
        $itemsKeranjang = Keranjang::where('id_pelanggan', $id_pelanggan)->get();
        $currentCartTotal = $itemsKeranjang->sum('subtotal_harga');

        // Cek apakah total belanja memenuhi syarat minimal voucher
        if ($currentCartTotal < $voucher->min_pembelian) {
            return Redirect::route('keranjang.index')->with('error', 'Total belanja minimal Rp ' . number_format($voucher->min_pembelian, 0, ',', '.') . ' untuk menggunakan kode ' . $code . '.');
        }

        // Simpan detail diskon ke session
        Session::put('cart_discount', [
            'code' => $voucher->kode,
            'type' => $voucher->tipe_diskon, // Pastikan kolom ini ada di tabel vouchers ('persen' atau 'tetap')
            'value' => $voucher->nilai_diskon, // Pastikan kolom ini ada
            'description' => $voucher->deskripsi, // Pastikan kolom ini ada
            'min_spend' => $voucher->min_pembelian // Simpan min_spend untuk validasi di JS jika perlu
        ]);

        return Redirect::route('keranjang.index')->with('success', 'Diskon "' . $voucher->deskripsi . '" berhasil diterapkan.');
    }


    /**
     * Menghapus diskon yang diterapkan dari keranjang.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeDiscount(): \Illuminate\Http\RedirectResponse
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            // Sebenarnya tidak perlu login untuk hapus diskon dari session, tapi konsisten saja
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid.');
        }

        if (Session::has('cart_discount')) {
            Session::forget('cart_discount');
            return Redirect::route('keranjang.index')->with('success', 'Diskon berhasil dihapus.');
        }

        return Redirect::route('keranjang.index');
    }

    /**
     * Menampilkan halaman pesan pengguna.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function pesan(Request $request): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Cek apakah user sudah login
        if (!Session::has('pelanggan')) {
            // Jika belum login, alihkan ke halaman login dengan pesan error
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melihat pesan.');
        }

        $pelangganSession = Session::get('pelanggan');
        $idPelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$idPelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $receiver = $request->query('receiver'); // misalnya 'admin'
        $productId = $request->query('product_id');
        $orderId = $request->query('order_id');

        $chatContext = null;
        $prefillText = ''; // Teks awal untuk input chat
        $currentProductIdKonteks = null;
        $currentOrderIdKonteks = null;
        $produkKonteks = null; // Variabel untuk menyimpan objek produk
        $pesananKonteks = null; // Variabel untuk menyimpan objek pesanan

        if ($productId) {
            $produk = Produk::find($productId);
            if ($produk) {
                $chatContext = "Mengenai produk: " . $produk->nama_produk . " (ID: " . $produk->id . ")";
                $prefillText = "Saya ingin bertanya tentang produk " . $produk->nama_produk . "...";
            }
            $produkKonteks = $produk; // Simpan objek produk
            $currentProductIdKonteks = $produk->id ?? null;
        } elseif ($orderId) {
            $pesanan = Pesanan::where('nomor_pesanan', $orderId)
                ->with('detailPesanan.produk') // Eager load detail pesanan dan produknya
                ->first();
            if ($pesanan) {
                // Anda bisa mengambil detail pesanan lain jika perlu
                $chatContext = "Mengenai pesanan: #" . $pesanan->nomor_pesanan;
                $prefillText = "Saya ingin bertanya tentang pesanan saya #" . $pesanan->nomor_pesanan . "...";
            }
            $pesananKonteks = $pesanan; // Simpan objek pesanan
            $currentOrderIdKonteks = $pesanan->nomor_pesanan ?? null;
        }

        // Ambil histori chat untuk pelanggan ini
        $chatMessages = Pesan::where('id_pelanggan', $idPelanggan)
            // Jika ingin membatasi chat hanya dengan admin, bisa tambahkan kondisi lain
            // atau asumsikan semua chat pelanggan adalah dengan admin.
            ->orderBy('created_at', 'asc')
            ->get();
        // Tandai pesan dari admin sebagai sudah dibaca oleh pelanggan saat halaman chat dibuka
        Pesan::where('id_pelanggan', $idPelanggan)
            ->where('pengirim_adalah_admin', true)
            ->where('sudah_dibaca_oleh_pelanggan', false)
            ->update(['sudah_dibaca_oleh_pelanggan' => true]);

        return view('frontend.pesan', [
            'receiver' => $receiver,
            'chatContext' => $chatContext, // Kirim konteks ke view
            'prefillText' => $prefillText, // Kirim teks awal ke view
            'chatMessages' => $chatMessages,
            'currentProductIdKonteks' => $currentProductIdKonteks,
            'currentOrderIdKonteks' => $currentOrderIdKonteks,
            'produkKonteks' => $produkKonteks, // Kirim objek produk ke view
            'pesananKonteks' => $pesananKonteks, // Kirim objek pesanan ke view
        ]);
    }
    public function kirimPesan(Request $request)
    {
        $pelangganSession = Session::get('pelanggan');
        // Perbaikan: Akses id_pelanggan dari session dengan aman
        $idPelanggan = null;
        if ($pelangganSession) {
            $idPelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);
        }



        if (!$idPelanggan) {
            return response()->json(['error' => 'Sesi tidak valid. Silakan login kembali.'], 401);
        }

        // Validasi request
        $validated = $request->validate([
            'isi_pesan' => 'required|string',
            'id_produk_konteks' => 'nullable|exists:produk,id',
            'nomor_pesanan_konteks' => 'nullable|string|max:255',
            // 'receiver_id' => 'required' // Jika ada target spesifik selain admin
        ]);

        $pesan = Pesan::create([
            'id_pelanggan' => $idPelanggan,
            'pengirim_adalah_admin' => false, // Pesan dari pelanggan
            'isi_pesan' => $validated['isi_pesan'],
            'id_produk_konteks' => $validated['id_produk_konteks'] ?? null,
            'nomor_pesanan_konteks' => $validated['nomor_pesanan_konteks'] ?? null,
            'sudah_dibaca_oleh_admin' => false, // Admin belum baca
            'sudah_dibaca_oleh_pelanggan' => true, // Pengirim otomatis sudah baca
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => 'Pesan terkirim!', 'message' => $pesan]);
        }

        return redirect()->back()->with('success', 'Pesan berhasil dikirim!');
    }

    /**
     * Menampilkan halaman notifikasi pengguna.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function notifikasi(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if (!Session::has('pelanggan')) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melihat notifikasi.');
        }

        $pelangganSession = Session::get('pelanggan');
        $idPelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$idPelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $notifikasiList = Notifikasi::where('id_pelanggan', $idPelanggan)
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Menggunakan pagination, 10 notifikasi per halaman

        // Opsi: Tandai semua notifikasi yang belum dibaca sebagai dibaca saat halaman dibuka
        // Notifikasi::where('id_pelanggan', $idPelanggan)
        //            ->where('sudah_dibaca', false)
        //            ->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);

        return view('frontend.notifikasi', compact('notifikasiList'));
    }

    /**
     * Menandai notifikasi sebagai sudah dibaca.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request)
    {
        if (!Session::has('pelanggan')) {
            return response()->json(['error' => 'Anda harus login.'], 401);
        }

        $pelangganSession = Session::get('pelanggan');
        $idPelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        $notifikasiId = $request->input('id'); // Bisa satu ID atau 'all'

        if ($notifikasiId === 'all') {
            Notifikasi::where('id_pelanggan', $idPelanggan)
                ->where('sudah_dibaca', false)
                ->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);
        } elseif (is_numeric($notifikasiId)) {
            Notifikasi::where('id', $notifikasiId)
                ->where('id_pelanggan', $idPelanggan)
                ->update(['sudah_dibaca' => true, 'dibaca_pada' => now()]);
        }
        return redirect()->route('notifikasi.index')->with('success', 'Notifikasi telah ditandai sebagai dibaca.');
    }

    /**
     * Menghapus notifikasi yang dipilih oleh pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hapusNotifikasiTerpilih(Request $request)
    {
        $pelanggan = Session::get('pelanggan');
        if (!$pelanggan) {
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }
        $id_pelanggan = is_array($pelanggan) ? ($pelanggan['id_pelanggan'] ?? null) : ($pelanggan->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            return redirect()->back()->with('error', 'Gagal mendapatkan ID pelanggan.');
        }

        // Logika ini sudah menangani penghapusan berdasarkan ID yang dipilih
        if ($request->has('selected_notif_ids') && is_array($request->input('selected_notif_ids'))) {
            $selectedIds = $request->input('selected_notif_ids');
            if (empty($selectedIds)) {
                return redirect()->route('notifikasi.index')->with('info', 'Tidak ada notifikasi yang dipilih untuk dihapus.');
            }

            // Hapus notifikasi yang dipilih milik pelanggan
            Notifikasi::where('id_pelanggan', $id_pelanggan)
                ->whereIn('id', $selectedIds)
                ->delete();

            return redirect()->route('notifikasi.index')->with('success', 'Notifikasi yang dipilih berhasil dihapus.');
        }

        return redirect()->route('notifikasi.index')->with('info', 'Tidak ada tindakan penghapusan yang dilakukan.');
    }

    public function updateStatusPesanan(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::findOrFail($id_pesanan);

        Log::info('updateStatusPesanan called', [
            'pesanan_id' => $id_pesanan,
            'request_status_baru' => $request->input('status_baru'),
        ]);
        $statusLama = $pesanan->status_pesanan;
        $statusBaru = $request->input('status_baru'); // Misal dari form admin

        $pesanan->status_pesanan = $statusBaru;
        $pesanan->save();

        // Buat notifikasi untuk pelanggan
        $judulNotif = '';
        $pesanNotif = '';
        $tipeNotif = '';

        Log::info('Status change check', [
            'status_lama' => $statusLama,
            'status_baru' => $statusBaru,
        ]);

        if ($statusBaru == 'diproses' && $statusLama != 'diproses') {
            $judulNotif = 'Pesanan Diproses';
            $pesanNotif = "Pesanan Anda #{$pesanan->nomor_pesanan} sedang kami proses dan kemas.";
            $tipeNotif = 'pesanan_diproses'; // atau 'pesanan_dikemas'
        } elseif ($statusBaru == 'dikirim' && $statusLama != 'dikirim') {
            $judulNotif = 'Pesanan Dikirim';
            // Pastikan ada kolom nomor_resi_pengiriman di tabel pesanan jika ingin menyertakan nomor resi
            $pesanNotif = "Pesanan Anda #{$pesanan->nomor_pesanan} telah dikirim.";
            if (!empty($pesanan->nomor_resi_pengiriman)) {
                $pesanNotif .= " No. Resi: {$pesanan->nomor_resi_pengiriman}.";
            } else {
                $pesanNotif .= " Anda akan segera menerima informasi nomor resi.";
            }
            $tipeNotif = 'pesanan_dikirim';
        } // Tambahkan kondisi lain sesuai kebutuhan

        Log::info('Notification check result', [
            'judul_notif' => $judulNotif,
        ]);

        if (!empty($judulNotif)) {
            Notifikasi::create([
                'id_pelanggan' => $pesanan->id_pelanggan,
                'tipe_notifikasi' => $tipeNotif,
                'judul' => $judulNotif,
                'pesan' => $pesanNotif,
                'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]), // Link ke detail pesanan
                'id_pesanan_terkait' => $pesanan->id,
            ]);
        }

        // ... (redirect atau response lainnya)
    }
    /**
     * Menampilkan halaman pesanan saya.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function pesananSaya(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melihat pesanan Anda.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $activeTab = request()->get('status', 'semua'); // Default ke tab 'semua' atau status yang dipilih

        $query = Pesanan::where('id_pelanggan', $id_pelanggan)
            ->with('detailPesanan.produk') // Eager load detail pesanan dan produknya
            ->orderBy('tanggal_pesanan', 'desc');

        if ($activeTab !== 'semua' && $activeTab !== '') {
            // Sesuaikan nilai status dengan yang ada di database Anda
            // Contoh: 'belum-bayar' di URL mungkin 'menunggu_pembayaran' di DB
            $statusMapping = [
                'belum-bayar' => 'menunggu_pembayaran',
                'dikemas' => 'diproses', // atau 'dikemas' jika itu nama status di DB
                'dikirim' => 'dikirim',
                'selesai' => 'selesai',
                // tambahkan mapping lain jika perlu
            ];
            if (array_key_exists($activeTab, $statusMapping)) {
                $query->where('status_pesanan', $statusMapping[$activeTab]);
            }
        }

        $filteredPesanan = $query->get();

        // Hitung jumlah pesanan per status
        $statusCounts = [
            'semua' => Pesanan::where('id_pelanggan', $id_pelanggan)->count(),
            'belum-bayar' => Pesanan::where('id_pelanggan', $id_pelanggan)->where('status_pesanan', 'menunggu_pembayaran')->count(),
            'dikemas' => Pesanan::where('id_pelanggan', $id_pelanggan)->where('status_pesanan', 'diproses')->count(),
            'dikirim' => Pesanan::where('id_pelanggan', $id_pelanggan)->where('status_pesanan', 'dikirim')->count(),
            'selesai' => Pesanan::where('id_pelanggan', $id_pelanggan)->where('status_pesanan', 'selesai')->count(),
        ];

        return view('frontend.pesanan.pesanan', [
            'semuaPesanan' => $filteredPesanan,
            'activeTab' => $activeTab,
            'statusCounts' => $statusCounts
        ]);
    }

    /**
     * Menampilkan detail pesanan spesifik.
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function pesananDetail($id)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melihat detail pesanan.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return Redirect::route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $pesanan = Pesanan::with([
            'alamatPengiriman',
            'voucher',
            'detailPesanan.produk' // Eager load produk untuk setiap detail pesanan
        ])
            ->where('id_pelanggan', $id_pelanggan)
            ->findOrFail($id); // Akan menampilkan 404 jika pesanan tidak ditemukan atau bukan milik pelanggan

        $midtransClientKey = config('services.midtrans.client_key');

        return view('frontend.pesanan.show', compact('pesanan', 'midtransClientKey'));
    }


    /**
     * Membatalkan pesanan pengguna.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batalPesanan(Request $request)
    {
        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk membatalkan pesanan.');
        }

        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $request->validate([
            'id_pesanan' => 'required|integer|exists:pesanan,id',
        ]);

        $pesanan = Pesanan::with('detailPesanan.produk')->where('id', $request->input('id_pesanan')) // Eager load detail dan produk
            ->where('id_pelanggan', $id_pelanggan)
            ->first();

        if (!$pesanan) {
            return redirect()->route('pesanan.saya.index')->with('error', 'Pesanan tidak ditemukan atau Anda tidak berhak membatalkannya.');
        }

        // Logika pembatalan pesanan (misalnya, update status, kembalikan stok jika perlu)
        if (in_array($pesanan->status_pesanan, ['menunggu_pembayaran', 'diproses'])) {
            try {
                DB::beginTransaction();

                $pesanan->status_pesanan = 'dibatalkan';
                $pesanan->save();

                // Kembalikan stok produk
                foreach ($pesanan->detailPesanan as $item) {
                    if ($item->produk) { // Pastikan produk masih ada
                        $item->produk->stok += $item->jumlah;
                        $item->produk->save();
                    }
                }
                DB::commit();

                // Buat notifikasi untuk pelanggan
                Notifikasi::create([
                    'id_pelanggan' => $id_pelanggan,
                    'tipe_notifikasi' => 'pesanan_dibatalkan_user',
                    'judul' => 'Pesanan Dibatalkan',
                    'pesan' => "Pesanan Anda #{$pesanan->nomor_pesanan} telah berhasil Anda batalkan.",
                    'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]), // Link ke detail pesanan yang dibatalkan
                    'id_pesanan_terkait' => $pesanan->id,
                ]);

                return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])->with('success', 'Pesanan berhasil dibatalkan dan stok telah dikembalikan.');
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
            }
        }
        return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])->with('error', 'Pesanan tidak dapat dibatalkan pada status ini.');
    }

    /**
     * Menampilkan halaman pembayaran.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function pembayaran(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // Cek apakah user sudah login
        if (!Session::has('pelanggan')) {
            // Jika belum login, alihkan ke halaman login dengan pesan error
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melanjutkan pembayaran.');
        }

        return view('frontend.pesanan.pembayaran'); // Asumsi view ada di frontend.pembayaran.index
    }

    /**
     * Menampilkan halaman lacak pengiriman berdasarkan ID Pesanan.
     *
     * @param  string  $id_pesanan
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function lacakPengirimanByPesanan($id_pesanan): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        if (!Session::has('pelanggan')) {
            return Redirect::route('login.form')->with('error', 'Anda harus login untuk melacak pengiriman.');
        }

        $pesanan = Pesanan::with('kendaraanPengirim')->findOrFail($id_pesanan);

        // Ambil info kendaraan pengirim jika ada
        $vehicleInfo = null;
        if ($pesanan->kendaraanPengirim) {
            $vehicleInfo = [
                'type'         => $pesanan->kendaraanPengirim->type,
                'plate_number' => $pesanan->kendaraanPengirim->plate_number,
                'driver_name'  => $pesanan->kendaraanPengirim->driver_name,
                'driver_phone' => $pesanan->kendaraanPengirim->driver_phone,
                // Tambahkan field lain jika perlu
            ];
        }

        $warehouseCoords = [
            'lat' => config('shipping.origin.latitude', -6.966667), // Ambil dari config/shipping.php
            'lng' => config('shipping.origin.longitude', 110.416664), // Ambil dari config/shipping.php
        ];

        $destinationCoords = [
            'lat' => $pesanan->alamatPengiriman->latitude,
            'lng' => $pesanan->alamatPengiriman->longitude,
        ];

        $googleMapsApiKey = config('services.google_maps.api_key'); // Ambil dari config/services.php

        return view('frontend.pesanan.lacak_map', compact(
            'pesanan',
            'vehicleInfo',
            'warehouseCoords',
            'destinationCoords',
            'googleMapsApiKey'
        ));
    }
    public function lacakPengirimanPeta($nomor_pesanan)
    {
        $pesanan = Pesanan::where('nomor_pesanan', $nomor_pesanan)
            ->with('alamatPengiriman') // Eager load alamat pengiriman
            ->first();

        if (!$pesanan || !$pesanan->alamatPengiriman || $pesanan->status_pesanan !== 'dikirim' || !$pesanan->nomor_resi) {
            // Jika pesanan tidak ditemukan, tidak ada alamat, belum dikirim, atau tidak ada resi
            return redirect()->route('pesanan.saya.index')->with('error', 'Informasi pelacakan peta tidak tersedia untuk pesanan ini.');
        }

        $dataPelanggan = Session::get('pelanggan');
        if (!$dataPelanggan || (is_array($dataPelanggan) ? $dataPelanggan['id_pelanggan'] : $dataPelanggan->id_pelanggan) != $pesanan->id_pelanggan) {
            // Jika bukan pemilik pesanan
            return redirect()->route('home')->with('error', 'Anda tidak diizinkan mengakses halaman ini.');
        }

        $warehouseCoords = [
            'lat' => config('shipping.origin.latitude', -6.966667), // Ambil dari config/shipping.php
            'lng' => config('shipping.origin.longitude', 110.416664), // Ambil dari config/shipping.php
        ];

        $destinationCoords = [
            'lat' => $pesanan->alamatPengiriman->latitude,
            'lng' => $pesanan->alamatPengiriman->longitude,
        ];

        // Data kendaraan (contoh, ini bisa diambil dari database jika ada)
        $vehicleInfo = [
            'type' => 'Truk Box Sedang',
            'plate_number' => 'B 1234 JFT',
            'driver_name' => 'Bambang S.',
            'driver_phone' => '0812xxxxxxx',
            // Untuk pelacakan real-time, Anda memerlukan data lokasi kendaraan saat ini
            // 'current_lat' => -6.210000, // Contoh lokasi kendaraan saat ini
            // 'current_lng' => 106.820000, // Contoh lokasi kendaraan saat ini
        ];

        $googleMapsApiKey = config('services.google_maps.api_key'); // Ambil dari config/services.php

        return view('frontend.pesanan.lacak_map', compact(
            'pesanan',
            'warehouseCoords',
            'destinationCoords',
            'vehicleInfo',
            'googleMapsApiKey'
        ));
    }
    public function hitungUlangOngkir(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alamat_id' => 'required|exists:alamat_pengiriman,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak valid.']);
        }

        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession) {
            return response()->json(['success' => false, 'message' => 'Sesi pengguna tidak ditemukan.']);
        }
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        $alamat = AlamatPengiriman::where('id', $request->alamat_id)
            ->where('id_pelanggan', $id_pelanggan)
            ->first();

        if (!$alamat || !$alamat->latitude || !$alamat->longitude) {
            return response()->json(['success' => false, 'message' => 'Detail alamat tidak lengkap atau tidak ditemukan.']);
        }

        try {
            $distance = $this->shippingService->calculateDistance($alamat->latitude, $alamat->longitude);
            $ongkosKirim = 0;
            $estimasiPengiriman = 'Estimasi tidak tersedia';

            if ($distance !== null) {
                $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
            }

            return response()->json([
                'success' => true,
                'ongkos_kirim' => $ongkosKirim,
                'ongkos_kirim_formatted' => 'Rp ' . number_format($ongkosKirim, 0, ',', '.'),
                'estimasi_pengiriman' => $estimasiPengiriman,
            ]);
        } catch (\Exception $e) {
            Log::error('Error recalculating shipping: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghitung ongkos kirim. Silakan coba lagi.']);
        }
    }
}
