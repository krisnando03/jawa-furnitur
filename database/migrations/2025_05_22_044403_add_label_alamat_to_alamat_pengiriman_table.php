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
        Schema::table('alamat_pengiriman', function (Blueprint $table) {
            $table->string('label_alamat')->nullable()->after('nomor_telepon'); // ->after() bersifat opsional, untuk menentukan posisi kolom

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alamat_pengiriman', function (Blueprint $table) {
            $table->dropColumn('label_alamat');
        });
    }
};
