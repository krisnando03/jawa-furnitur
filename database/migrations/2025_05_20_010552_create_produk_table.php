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
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori') // Foreign key ke tabel kategori
                ->constrained('kategori')
                ->onDelete('cascade'); // Jika kategori dihapus, produk terkait juga terhapus
            $table->string('nama_produk');
            $table->string('slug')->unique();
            $table->text('deskripsi_singkat')->nullable();
            $table->longText('deskripsi_lengkap')->nullable();
            $table->decimal('harga', 15, 2); // Sesuaikan presisi jika perlu (misal: 10,0 untuk tanpa desimal)
            $table->integer('stok')->default(0);
            $table->string('gambar_produk')->nullable(); // Path atau nama file gambar
            $table->string('warna')->nullable(); // Bisa juga JSON atau tabel terpisah jika lebih kompleks
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
