<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KendaraanPengirim extends Model
{
    protected $table = 'kendaraan_pengirim';
    protected $fillable = [
        'type',
        'plate_number',
        'driver_name',
        'driver_phone',
        'status'
    ];

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_kendaraan_pengirim');
    }
}
