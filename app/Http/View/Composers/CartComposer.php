<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Models\Keranjang; // Pastikan model Keranjang di-import

class CartComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $jumlahItemKeranjang = 0;
        $pelangganSession = Session::get('pelanggan');

        if ($pelangganSession) {
            $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);
            if ($id_pelanggan) {
                // Hitung jumlah unik produk di keranjang, atau total kuantitas
                // Untuk saat ini, kita hitung jumlah baris (unik produk)
                $jumlahItemKeranjang = Keranjang::where('id_pelanggan', $id_pelanggan)->count();
                // Jika ingin total kuantitas semua produk:
                // $jumlahItemKeranjang = Keranjang::where('id_pelanggan', $id_pelanggan)->sum('jumlah');
            }
        }
        $view->with('jumlahItemKeranjang', $jumlahItemKeranjang);
    }
}
