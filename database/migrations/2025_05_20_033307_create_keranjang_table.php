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
        Schema::create('keranjang', function (Blueprint $table) {
            $table->id(); // Primary key (id_keranjang)
            $table->unsignedBigInteger('id_pelanggan');
            $table->unsignedBigInteger('id_produk');
            $table->integer('jumlah');
            $table->decimal('harga_saat_dibeli', 15, 2); // Harga satuan produk saat ditambahkan
            $table->decimal('subtotal_harga', 15, 2);    // jumlah * harga_saat_dibeli
            $table->decimal('berat_satuan_saat_dibeli', 8, 2)->default(0.00); // Berat satuan produk saat ditambahkan
            $table->decimal('subtotal_berat', 8, 2)->default(0.00);      // jumlah * berat_satuan_saat_dibeli
            $table->timestamps();

            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('tb_pelanggan')->onDelete('cascade');
            $table->foreign('id_produk')->references('id')->on('produk')->onDelete('cascade');

            $table->unique(['id_pelanggan', 'id_produk']); // Setiap pelanggan hanya bisa punya 1 baris untuk produk yang sama
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
