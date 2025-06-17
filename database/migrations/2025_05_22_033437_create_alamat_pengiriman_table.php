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
        Schema::create('alamat_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pelanggan')->constrained(table: 'tb_pelanggan', column: 'id_pelanggan')->onDelete('cascade');
            $table->string('nama_penerima', 255);
            $table->string('nomor_telepon', 25);
            $table->string('label_alamat')->nullable()->comment('Contoh: Rumah, Kantor'); // <-- PASTIKAN BARIS INI ADA
            $table->text('alamat_lengkap');
            $table->string('provinsi', 100);
            $table->string('kota', 100);
            $table->string('kode_pos', 10);
            $table->boolean('is_utama')->default(false); // <-- TAMBAHKAN ATAU PASTIKAN BARIS INI ADA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alamat_pengiriman');
    }
};
