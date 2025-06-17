<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'tb_pelanggan';

    protected $primaryKey = 'id_pelanggan';

    protected $fillable = [
        'nama',
        'alamat',
        'no_telepon',
        'email',
        'username',
        'password',
        'profile_photo_path'
    ];

    public $timestamps = false;
}
