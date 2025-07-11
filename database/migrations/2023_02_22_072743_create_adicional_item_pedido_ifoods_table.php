<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdicionalItemPedidoIfoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('adicional_item_pedido_ifoods', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('item_pedido_id')->unsigned();
            $table->foreign('item_pedido_id')->references('id')
            ->on('item_pedido_ifoods')->onDelete('cascade');

            $table->string('nome', 100);
            $table->string('unidade', 10);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('total', 10, 2);
            
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
        Schema::dropIfExists('adicional_item_pedido_ifoods');
    }
}
