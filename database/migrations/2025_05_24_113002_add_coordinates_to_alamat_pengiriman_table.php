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
            $table->decimal('latitude', 10, 7)->nullable()->after('kode_pos'); // Presisi 10, skala 7 cocok untuk latitude
            $table->decimal('longitude', 11, 7)->nullable()->after('latitude'); // Presisi 11, skala 7 cocok untuk longitude

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alamat_pengiriman', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
