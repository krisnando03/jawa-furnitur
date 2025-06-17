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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pelanggan')->constrained(table: 'tb_pelanggan', column: 'id_pelanggan')->onDelete('cascade');
            $table->string('tipe_notifikasi')->comment('Contoh: pembayaran, proses_pesanan, pengiriman, pesanan_selesai');
            $table->string('judul');
            $table->text('pesan');
            $table->string('link_aksi')->nullable()->comment('URL untuk aksi terkait notifikasi, misal link ke detail pesanan');
            $table->foreignId('id_pesanan_terkait')->nullable()->constrained('pesanan')->onDelete('set null')->comment('ID pesanan yang terkait dengan notifikasi ini');
            $table->boolean('sudah_dibaca')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps(); // created_at (kapan notifikasi dibuat) dan updated_at

            $table->index(['id_pelanggan', 'sudah_dibaca', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
