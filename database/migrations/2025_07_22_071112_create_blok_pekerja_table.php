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
        Schema::create('blok_pekerja', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('blok_id');
        $table->unsignedBigInteger('pekerja_id');
        $table->timestamps();

        $table->foreign('blok_id')->references('id_blok')->on('blok')->onDelete('cascade');
        $table->foreign('pekerja_id')->references('id_pekerja')->on('pekerja')->onDelete('cascade');
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blok_pekerja');
    }
};
