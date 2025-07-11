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
        Schema::create('venda_balcaos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->string('codigo_venda', 8);
            $table->integer('numero_sequencial');

            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->string('cliente_nome', 50)->nullable();

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->decimal('valor_total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);
            $table->string('observacao');
            $table->boolean('estado')->default(0);

            $table->string('bandeira_cartao', 2)->default('99');
            $table->string('cnpj_cartao', 18)->default('');
            $table->string('cAut_cartao', 20)->default('');
            $table->string('descricao_pag_outros', 80)->default('');

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->integer('venda_id')->nullable();
            $table->enum('tipo_venda', ['nfe', 'nfce']);

            $table->string('placa', 9);
            $table->string('uf', 2);
            $table->decimal('valor', 10, 2);
            $table->integer('tipo');
            $table->integer('quantidade_volumes');
            $table->string('numeracao_volumes', 20);
            $table->string('especie', 20);
            $table->decimal('peso_liquido',8, 3);
            $table->decimal('peso_bruto',8, 3);
            $table->integer('vendedor_id')->nullable();

            // alter table venda_balcaos add column cliente_nome varchar(50) default null;
            // alter table venda_balcaos add column vendedor_id integer default null;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venda_balcaos');
    }
};
