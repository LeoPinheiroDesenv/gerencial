<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePedidoIfoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_ifoods', function (Blueprint $table) {
            $table->increments('id');

            $table->string('status', 10);
            $table->string('pedido_id', 50);
            $table->string('data_pedido', 30);

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('tipo_pedido', 40)->nullable();

            $table->string('endereco', 255)->nullable();
            $table->string('bairro', 50)->nullable();
            $table->string('cep', 10)->nullable();

            $table->string('nome_cliente', 100)->nullable();
            $table->string('id_cliente', 100)->nullable();
            $table->string('telefone_cliente', 100)->nullable();

            $table->decimal('valor_produtos', 10, 2)->nullable();
            $table->decimal('valor_entrega', 10, 2)->nullable();
            $table->decimal('valor_total', 10, 2)->nullable();
            $table->decimal('taxas_adicionais', 10, 2)->nullable();

            $table->string('cpf_na_nota', 20)->nullable();

            $table->boolean('status_leitura')->default(0);
            $table->boolean('status_driver')->default(0);

            // alter table pedido_ifoods add column status_leitura boolean default 0;
            // alter table pedido_ifoods add column status_driver boolean default 0;

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
        Schema::dropIfExists('pedido_ifoods');
    }
}
