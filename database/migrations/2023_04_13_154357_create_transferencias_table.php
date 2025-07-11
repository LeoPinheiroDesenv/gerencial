<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferencias', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('filial_saida_id')->unsigned()->nullable();
            $table->foreign('filial_saida_id')->references('id')->on('filials')->onDelete('cascade');

            $table->integer('filial_entrada_id')->unsigned()->nullable();
            $table->foreign('filial_entrada_id')->references('id')->on('filials')->onDelete('cascade');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->string('observacao', 255)->nullable();

            $table->string('chave', 48)->nullable();
            $table->integer('numero_nfe')->nullable();

            $table->integer('natureza_id')->nullable()->unsigned();
            $table->foreign('natureza_id')->references('id')->on('natureza_operacaos');

            $table->integer('transportadora_id')->nullable()->unsigned();
            $table->foreign('transportadora_id')->references('id')->on('transportadoras')
            ->onDelete('cascade');

            $table->timestamp('data_emissao')->nullable();
            $table->integer('finNFe')->nullable();
            $table->integer('tpNF')->default(1);
            $table->integer('sequencia_cce')->default(0);
            $table->enum('estado', ['novo', 'rejeitado', 'cancelado', 'aprovado'])->default('novo');
            $table->text('signed_xml')->nullable();
            $table->string('recibo', 30)->nullable();
            // alter table transferencias add column observacao varchar(255) default null;

            // alter table transferencias add column chave varchar(48) default null;
            // alter table transferencias add column numero_nfe integer default null;
            // alter table transferencias add column natureza_id integer default null;
            // alter table transferencias add column transportadora_id integer default null;
            // alter table transferencias add column data_emissao timestamp default CURRENT_TIMESTAMP;

            // alter table transferencias add column finNFe integer default null;
            // alter table transferencias add column tpNF integer default 0;
            // alter table transferencias add column estado enum('novo', 'rejeitado', 'cancelado', 'aprovado') default 'novo';
            // alter table transferencias add column signed_xml text default null;
            // alter table transferencias add column recibo varchar(30) default null;
            // alter table transferencias add column sequencia_cce integer default 0;

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
        Schema::dropIfExists('transferencias');
    }
}
