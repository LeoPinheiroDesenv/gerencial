<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendaCaixasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venda_caixas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('cliente_id')->nullable()->unsigned();
            $table->foreign('cliente_id')->references('id')->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->timestamp('data_registro')->useCurrent();
            $table->decimal('valor_total', 16,7);
            $table->decimal('dinheiro_recebido', 10,2);
            $table->decimal('troco', 10,2);
            $table->decimal('desconto', 10,2);
            $table->decimal('valor_cashback', 10,2);
            $table->decimal('acrescimo', 10,2);

            $table->string('forma_pagamento', 20);
            $table->string('tipo_pagamento', 2);
            
            $table->string('estado', 20);
            $table->integer('NFcNumero')->default(0);
            $table->string('chave', 48);
            $table->string('path_xml', 48);

            $table->string('nome', 50);
            $table->string('cpf', 18);
            $table->string('observacao', 150);
            $table->integer('pedido_delivery_id');
            $table->integer('pedido_ifood_id')->nullable();

            $table->string('tipo_pagamento_1', 20)->default('');
            $table->decimal('valor_pagamento_1', 10,2)->default(0);

            $table->string('tipo_pagamento_2', 20)->default('');
            $table->decimal('valor_pagamento_2', 10,2)->default(0);

            $table->string('tipo_pagamento_3', 20)->default('');
            $table->decimal('valor_pagamento_3', 10,2)->default(0);

            $table->text('qr_code_base64');

            $table->string('bandeira_cartao', 2)->default('99');
            $table->string('cnpj_cartao', 18)->default('');
            $table->string('cAut_cartao', 20)->default('');
            $table->string('descricao_pag_outros', 80)->default('');

            $table->boolean('rascunho')->default(false);
            $table->boolean('consignado')->default(false);
            $table->boolean('pdv_java')->default(false);
            $table->boolean('retorno_estoque')->default(false);
            $table->boolean('troca')->default(0);
            $table->integer('prevenda_nivel')->default(0);
            $table->decimal('credito_troca', 10,2)->default(0);

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');
            
            $table->integer('numero_sequencial')->deafult(0);

            $table->boolean('contigencia')->default(0);
            $table->boolean('reenvio_contigencia')->default(0);
            $table->timestamp('data_emissao')->nullable();
            $table->text('signed_xml')->nullable();
            $table->text('recibo')->nullable();

            $table->integer('vendedor_id')->nullable();

            // alter table venda_caixas add column bandeira_cartao varchar(2) default '99';
            // alter table venda_caixas add column cnpj_cartao varchar(18) default '';
            // alter table venda_caixas add column cAut_cartao varchar(20) default '';
            // alter table venda_caixas add column descricao_pag_outros varchar(80) default '';
            // alter table venda_caixas add column rascunho boolean default false;
            // alter table venda_caixas add column consignado boolean default false;
            // alter table venda_caixas add column pdv_java boolean default false;
            // alter table venda_caixas add column retorno_estoque boolean default false;
            // alter table venda_caixas add column troca boolean default false;
            // alter table venda_caixas add column credito_troca decimal(10,2) default 0;
            // alter table venda_caixas add column valor_cashback decimal(10,2) default 0;
            // alter table venda_caixas add column prevenda_nivel integer default 0;
            // alter table venda_caixas add column pedido_ifood_id integer default null;
            // alter table venda_caixas add column filial_id integer default null;
            
            // alter table venda_caixas add column numero_sequencial integer default 0;

            // alter table venda_caixas add column contigencia boolean default null;
            // alter table venda_caixas add column reenvio_contigencia boolean default null;
            // alter table venda_caixas add column data_emissao timestamp default CURRENT_TIMESTAMP;
            
            // alter table venda_caixas add column signed_xml text default null;
            // alter table venda_caixas add column recibo text default null;

            // alter table venda_caixas add column vendedor_id integer default null;
            
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
        Schema::dropIfExists('venda_caixas');
    }
}
