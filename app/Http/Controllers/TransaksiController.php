<?php

namespace App\Http\Controllers;

use App\Models\Pesanan; // Import model Pesanan
use App\Models\AlamatPengiriman; // Import model AlamatPengiriman
use App\Models\Voucher; // Import model Voucher
use App\Models\Produk; // Sesuaikan dengan model produk Anda
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\ShippingService; // Import ShippingService
use App\Services\MidtransService; // Import MidtransService

class TransaksiController extends Controller
{
    protected ShippingService $shippingService;
    protected MidtransService $midtransService;

    public function __construct(ShippingService $shippingService, MidtransService $midtransService) // Inject di sini
    {
        $this->shippingService = $shippingService;
        $this->midtransService = $midtransService;
    }
    public function detail($produkId)
    {
        $produk = Produk::findOrFail($produkId);
        $jumlahBeli = (int) request()->query('jumlah', 1); // Ambil jumlah dari query string, default 1

        if (!Session::has('pelanggan')) {
            return redirect()->route('login.form')->with('error', 'Anda harus login untuk melanjutkan transaksi.');
        }

        $pelangganSession = Session::get('pelanggan');
        $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

        if (!$id_pelanggan) {
            Session::forget('pelanggan');
            return redirect()->route('login.form')->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Ambil alamat pengiriman utama atau yang terakhir
        $alamatPengiriman = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)
            ->where('is_utama', true)
            ->first();
        if (!$alamatPengiriman) {
            $alamatPengiriman = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $daftarAlamat = AlamatPengiriman::where('id_pelanggan', $id_pelanggan)->orderBy('is_utama', 'desc')->get();
        $daftarVoucher = Voucher::where('aktif', true)
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_berakhir', '>=', now())
            // ->where('kuota', '>', DB::raw('digunakan')) // Jika ingin filter kuota
            ->get();

        // Kalkulasi Ongkos Kirim dan Estimasi untuk ditampilkan
        $ongkosKirim = 0;
        $estimasiPengiriman = 'Estimasi tidak tersedia'; // Default
        if ($alamatPengiriman && $alamatPengiriman->latitude && $alamatPengiriman->longitude) {
            try {
                $distance = $this->shippingService->calculateDistance($alamatPengiriman->latitude, $alamatPengiriman->longitude);
                if ($distance !== null) {
                    $ongkosKirim = $this->shippingService->calculateShippingCost($distance);
                    $estimasiPengiriman = $this->shippingService->estimateDeliveryTime($distance);
                }
            } catch (\Exception $e) {
                // Log error jika ada masalah kalkulasi, ongkir tetap default
                \Illuminate\Support\Facades\Log::error('Error calculating shipping in TransaksiController: ' . $e->getMessage());
            }
        }

        // Kalkulasi rincian pembayaran awal (bisa di-override jika voucher dipilih)
        $rincianPembayaran = (object) [
            'subtotal' => $produk->harga * $jumlahBeli,
            'diskon' => 0,
            'ongkosKirim' => $ongkosKirim,
            'total' => ($produk->harga * $jumlahBeli) + $ongkosKirim, // Total awal sudah termasuk ongkir
        ];
        return view('frontend.pesanan.detail', compact('produk', 'jumlahBeli', 'alamatPengiriman', 'daftarAlamat', 'daftarVoucher', 'rincianPembayaran', 'ongkosKirim', 'estimasiPengiriman'));
    }

    public function pembayaran($transaksiId)
    {
        // $transaksiId di sini adalah ID Pesanan
        $pesanan = Pesanan::with('detailPesanan.produk') // Eager load untuk ringkasan jika perlu
            ->findOrFail($transaksiId);

        $pelangganSession = Session::get('pelanggan');
        if (!$pelangganSession || (is_array($pelangganSession) ? $pelangganSession['id_pelanggan'] : $pelangganSession->id_pelanggan) != $pesanan->id_pelanggan) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan atau bukan milik Anda.');
        }

        // Hanya izinkan akses jika status memungkinkan pembayaran
        if (!in_array($pesanan->status_pesanan, [
            Pesanan::STATUS_MENUNGGU_PEMBAYARAN,
            Pesanan::STATUS_PEMBAYARAN_PENDING_GATEWAY, // Jika user kembali dari Midtrans dan status pending
            Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY,   // Jika pembayaran sebelumnya gagal
            // Pesanan::STATUS_MENUNGGU_BUKTI_PEMBAYARAN // Hapus ini jika tidak ingin ganti dari transfer ke Midtrans
        ])) {
            return redirect()->route('pesanan.saya.detail', ['id' => $pesanan->id])
                ->with('info', 'Status pesanan saat ini tidak memerlukan tindakan pembayaran lebih lanjut melalui halaman ini.');
        }

        $snapToken = null; // Inisialisasi snapToken sebagai null
        $midtransClientKey = config('services.midtrans.client_key');
        $errorMidtrans = null;

        // Coba generate Snap Token jika belum ada, atau jika metode adalah Midtrans dan tokennya mungkin perlu di-refresh
        // atau jika statusnya gagal dan ingin coba lagi.
        // Atau jika metode belum dipilih, kita asumsikan user mungkin mau bayar online
        if (
            empty($pesanan->snap_token) ||
            $pesanan->payment_gateway_name == 'midtrans' ||
            $pesanan->status_pesanan == Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY ||
            empty($pesanan->metode_pembayaran)
        ) {

            if (
                $pesanan->status_pesanan == Pesanan::STATUS_MENUNGGU_PEMBAYARAN ||
                $pesanan->status_pesanan == Pesanan::STATUS_PEMBAYARAN_GAGAL_GATEWAY
            ) {
                try {
                    Log::info('Mencoba mendapatkan Snap Token untuk pesanan ID: ' . $pesanan->id . ' di TransaksiController@pembayaran.');
                    $pesanan->loadMissing(['pelanggan', 'detailPesanan.produk', 'alamatPengiriman']);
                    $snapToken = $this->midtransService->getSnapToken($pesanan); // Ini akan menyimpan token ke pesanan jika berhasil
                    if (!$snapToken) {
                        Log::warning('Snap Token bernilai null setelah pemanggilan getSnapToken untuk pesanan ID: ' . $pesanan->id);
                        $errorMidtrans = "Gagal memproses pembayaran online saat ini. Silakan coba metode COD atau hubungi kami.";
                    }
                } catch (\Exception $e) {
                    Log::error('Gagal mendapatkan Snap Token di TransaksiController untuk pesanan ID ' . $pesanan->id . ': ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
                    $errorMidtrans = "Terjadi kesalahan saat menyiapkan pembayaran online. Silakan coba metode COD atau hubungi kami. Detail: " . $e->getMessage();
                }
            }
        } elseif ($pesanan->snap_token && $pesanan->payment_gateway_name == 'midtrans') {
            // Jika token sudah ada dan belum dibayar (misal user kembali ke halaman ini)
            $snapToken = $pesanan->snap_token;
        }

        return view('frontend.pesanan.pembayaran', compact('pesanan', 'snapToken', 'midtransClientKey', 'errorMidtrans'));
    }
}
