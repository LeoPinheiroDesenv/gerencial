<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImpressaoPedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('impressao_pedidos', function (Blueprint $table) {
            $table->id();

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('impressora_id');
            $table->integer('produto_id');
            $table->integer('pedido_id');

            $table->decimal('quantidade_item', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->enum('tabela', ['pedidos', 'delivery'])->default('pedidos');
            
            $table->boolean('status')->deafult(0);
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
        Schema::dropIfExists('impressao_pedidos');
    }
}
