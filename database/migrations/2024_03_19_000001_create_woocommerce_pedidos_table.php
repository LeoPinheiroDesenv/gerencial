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
        Schema::create('woocommerce_pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('woocommerce_id');
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->string('cliente_nome');
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefone')->nullable();
            $table->string('status');
            $table->decimal('total', 10, 2);
            $table->string('forma_pagamento')->nullable();
            $table->string('forma_envio')->nullable();
            $table->string('endereco_entrega')->nullable();
            $table->string('bairro_entrega')->nullable();
            $table->string('cidade_entrega')->nullable();
            $table->string('estado_entrega')->nullable();
            $table->string('cep_entrega')->nullable();
            $table->text('observacao')->nullable();
            $table->timestamp('data_pedido');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas');
            $table->foreign('cliente_id')->references('id')->on('clientes');
            $table->unique(['empresa_id', 'woocommerce_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woocommerce_pedidos');
    }
}; 