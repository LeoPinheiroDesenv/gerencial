<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagamentoPedidoIfoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagamento_pedido_ifoods', function (Blueprint $table) {
            $table->id();

            $table->integer('pedido_id')->unsigned();
            $table->foreign('pedido_id')->references('id')
            ->on('pedido_ifoods')->onDelete('cascade');

            $table->string('forma_pagamento', 30)->nullable();
            $table->string('tipo_pagamento', 30)->nullable();
            $table->string('bandeira_cartao', 20)->nullable();
            $table->decimal('valor', 10, 2);

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
        Schema::dropIfExists('pagamento_pedido_ifoods');
    }
}
