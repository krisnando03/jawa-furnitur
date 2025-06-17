<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pesanan; // Import model Pesanan
use Illuminate\Support\Facades\Log;

class UpdateStatusPesananDikirim extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pesanan:dikirim {nomor_resi : Nomor resi pengiriman}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ubah status pesanan menjadi "dikirim" dengan nomor resi.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $nomorResi = $this->argument('nomor_resi');

        // 1. Cari pesanan dengan status "diproses" (Anda mungkin perlu menyesuaikan kondisi ini jika ada kolom lain)
        $pesanan = Pesanan::where('status_pesanan', 'diproses')->first();

        if ($pesanan) {
            // 2. Jika pesanan ditemukan, ubah status menjadi "dikirim" dan simpan nomor resi
            $pesanan->status_pesanan = 'dikirim';
            $pesanan->nomor_resi = $nomorResi; // Simpan nomor resi
            $pesanan->tanggal_pengiriman = now(); // Tambahkan tanggal pengiriman saat ini

            $pesanan->save();

            Log::info("Status pesanan {$pesanan->nomor_pesanan} diubah menjadi 'dikirim' dengan nomor resi: {$nomorResi}.");
            $this->info("Status pesanan {$pesanan->nomor_pesanan} berhasil diubah menjadi 'dikirim' dengan nomor resi: {$nomorResi}.");
        } else {
            Log::warning("Tidak ada pesanan dengan status 'diproses' yang ditemukan.");
            $this->warn("Tidak ada pesanan dengan status 'diproses' yang ditemukan.");
        }

        return 0;
    }
}
