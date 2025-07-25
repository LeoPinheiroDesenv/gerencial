<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCtesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ctes', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->text('chave_nfe');

            $table->integer('remetente_id')->unsigned();
            $table->foreign('remetente_id')->references('id')
            ->on('clientes');

            $table->integer('destinatario_id')->unsigned();
            $table->foreign('destinatario_id')->references('id')
            ->on('clientes');

            $table->integer('recebedor_id')->unsigned()->nullable();
            $table->foreign('recebedor_id')->references('id')
            ->on('clientes');

            // alter table ctes add column recebedor_id integer default null;
            // alter table ctes add column expedidor_id integer default null;

            $table->integer('expedidor_id')->unsigned()->nullable();
            $table->foreign('expedidor_id')->references('id')
            ->on('clientes');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->integer('natureza_id')->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('tomador');
            // Indica o "papel" do tomador: 0-Remetente; 1-Expedidor; 2-Recebedor; 3-Destinatário

            $table->integer('municipio_envio')->unsigned();
            $table->foreign('municipio_envio')->references('id')
            ->on('cidades');

            $table->integer('municipio_inicio')->unsigned();
            $table->foreign('municipio_inicio')->references('id')
            ->on('cidades');

            $table->integer('municipio_fim')->unsigned();
            $table->foreign('municipio_fim')->references('id')
            ->on('cidades');

            $table->string('logradouro_tomador', 80)->nullable();
            $table->string('numero_tomador', 20)->nullable();
            $table->string('bairro_tomador', 40)->nullable();
            $table->string('cep_tomador', 10)->nullable();

            $table->integer('municipio_tomador')->nullable()->unsigned();
            $table->foreign('municipio_tomador')->references('id')
            ->on('cidades');

            $table->decimal('valor_transporte', 10, 2);
            $table->decimal('valor_receber', 10, 2);
            $table->decimal('valor_carga', 10, 2);
            
            $table->string('produto_predominante', 30);
            $table->date('data_previsata_entrega');

            $table->string('observacao');
            $table->integer('sequencia_cce');
            $table->integer('cte_numero')->default(0);
            $table->string('chave', 48);
            $table->string('path_xml', 51);
            $table->string('estado', 20);
            $table->timestamp('data_registro')->useCurrent();

            $table->boolean('retira');
            $table->string('detalhes_retira', 100);
            $table->string('modal', 2);

            $table->integer('veiculo_id')->unsigned();
            $table->foreign('veiculo_id')->references('id')
            ->on('veiculos');

            $table->string('tpDoc', 2);
            $table->string('descOutros', 100);
            $table->integer('nDoc');
            $table->decimal('vDocFisc', 10, 2);

            $table->integer('globalizado');
            $table->integer('tipo_servico')->default(0);
            $table->string('cst', 3)->default('00');
            $table->decimal('perc_icms', 5, 2)->default(0);
            $table->decimal('pRedBC', 5, 2)->default(0);
            $table->boolean('status_pagamento')->default(0);

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');
            $table->string('recibo', 30)->nullable();

            // alter table ctes add column pRedBC decimal(5,2) default 0;
                
            // alter table ctes drop column chave_nfe;
            // alter table ctes add column chave_nfe text;
            // alter table ctes add column status_pagamento boolean default 0;
            // alter table ctes add column filial_id integer default null;

            // alter table ctes add column recibo varchar(30) default null;
            // alter table ctes add column tipo_servico integer default null;
            
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
        Schema::dropIfExists('ctes');
    }
}
