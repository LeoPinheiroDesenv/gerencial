<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPedidoMesaPizzasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pedido_mesa_pizzas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('item_pedido')->unsigned();
            $table->foreign('item_pedido')->references('id')->on('item_pedido_mesas')->onDelete('cascade');
            
            $table->integer('sabor_id')->unsigned();
            $table->foreign('sabor_id')->references('id')->on('produto_deliveries')->onDelete('cascade');

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
        Schema::dropIfExists('item_pedido_mesa_pizzas');
    }
}
