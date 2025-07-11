<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemPedidoIfoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_pedido_ifoods', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pedido_id')->unsigned();
            $table->foreign('pedido_id')->references('id')
            ->on('pedido_ifoods')->onDelete('cascade');

            $table->integer('produto_id');

            $table->string('nome_produto', 150);
            $table->string('image_url', 200);
            $table->string('unidade', 40);

            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('quantidade', 10, 2);
            $table->decimal('total', 10, 2);
            $table->decimal('valor_adicional', 10, 2);

            $table->string('observacao', 200);
            $table->timestamps();

            // alter table item_pedido_ifoods add column produto_id integer;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_pedido_ifoods');
    }
}
