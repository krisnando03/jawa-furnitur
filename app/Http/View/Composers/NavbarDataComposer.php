<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Models\Pesan;
use App\Models\Keranjang;
use App\Models\Pesanan;
use App\Models\Notifikasi;

class NavbarDataComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $unreadMessagesCount = 0;
        $jumlahItemKeranjang = 0;
        $unfinishedOrdersCount = 0;
        $unreadNotificationsCount = 0; // Tambahkan ini

        if (Session::has('pelanggan')) {
            $pelangganSession = Session::get('pelanggan');
            $idPelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

            if ($idPelanggan) {
                // Hitung pesan belum dibaca oleh pelanggan (dikirim oleh admin)
                $unreadMessagesCount = Pesan::where('id_pelanggan', $idPelanggan)
                    ->where('pengirim_adalah_admin', true)
                    ->where('sudah_dibaca_oleh_pelanggan', false)
                    ->count();

                // Hitung jumlah item di keranjang
                $jumlahItemKeranjang = Keranjang::where('id_pelanggan', $idPelanggan)->sum('jumlah');

                // Hitung pesanan yang belum selesai
                $unfinishedOrdersCount = Pesanan::where('id_pelanggan', $idPelanggan)
                    ->whereNotIn('status_pesanan', ['selesai', 'dibatalkan'])
                    ->count();

                // Hitung notifikasi belum dibaca
                $unreadNotificationsCount = Notifikasi::where('id_pelanggan', $idPelanggan)
                    ->where('sudah_dibaca', false)
                    ->count();
            }
        }

        $view->with(compact('unreadMessagesCount', 'jumlahItemKeranjang', 'unfinishedOrdersCount', 'unreadNotificationsCount')); // Tambahkan variabel baru
    }
}
