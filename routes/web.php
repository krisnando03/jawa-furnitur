<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\ValidationController; // Sesuaikan namespace jika perlu
use App\Http\Controllers\ProfileController; // Tambahkan ini
use App\Http\Controllers\TransaksiController; // Pastikan Controller sudah ada
use App\Http\Controllers\PesananController; // Sesuaikan dengan namespace dan nama controller Anda

Route::get('/', [FrontendController::class, 'index'])->name('home'); // Pastikan route home dinamai
Route::get('/produk', [FrontendController::class, 'produk'])->name('produk.index');
Route::get('/produk/{id}', [FrontendController::class, 'detail'])->name('produk.detail');

Route::get('/login', [LoginController::class, 'showForm'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login'); // Changed name to 'login'
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register'); // Changed name to 'register'
Route::post('/check-availability', [ValidationController::class, 'checkAvailability'])->name('check.availability');
Route::post('/check-login-email', [ValidationController::class, 'checkLoginEmail'])->name('check.login.email');

// Route untuk Profil Pengguna
Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/alamat/store', [ProfileController::class, 'storeAlamat'])->name('profile.alamat.store');
// Route untuk mengedit dan menghapus alamat
Route::put('/profile/alamat/{alamat}', [ProfileController::class, 'updateAlamat'])->name('profile.alamat.update');
Route::delete('/profile/alamat/{alamat}', [ProfileController::class, 'destroyAlamat'])->name('profile.alamat.destroy');
Route::post('/profile/alamat/{alamat}/set-utama', [ProfileController::class, 'setAlamatUtama'])->name('profile.alamat.setUtama');
// Route untuk Halaman Keranjang
Route::get('/keranjang', [FrontendController::class, 'keranjang'])->name('keranjang.index');
Route::post('/keranjang/add', [FrontendController::class, 'addToCart'])->name('keranjang.add'); // Tambahkan route ini
Route::post('/keranjang/hapus/{keranjang}', [FrontendController::class, 'hapusItemKeranjang'])->name('keranjang.hapus'); // Route untuk hapus item
Route::post('/keranjang/hapus-terpilih', [FrontendController::class, 'hapusItemTerpilih'])->name('keranjang.hapus.terpilih'); // Route untuk hapus item terpilih
Route::post('/keranjang/apply-discount', [FrontendController::class, 'applyDiscount'])->name('keranjang.apply_discount');
Route::get('/keranjang/remove-discount', [FrontendController::class, 'removeDiscount'])->name('keranjang.remove_discount');
Route::post('/keranjang/update/{keranjang}', [FrontendController::class, 'updateItemKeranjang'])->name('keranjang.update'); // Route untuk update jumlah
Route::post('/checkout/from-cart', [PesananController::class, 'checkoutFromCart'])->name('checkout.from.cart');

// Route untuk Halaman Pesan
Route::get('/pesan', [FrontendController::class, 'pesan'])->name('pesan.index');
Route::post('/pesan/kirim', [FrontendController::class, 'kirimPesan'])->name('pesan.kirim');
Route::post('/pesan/checkout', [FrontendController::class, 'checkout'])->name('pesan.checkout'); // Tambahkan route ini

// Route untuk Halaman Notifikasi
Route::get('/notifikasi', [FrontendController::class, 'notifikasi'])->name('notifikasi.index');
Route::post('/notifikasi/read', [FrontendController::class, 'markAsRead'])->name('notifikasi.read'); // Tambahkan route ini
Route::post('/notifikasi/hapus-terpilih', [FrontendController::class, 'hapusNotifikasiTerpilih'])->name('notifikasi.hapus.terpilih');

// Route untuk Halaman Pesanan Saya
Route::get('/pesanan-saya', [FrontendController::class, 'pesananSaya'])->name('pesanan.saya.index');
Route::get('/pesanan-saya/{id}', [FrontendController::class, 'pesananDetail'])->name('pesanan.saya.detail'); // Tambahkan route ini
Route::post('/pesanan-saya/batal', [FrontendController::class, 'batalPesanan'])->name('pesanan.saya.batal'); // Tambahkan route ini
Route::post('/pesanan-saya/konfirmasi', [FrontendController::class, 'konfirmasiPesanan'])->name('pesanan.saya.konfirmasi'); // Tambahkan route ini
Route::post('/pesanan/{id}/bukti-terima', [PesananController::class, 'uploadBuktiTerima'])->name('pesanan.uploadBuktiTerima');
Route::post('/pesanan/{id}/konfirmasi-terima', [PesananController::class, 'konfirmasiTerima'])->name('pesanan.konfirmasiTerima');
Route::get('/pesanan/{id}/beli-lagi', [PesananController::class, 'beliLagi'])->name('pesanan.beliLagi');
Route::get('/pesanan/{nomor_pesanan}/lacak-peta', [FrontendController::class, 'lacakPengirimanPeta'])->name('pesanan.lacak.peta');
Route::post('/pesanan/hitung-ulang-ongkir', [FrontendController::class, 'hitungUlangOngkir'])->name('pesanan.hitungUlangOngkir');

// Route untuk Halaman Pembayaran
Route::get('/pembayaran', [FrontendController::class, 'pembayaran'])->name('pembayaran.index');

// Route untuk Halaman Lacak Pengiriman
Route::get('/lacak-pengiriman/pesanan/{id_pesanan}', [FrontendController::class, 'lacakPengirimanByPesanan'])->name('lacak.pengiriman.pesanan');

Route::get('/transaksi/{produkId}/detail', [TransaksiController::class, 'detail'])->name('transaksi.detail');
Route::get('/transaksi/{transaksiId}/pembayaran', [TransaksiController::class, 'pembayaran'])->name('transaksi.pembayaran');

// Route untuk menyimpan pesanan baru
Route::post('/pesanan/store', [PesananController::class, 'store'])->name('pesanan.store');

Route::post('/checkout/confirm-cart', [PesananController::class, 'confirmCartCheckout'])->name('checkout.confirm.cart');
// Route untuk update metode pembayaran
Route::post('/pesanan/update-metode-pembayaran', [PesananController::class, 'updateMetodePembayaran'])->name('pesanan.updateMetodePembayaran');
// Route untuk "Buy Now"
Route::post('/pesanan/buy-now', [PesananController::class, 'buyNow'])->name('pesanan.buyNow');
Route::post('/pesanan/{id}/checkout-akhir', [PesananController::class, 'prosesCheckoutAkhir'])->name('pesanan.prosesCheckoutAkhir');

// Route untuk kalkulasi ongkir dinamis
Route::post('/calculate-shipping-cost', [PesananController::class, 'calculateDynamicShipping'])->name('pesanan.calculateDynamicShipping');

Route::post('/payment/midtrans/notification', [PesananController::class, 'handleMidtransNotification']);
