<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('fornecedor_id')->unsigned();
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors')
            ->onDelete('cascade');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')
            ->onDelete('cascade');

            $table->integer('categoria_conta_id')->nullable();

            $table->string('observacao');
            $table->string('xml_path', 48);
            $table->string('chave', 44);
            $table->string('nf', 20);
            $table->integer('numero_emissao');
            $table->string('estado', 10);
            $table->string('lote', 20)->nullable();
            
            $table->decimal('valor', 16,7);
            $table->decimal('desconto', 10,2);
            $table->decimal('acrescimo', 10,2);
            $table->integer('sequencia_cce');

            $table->string('placa', 9);
            $table->string('uf', 2);
            $table->decimal('valor_frete', 10, 2);
            $table->integer('tipo');
            $table->integer('qtdVolumes');
            $table->string('numeracaoVolumes', 20);
            $table->string('especie', 20);
            $table->decimal('peso_liquido', 8, 3);
            $table->decimal('peso_bruto', 8, 3);

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->string('tipo_pagamento', 2)->default('');
            $table->integer('natureza_id')->default(0);

            $table->timestamp('data_emissao')->nullable();

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');
            $table->integer('numero_sequencial')->deafult(0);
            $table->boolean('xml_importado')->deafult(0);
            $table->boolean('importado_manifesto')->deafult(0);
            
            // alter table compras add column sequencia_cce integer default 0;

            // alter table compras add column placa varchar(9) default '';
            // alter table compras add column uf varchar(2) default '';
            // alter table compras add column valor_frete decimal(10, 2) default 0;
            // alter table compras add column tipo integer default 0;
            // alter table compras add column qtdVolumes integer default 0;
            // alter table compras add column numeracaoVolumes varchar(20) default '';
            // alter table compras add column especie varchar(20) default '';
            // alter table compras add column peso_liquido decimal(8, 3) default 0;
            // alter table compras add column peso_bruto decimal(8, 3) default 0;
            // alter table compras add column acrescimo decimal(10, 2) default 0;

            // alter table compras add column tipo_pagamento varchar(2) default '';
            // alter table compras add column natureza_id integer default 0;

            // alter table compras add column data_emissao timestamp default CURRENT_TIMESTAMP;
            // alter table compras add column filial_id integer default null;
            // alter table compras add column numero_sequencial integer default 0;
            // alter table compras add column lote varchar(20) default null;

            // alter table compras add column xml_importado boolean default 0;
            // alter table compras add column importado_manifesto boolean default 0;
            // alter table compras add column categoria_conta_id integer default null;
            
            
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
        Schema::dropIfExists('compras');
    }
}
