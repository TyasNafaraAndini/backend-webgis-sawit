<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlatPekerjaTable extends Migration
{
    public function up()
    {
        Schema::create('alat_pekerja', function (Blueprint $table) {
            $table->unsignedBigInteger('alat_id');
            $table->unsignedBigInteger('pekerja_id');

            $table->foreign('alat_id')->references('id_alat')->on('alat')->onDelete('cascade');
            $table->foreign('pekerja_id')->references('id_pekerja')->on('pekerja')->onDelete('cascade');

            $table->primary(['alat_id', 'pekerja_id']); // Composite primary key
        });
    }

    public function down()
    {
        Schema::dropIfExists('alat_pekerja');
    }
}
