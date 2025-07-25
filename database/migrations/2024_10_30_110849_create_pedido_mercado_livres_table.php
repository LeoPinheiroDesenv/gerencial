<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedido_mercado_livres', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('clientes')->onDelete('cascade');

            $table->bigInteger('_id');
            $table->string('tipo_pagamento', 50);
            $table->string('status', 20);

            $table->decimal('total', 10, 2);
            $table->decimal('valor_entrega', 10, 2);

            $table->string('nickname', 50);
            $table->bigInteger('seller_id');

            $table->string('entrega_id', 20)->nullable();
            $table->string('data_pedido', 20);

            $table->string('comentario', 200)->nullable();
            $table->integer('venda_id')->nullable();

            $table->string('rua_entrega', 100)->nullable();
            $table->string('numero_entrega', 10)->nullable();
            $table->string('cep_entrega', 10)->nullable();
            $table->string('bairro_entrega', 50)->nullable();
            $table->string('cidade_entrega', 100)->nullable();
            $table->string('comentario_entrega', 200)->nullable();

            $table->string('codigo_rastreamento', 30)->nullable();

            $table->string('cliente_nome', 50)->nullable();
            $table->string('cliente_documento', 20)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_mercado_livres');
    }
};
