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
        Schema::create('jual', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('jumlah_pembelian')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jual');
    }
};
