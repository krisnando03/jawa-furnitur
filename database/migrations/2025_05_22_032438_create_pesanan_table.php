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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id(); // Primary key untuk tabel pesanan
            $table->foreignId('id_pelanggan')->constrained(table: 'tb_pelanggan', column: 'id_pelanggan')->onDelete('cascade');
            $table->string('nomor_pesanan')->unique();
            $table->foreignId('id_alamat_pengiriman')->constrained('alamat_pengiriman')->onDelete('restrict');
            $table->foreignId('id_voucher')->nullable()->constrained('vouchers')->onDelete('set null');
            $table->decimal('subtotal_produk', 15, 2);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('ongkos_kirim', 15, 2)->default(0);
            $table->decimal('total_pembayaran', 15, 2);
            $table->string('status_pesanan')->default('menunggu_pembayaran')->comment('Contoh: menunggu_pembayaran, diproses, dikirim, selesai, dibatalkan');
            $table->text('catatan_pembeli')->nullable();
            $table->string('metode_pembayaran')->nullable();
            $table->string('bukti_pembayaran')->nullable()->comment('Path ke file bukti');
            $table->dateTime('tanggal_pesanan');
            $table->dateTime('tanggal_pembayaran')->nullable();
            $table->dateTime('tanggal_pengiriman')->nullable();
            $table->string('nomor_resi')->nullable();
            $table->timestamps();

            $table->index('status_pesanan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
