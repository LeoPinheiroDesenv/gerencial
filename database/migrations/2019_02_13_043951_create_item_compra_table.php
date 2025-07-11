<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemCompraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_compras', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('compra_id')->unsigned();
            $table->foreign('compra_id')->references('id')->on('compras');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 16,7);
            $table->decimal('valor_unitario', 16,7);
            $table->string('unidade_compra', 10);

            $table->date('validade')->nullable();

            $table->string('cfop_entrada', 4)->default('');
            $table->string('codigo_siad', 10)->default('');


            $table->string('nDI', 30)->nullable();
            $table->date('dDI')->nullable();
            $table->integer('cidade_desembarque_id')->nullable();
            $table->date('dDesemb')->nullable();
            $table->string('tpViaTransp', 2)->nullable();
            $table->decimal('vAFRMM', 12, 2)->nullable();
            $table->string('tpIntermedio', 2)->nullable();
            $table->string('documento', 18)->nullable();
            $table->string('UFTerceiro', 2)->nullable();
            $table->string('cExportador', 30)->nullable();

            $table->string('nAdicao', 10)->nullable();
            $table->string('cFabricante', 20)->nullable();

            // alter table item_compras modify column quantidade decimal(16,7);

            // alter table item_compras add column nDI varchar(30) default null;
            // alter table item_compras add column dDI date default null;
            // alter table item_compras add column dDesemb date default null;
            // alter table item_compras add column tpViaTransp varchar(2) default null;
            // alter table item_compras add column vAFRMM decimal(12,2) default null;
            // alter table item_compras add column tpIntermedio varchar(2) default null;
            // alter table item_compras add column documento varchar(18) default null;
            // alter table item_compras add column UFTerceiro varchar(2) default null;
            // alter table item_compras add column cExportador varchar(30) default null;
            // alter table item_compras add column nAdicao varchar(10) default null;
            // alter table item_compras add column cFabricante varchar(20) default null;
            // alter table item_compras add column cidade_desembarque_id integer default null;


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
        Schema::dropIfExists('item_compras');
    }
}
