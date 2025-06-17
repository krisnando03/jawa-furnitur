<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Pelanggan;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Notification;
use Exception;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function getSnapToken(Pesanan $pesanan)
    {
        Log::info('Memulai proses getSnapToken untuk pesanan ID: ' . $pesanan->id);

        if (empty($pesanan->pelanggan) || empty($pesanan->detailPesanan)) {
            Log::error('MidtransService: Pelanggan atau Detail Pesanan kosong untuk pesanan ID ' . $pesanan->id);
            throw new \Exception("Data pelanggan atau detail pesanan tidak lengkap.");
        }

        $item_details = [];
        foreach ($pesanan->detailPesanan as $item) {
            $item_details[] = [
                'id'       => $item->id_produk,
                'price'    => (int)$item->harga_satuan_saat_order,
                'quantity' => (int)$item->jumlah,
                'name'     => substr($item->nama_produk_saat_order, 0, 50)
            ];
        }

        if ($pesanan->ongkos_kirim > 0) {
            $item_details[] = [
                'id'       => 'ONGKIR',
                'price'    => (int)$pesanan->ongkos_kirim,
                'quantity' => 1,
                'name'     => 'Biaya Pengiriman'
            ];
        }

        if ($pesanan->diskon > 0) {
            $item_details[] = [
                'id'       => 'DISKON_VOUCHER',
                'price'    => (int)(-$pesanan->diskon),
                'quantity' => 1,
                'name'     => 'Diskon Voucher'
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $pesanan->nomor_pesanan . '_' . time(),
                'gross_amount' => (int)$pesanan->total_pembayaran,
            ],
            'item_details'        => $item_details,
            'customer_details'    => [
                'first_name' => $pesanan->pelanggan->nama ?? $pesanan->alamatPengiriman->nama_penerima ?? 'Pelanggan',
                'last_name'  => '',
                'email'      => $pesanan->pelanggan->email,
                'phone'      => $pesanan->pelanggan->no_telepon ?? $pesanan->alamatPengiriman->nomor_telepon,
                'billing_address' => [
                    'first_name' => $pesanan->alamatPengiriman->nama_penerima ?? $pesanan->pelanggan->nama ?? 'Pelanggan',
                    'last_name' => '',
                    'phone' => $pesanan->alamatPengiriman->nomor_telepon ?? $pesanan->pelanggan->no_telepon,
                    'address' => $pesanan->alamatPengiriman->alamat_lengkap,
                    'city' => $pesanan->alamatPengiriman->kota,
                    'postal_code' => $pesanan->alamatPengiriman->kode_pos,
                    'country_code' => 'IDN'
                ],
                'shipping_address' => [
                    'first_name' => $pesanan->alamatPengiriman->nama_penerima ?? $pesanan->pelanggan->nama ?? 'Pelanggan',
                    'last_name' => '',
                    'phone' => $pesanan->alamatPengiriman->nomor_telepon ?? $pesanan->pelanggan->no_telepon,
                    'address' => $pesanan->alamatPengiriman->alamat_lengkap,
                    'city' => $pesanan->alamatPengiriman->kota,
                    'postal_code' => $pesanan->alamatPengiriman->kode_pos,
                    'country_code' => 'IDN'
                ]
            ],
            'callbacks' => [
                'finish' => route('pesanan.saya.detail', ['id' => $pesanan->id]) . '?payment_status=finish',
                'unfinish' => route('pesanan.saya.detail', ['id' => $pesanan->id]) . '?payment_status=unfinish',
                'error' => route('pesanan.saya.detail', ['id' => $pesanan->id]) . '?payment_status=error',
            ],
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s O'),
                'unit' => 'hour',
                'duration' => 1,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            Log::info('Berhasil mendapatkan Snap Token dari Midtrans', ['snap_token' => $snapToken, 'order_id' => $pesanan->id]);
            $pesanan->snap_token = $snapToken;
            $pesanan->payment_gateway_name = 'midtrans';
            $pesanan->save();
            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Token Exception: ' . $e->getMessage(), ['params' => $params, 'order_id' => $pesanan->id]);
            return null;
        }
    }

    public function handleNotification($payload)
    {
        Log::info('Menerima notifikasi Midtrans', ['payload' => $payload]);

        // Directly use $payload. Ensure your controller passes $request->all() as $payload.
        $orderId     = $payload['order_id'] ?? null;
        $statusCode  = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        // Validate that essential data for signature check is present
        if (is_null($orderId) || is_null($statusCode) || is_null($grossAmount) || is_null($signatureKey)) {
            Log::error('Midtrans Notification: Data tidak lengkap untuk validasi signature.', $payload);
            return ['status' => 'error', 'message' => 'Invalid notification data. Missing required fields for signature validation.'];
        }

        $serverKey = config('services.midtrans.server_key');
        // Ensure grossAmount is formatted exactly as Midtrans sends it (e.g., "10000.00") for the hash
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::warning('Midtrans signature mismatch', [
                'order_id' => $orderId,
                'expected' => $expectedSignature,
                'received' => $signatureKey,
            ]);
            return ['status' => 'error', 'message' => 'Invalid signature'];
        }

        DB::beginTransaction();
        try {
            Log::info('Signature valid, memproses notifikasi Midtrans', ['order_id' => $orderId]);

            // Use data directly from $payload instead of new Notification()
            $transactionStatus = $payload['transaction_status'] ?? null;
            $paymentType       = $payload['payment_type'] ?? null;
            // $orderIdMidtrans is the same as $orderId from the payload used for signature
            $orderIdMidtrans   = $orderId;
            $fraudStatus       = $payload['fraud_status'] ?? null;
            // $transactionId     = $payload['transaction_id'] ?? null; // Also available if needed

            Log::info('Data notifikasi Midtrans dari payload:', [
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'order_id_midtrans' => $orderIdMidtrans,
                'fraud_status' => $fraudStatus
            ]);

            $nomorPesananAsli = explode('_', $orderId)[0]; // Use $orderId which comes from $payload['order_id']
            $pesanan = Pesanan::where('nomor_pesanan', $nomorPesananAsli)->first();
            if (!$pesanan) {
                Log::warning('Midtrans Notification: Pesanan tidak ditemukan', [
                    'order_id_midtrans' => $orderIdMidtrans,
                    'nomor_pesanan_asli' => $nomorPesananAsli
                ]);
                DB::rollBack();
                return ['status' => 'error', 'message' => 'Pesanan tidak ditemukan.'];
            }

            Log::info('Pesanan ditemukan', ['id' => $pesanan->id, 'status_pesanan' => $pesanan->status_pesanan]);

            // Store the raw payload as the gateway response
            $pesanan->payment_gateway_response = json_encode($payload);

            // Hindari proses ganda jika status sudah final
            if (in_array($pesanan->status_pesanan, [
                Pesanan::STATUS_PEMBAYARAN_BERHASIL_GATEWAY,
                'diproses',
                'selesai',
                'dikirim',
                'dibatalkan'
            ])) {
                if ($pesanan->status_pesanan == Pesanan::STATUS_PEMBAYARAN_BERHASIL_GATEWAY && $transactionStatus == 'settlement') {
                    Log::info('Pesanan sudah settlement sebelumnya, tidak diproses ulang.', [
                        'order_id' => $pesanan->id,
                        'current_status' => $pesanan->status_pesanan,
                        'midtrans_status' => $transactionStatus
                    ]);
                } else {
                    Log::info('Midtrans Notification: Pesanan sudah dalam status final atau sudah diproses.', [
                        'order_id' => $pesanan->id,
                        'current_status' => $pesanan->status_pesanan,
                        'midtrans_status' => $transactionStatus
                    ]);
                    DB::commit();
                    return ['status' => 'ok', 'message' => 'Pesanan sudah diproses sebelumnya.'];
                }
            }

            Log::info('Memproses update status pesanan berdasarkan notifikasi Midtrans', [
                'order_id' => $pesanan->id,
                'transaction_status' => $transactionStatus
            ]);

            // Update status pesanan sesuai status Midtrans
            if ($transactionStatus == 'settlement' || ($transactionStatus == 'capture' && $fraudStatus == 'accept')) {
                $pesanan->status_pesanan = 'diproses';
                $pesanan->waktu_pembayaran_gateway = now();
                $notifJudul = 'Pembayaran Berhasil';
                $notifPesan = "Pembayaran untuk pesanan #{$pesanan->nomor_pesanan} telah berhasil dan pesanan akan segera diproses.";
                $notifTipe = 'pembayaran_berhasil';
                Log::info('Status pesanan diubah menjadi diproses', ['order_id' => $pesanan->id]);
            } elseif ($transactionStatus == 'pending') {
                $pesanan->status_pesanan = 'menunggu_pembayaran';
                $notifJudul = 'Pembayaran Pending';
                $notifPesan = "Pembayaran untuk pesanan #{$pesanan->nomor_pesanan} sedang menunggu. Segera selesaikan pembayaran Anda.";
                $notifTipe = 'pembayaran_pending_gateway';
                Log::info('Status pesanan diubah menjadi menunggu_pembayaran', ['order_id' => $pesanan->id]);
            } elseif ($transactionStatus == 'capture' && $fraudStatus == 'challenge') {
                $pesanan->status_pesanan = 'verifikasi';
                $notifJudul = 'Pembayaran Perlu Verifikasi';
                $notifPesan = "Pembayaran untuk pesanan #{$pesanan->nomor_pesanan} sedang diverifikasi oleh bank.";
                $notifTipe = 'pembayaran_challenge';
                Log::info('Status pesanan diubah menjadi verifikasi', ['order_id' => $pesanan->id]);
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                $pesanan->status_pesanan = 'gagal';
                if (!in_array($pesanan->status_pesanan, ['dibatalkan'])) {
                    foreach ($pesanan->detailPesanan as $item) {
                        if ($item->produk) {
                            $item->produk->stok += $item->jumlah;
                            $item->produk->save();
                        }
                    }
                }
                $notifJudul = 'Pembayaran Gagal';
                $notifPesan = "Pembayaran untuk pesanan #{$pesanan->nomor_pesanan} gagal atau dibatalkan.";
                if ($transactionStatus == 'expire') {
                    $notifPesan = "Pembayaran untuk pesanan #{$pesanan->nomor_pesanan} telah kedaluwarsa.";
                }
                $notifTipe = 'pembayaran_gagal';
                Log::info('Status pesanan diubah menjadi gagal', ['order_id' => $pesanan->id]);
            } else {
                // Status lain, tidak di-handle
                $notifJudul = null;
                $notifPesan = null;
                $notifTipe = null;
                Log::info('Status pesanan tidak diubah, status Midtrans tidak di-handle', [
                    'order_id' => $pesanan->id,
                    'transaction_status' => $transactionStatus
                ]);
            }

            // Ambil nama bank jika payment_type adalah bank_transfer
            if ($paymentType === 'bank_transfer' && !empty($payload['va_numbers'][0]['bank'])) {
                $namaBank = strtoupper($payload['va_numbers'][0]['bank']);
                $pesanan->metode_pembayaran = $namaBank;
            } elseif ($paymentType === 'echannel' && !empty($payload['bank'])) {
                // Untuk Mandiri e-channel
                $pesanan->metode_pembayaran = strtoupper($payload['bank']);
            } elseif ($paymentType === 'permata') {
                $pesanan->metode_pembayaran = 'PERMATA';
            } else {
                $pesanan->metode_pembayaran = $paymentType;
            }

            $pesanan->save();
            Log::info('Pesanan berhasil disimpan setelah update status', ['order_id' => $pesanan->id, 'status_pesanan' => $pesanan->status_pesanan]);

            if (!empty($notifJudul)) {
                Notifikasi::create([
                    'id_pelanggan' => $pesanan->id_pelanggan,
                    'tipe_notifikasi' => $notifTipe,
                    'judul' => $notifJudul,
                    'pesan' => $notifPesan,
                    'link_aksi' => route('pesanan.saya.detail', ['id' => $pesanan->id]),
                    'id_pesanan_terkait' => $pesanan->id,
                ]);
                Log::info('Notifikasi berhasil dibuat untuk pesanan', ['order_id' => $pesanan->id, 'tipe_notifikasi' => $notifTipe]);
            }

            DB::commit();
            Log::info('Midtrans Notification Handled Successfully', [
                'order_id' => $pesanan->id,
                'new_status' => $pesanan->status_pesanan,
                'payment_type' => $paymentType
            ]);
            return ['status' => 'ok', 'message' => 'Notifikasi berhasil diproses.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Notification Exception: ' . $e->getMessage(), [
                'order_id_midtrans' => $orderId ?? 'N/A',
                'payload' => $payload
            ]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
