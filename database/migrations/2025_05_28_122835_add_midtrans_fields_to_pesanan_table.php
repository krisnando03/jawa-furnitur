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
            $table->string('snap_token')->nullable()->after('bukti_pembayaran');
            $table->string('payment_gateway_name')->nullable()->after('snap_token');
            // metode_pembayaran akan diisi dengan tipe spesifik dari gateway, misal 'gopay', 'credit_card'
            // Jika sebelumnya metode_pembayaran hanya untuk 'cod' atau 'bank_transfer', pastikan cukup panjang
            // Jika metode_pembayaran sudah ada dan cukup, tidak perlu diubah.
            // $table->string('metode_pembayaran', 100)->nullable()->change(); // Contoh jika perlu diubah
            $table->text('payment_gateway_response')->nullable()->after('payment_gateway_name');
            $table->timestamp('waktu_pembayaran_gateway')->nullable()->after('payment_gateway_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['snap_token', 'payment_gateway_name', 'payment_gateway_response', 'waktu_pembayaran_gateway']);
        });
    }
};
