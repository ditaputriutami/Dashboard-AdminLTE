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
        Schema::create('detail_jual', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jual_id')->constrained('jual');
            $table->integer('barang_id');
            $table->foreign('barang_id')->references('id')->on('barang');
            $table->integer('qty');
            $table->integer('harga_sekarang');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_jual');
    }
};
