<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemessaNfeFaturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remessa_nfe_faturas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('remessa_id')->unsigned();
            $table->foreign('remessa_id')->references('id')->on('remessa_nves')
            ->onDelete('cascade');

            $table->string('tipo_pagamento', 30);
            $table->decimal('valor', 16,7);
            $table->date('data_vencimento');

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
        Schema::dropIfExists('remessa_nfe_faturas');
    }
}
