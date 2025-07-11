<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->increments('id');

            $table->text('texto');
            $table->integer('accessos_forcar_assinar')->default(0);
            $table->boolean('usar_certificado')->default(0);

            // alter table contratos add column accessos_forcar_assinar integer default 0;
            // alter table contratos add column usar_certificado boolean default 0;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contratos');
    }
}
