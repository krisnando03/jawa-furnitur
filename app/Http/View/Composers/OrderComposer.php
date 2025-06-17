<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use App\Models\Pesanan;

class OrderComposer
{
    public function compose(View $view)
    {
        $unfinishedOrdersCount = 0;

        if (Session::has('pelanggan')) {
            $pelangganSession = Session::get('pelanggan');
            $id_pelanggan = is_array($pelangganSession) ? ($pelangganSession['id_pelanggan'] ?? null) : ($pelangganSession->id_pelanggan ?? null);

            if ($id_pelanggan) {
                // Hitung pesanan yang belum selesai (misalnya, belum dibayar, diproses, dikirim)
                $unfinishedOrdersCount = Pesanan::where('id_pelanggan', $id_pelanggan)
                    ->whereIn('status_pesanan', ['menunggu_pembayaran', 'diproses', 'dikirim'])
                    ->count();
            }
        }

        $view->with('unfinishedOrdersCountGlobal', $unfinishedOrdersCount);
    }
}
