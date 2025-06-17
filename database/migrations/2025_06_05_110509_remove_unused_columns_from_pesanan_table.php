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
            $table->dropColumn([
                'bukti_pembayaran',
                'waktu_bukti_diunggah',
                'tanggal_pembayaran',
                // tambahkan kolom lain yang ingin dihapus
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('bukti_pembayaran')->nullable();
            $table->timestamp('waktu_bukti_diunggah')->nullable();
            $table->timestamp('tanggal_pembayaran')->nullable();
        });
    }
};
