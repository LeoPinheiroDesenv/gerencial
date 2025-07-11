<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApuracaoSalariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apuracao_salarios', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('funcionario_id')->unsigned();
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')
            ->onDelete('cascade');

            $table->string('mes', 20);
            $table->integer('ano');
            $table->decimal('valor_final', 10, 2);

            $table->string('forma_pagamento', 30);
            $table->string('observacao', 100);

            $table->integer('conta_pagar_id')->default(0);
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
        Schema::dropIfExists('apuracao_salarios');
    }
}
