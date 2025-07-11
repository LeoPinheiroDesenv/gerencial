<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('numero_sequencial')->deafult(0);

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('frete_id')->nullable()->unsigned();
            $table->foreign('frete_id')->references('id')->on('fretes')->onDelete('cascade');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->timestamp('data_registro')->useCurrent();
            $table->date('data_entrega')->nullable();
            $table->decimal('valor_total', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);
            $table->text('observacao');
            $table->string('estado', 20);
            $table->integer('sequencia_cce');

            $table->integer('NfNumero')->default(0);
            $table->integer('nSerie')->default(0);
            $table->string('chave',48);
            $table->string('path_xml',51);

            $table->integer('pedido_ecommerce_id')->default(0);
            $table->integer('pedido_nuvemshop_id')->default(0);
            $table->integer('pedido_mercado_livre_id')->default(0);

            $table->string('bandeira_cartao', 2)->default('99');
            $table->string('cnpj_cartao', 18)->default('');
            $table->string('cAut_cartao', 20)->default('');
            $table->string('descricao_pag_outros', 80)->default('');
            $table->timestamp('data_emissao')->nullable();

            $table->boolean('troca')->default(0);
            $table->decimal('credito_troca', 10,2)->default(0);

            $table->date('data_retroativa')->nullable();
            $table->date('data_saida')->nullable();
            $table->integer('vendedor_id')->nullable();

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->boolean('contigencia')->default(0);
            $table->boolean('reenvio_contigencia')->default(0);
            $table->text('signed_xml')->nullable();
            $table->string('recibo', 30)->nullable();

            // alter table vendas add column bandeira_cartao varchar(2) default '99';
            // alter table vendas add column recibo varchar(30) default null;
            // alter table vendas add column cnpj_cartao varchar(18) default '';
            // alter table vendas add column cAut_cartao varchar(20) default '';
            // alter table vendas add column descricao_pag_outros varchar(80) default '';
            // alter table vendas add column acrescimo decimal(10, 2) default 0;
            // alter table vendas add column data_entrega date default null;

            // alter table vendas add column pedido_mercado_livre_id integer default 0;
            // alter table vendas add column pedido_nuvemshop_id integer default 0;
            // alter table vendas add column nSerie integer default 1;
            // alter table vendas add column data_emissao timestamp default CURRENT_TIMESTAMP;

            // alter table vendas add column troca boolean default 0;
            // alter table vendas add column credito_troca decimal(10,2) default 0;
            // alter table vendas add column data_retroativa date default null;
            // alter table vendas add column data_saida date default null;
            // alter table vendas add column numero_sequencial integer default 0;
            // alter table vendas add column vendedor_id integer default null;

            // alter table vendas add column filial_id integer default null;
            // alter table vendas add column venda_caixa_id integer default null;
            

            // alter table vendas add column contigencia boolean default null;
            // alter table vendas add column reenvio_contigencia boolean default null;
            // alter table vendas add column signed_xml text default null;
            // alter table vendas add column recibo varchar(30) default null;


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
        Schema::dropIfExists('vendas');
    }
}
