<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoMesasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_mesas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            
            $table->decimal('valor_total', 10,2)->nullable();
            $table->string('forma_pagamento', 20)->nullable();
            $table->string('observacao', 50)->nullable();

            $table->enum('estado', ['fechado', 'aberto', 'concluido', 'recusado']);
            $table->string('uid', 40);
            $table->string('nome_cliente');
            $table->string('telefone_cliente');
            $table->integer('mesa_id');

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
        Schema::dropIfExists('pedido_mesas');
    }
}
