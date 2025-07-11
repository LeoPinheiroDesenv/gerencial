<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdemServicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordem_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')
            ->on('clientes')->onDelete('cascade');

            $table->integer('numero_sequencial')->deafult(0);

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')
            ->on('usuarios')->onDelete('cascade');

            $table->string('estado', 2)->default("pd");

            $table->string('descricao');
            $table->string('forma_pagamento', 30)->nullable();
            $table->decimal('valor', 10,2)->default(0);
            $table->timestamp('data_registro')->useCurrent();
            $table->date('data_prevista_finalizacao')->default("1981-01-01");
            $table->integer('NfNumero')->default(0);

            $table->decimal('desconto', 10,2)->nullable();
            $table->decimal('acrescimo', 10,2)->nullable();
            $table->string('observacao', 255)->nullable();

            $table->integer('venda_id')->default(0);
            $table->integer('nfse_id')->default(0);

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');


            $table->string('modelo', 150)->nullable();
            $table->string('filtro', 150)->nullable();
            $table->string('entrada_agua')->boolean();
            $table->string('potencia_motor', 10)->nullable();
            $table->string('ligar_motor_para', 10)->nullable();
            $table->string('registro_cascata')->boolean();
            $table->string('outros_servicos_cm', 255)->nullable();
            $table->integer('vendedor_id')->nullable();
            $table->string('nao_esquecer', 255)->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_entrega')->nullable();

            $table->string('rua_servico', 100)->nullable();
            $table->string('numero_servico', 20)->nullable();
            $table->string('bairro_servico', 50)->nullable();
            $table->string('complemento_servico', 150)->nullable();
            $table->integer('cidade_servico')->nullable();
            $table->string('cep_servico', 9)->nullable();

            // alter table ordem_servicos add column modelo varchar(150) default null;
            // alter table ordem_servicos add column filtro varchar(150) default null;
            // alter table ordem_servicos add column potencia_motor varchar(10) default null;
            // alter table ordem_servicos add column ligar_motor_para varchar(10) default null;
            // alter table ordem_servicos add column outros_servicos_cm varchar(255) default null;
            // alter table ordem_servicos add column nao_esquecer varchar(255) default null;
            // alter table ordem_servicos add column entrada_agua boolean default null;
            // alter table ordem_servicos add column registro_cascata boolean default null;
            // alter table ordem_servicos add column vendedor_id integer default null;
            // alter table ordem_servicos add column data_inicio date default null;
            // alter table ordem_servicos add column data_entrega date default null;

            // alter table ordem_servicos add column rua_servico varchar(100) default null;
            // alter table ordem_servicos add column numero_servico varchar(20) default null;
            // alter table ordem_servicos add column bairro_servico varchar(50) default null;
            // alter table ordem_servicos add column cep_servico varchar(9) default null;
            // alter table ordem_servicos add column complemento_servico varchar(150) default null;
            // alter table ordem_servicos add column cidade_servico integer default null;

            // alter table ordem_servicos add column desconto decimal(10,2) default null;
            // alter table ordem_servicos add column acrescimo decimal(10,2) default null;
            // alter table ordem_servicos add column observacao varchar(100) default null;
            // alter table ordem_servicos modify column forma_pagamento varchar(30) default null;

            // alter table ordem_servicos add column venda_id integer default 0;
            // alter table ordem_servicos add column nfse_id integer default 0;
            // alter table ordem_servicos add column numero_sequencial integer default 0;
            // alter table ordem_servicos add column conta_receber_id integer default 0;
            // alter table ordem_servicos add column filial_id integer default null;
            // alter table ordem_servicos modify column observacao varchar(255) default null;

            
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
        Schema::dropIfExists('ordem_servicos');
    }
}
