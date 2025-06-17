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
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pesanan')->constrained('pesanan')->onDelete('cascade');
            $table->foreignId('id_produk')->constrained('produk')->onDelete('restrict'); // Asumsi tabel produk sudah ada
            $table->string('nama_produk_saat_order');
            $table->decimal('harga_satuan_saat_order', 15, 2);
            $table->integer('jumlah');
            $table->decimal('subtotal', 15, 2)->comment('harga_satuan_saat_order * jumlah');
            $table->timestamps();

            // Composite unique key to prevent duplicate product entries per order (optional)
            // $table->unique(['id_pesanan', 'id_produk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
