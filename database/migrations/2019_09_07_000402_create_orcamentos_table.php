<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrcamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->nullable()->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('frete_id')->nullable()->unsigned();
            $table->foreign('frete_id')->references('id')->on('fretes')->onDelete('cascade');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->decimal('valor_total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);
            $table->string('observacao');

            $table->string('estado', 20);
            $table->boolean('email_enviado');
            $table->date('validade');
            $table->date('data_entrega')->nullable();
            $table->integer('venda_id');

            $table->date('data_retroativa')->nullable();

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');
            $table->integer('vendedor_id')->nullable();
            $table->boolean('ecommerce')->nullable();

            $table->integer('numero_sequencial')->deafult(0);
            
            // alter table orcamentos add column data_entrega date default null;
            // alter table orcamentos add column data_retroativa date default null;
            // alter table orcamentos add column filial_id integer default null;
            // alter table orcamentos add column vendedor_id integer default null;
            // alter table orcamentos add column ecommerce boolean default 0;
            
            // alter table orcamentos add column numero_sequencial integer default 0;
            
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
        Schema::dropIfExists('orcamentos');
    }
}
