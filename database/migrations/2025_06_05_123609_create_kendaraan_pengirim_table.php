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
        Schema::create('kendaraan_pengirim', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Jenis kendaraan (mobil, motor, dll)
            $table->string('plate_number');
            $table->string('driver_name');
            $table->string('driver_phone');
            $table->enum('status', ['available', 'on_delivery'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendaraan_pengirim');
    }
};
