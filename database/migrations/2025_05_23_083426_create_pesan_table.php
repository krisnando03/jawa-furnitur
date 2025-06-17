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
        Schema::create('pesan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pelanggan')->comment('ID pelanggan yang terlibat dalam percakapan')->constrained(table: 'tb_pelanggan', column: 'id_pelanggan')->onDelete('cascade');
            $table->boolean('pengirim_adalah_admin')->default(false)->comment('TRUE jika pengirim adalah admin, FALSE jika pelanggan');
            $table->text('isi_pesan');
            $table->foreignId('id_produk_konteks')->nullable()->comment('Konteks produk jika pesan terkait produk tertentu')->constrained('produk')->onDelete('set null');
            $table->string('nomor_pesanan_konteks')->nullable()->comment('Konteks nomor pesanan jika pesan terkait pesanan tertentu');
            $table->boolean('sudah_dibaca_oleh_pelanggan')->default(false)->comment('Status dibaca untuk pesan yang dikirim oleh admin');
            $table->boolean('sudah_dibaca_oleh_admin')->default(false)->comment('Status dibaca untuk pesan yang dikirim oleh pelanggan');
            $table->timestamps(); // created_at dan updated_at

            $table->index('id_pelanggan');
            $table->index(['id_pelanggan', 'created_at']); // Untuk mengambil history chat per pelanggan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesan');
    }
};
