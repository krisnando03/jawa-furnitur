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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama_voucher');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe_diskon', ['persen', 'tetap']);
            $table->decimal('nilai_diskon', 15, 2);
            $table->decimal('min_pembelian', 15, 2)->default(0);
            $table->decimal('maks_diskon', 15, 2)->nullable()->comment('Untuk tipe persen, batas maksimal diskon');
            $table->integer('kuota')->nullable();
            $table->integer('digunakan')->default(0);
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_berakhir');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
