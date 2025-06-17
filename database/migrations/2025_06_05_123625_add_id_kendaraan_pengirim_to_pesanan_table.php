<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kendaraan_pengirim')->nullable()->after('id_alamat_pengiriman');
            $table->foreign('id_kendaraan_pengirim')->references('id')->on('kendaraan_pengirim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropForeign(['id_kendaraan_pengirim']);
            $table->dropColumn('id_kendaraan_pengirim');
        });
    }
};
