<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemDevolucaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_devolucaos', function (Blueprint $table) {
            $table->increments('id');

            $table->string('cod', 10);
            $table->string('nome', 150);
            $table->string('ncm', 10);
            $table->string('cfop', 10);
            $table->string('codBarras', 13);
            $table->decimal('valor_unit', 14, 4);
            $table->decimal('sub_total', 14, 4);
            $table->decimal('quantidade', 10, 4);
            $table->boolean('item_parcial');    
            $table->string('unidade_medida', 8);   

            $table->string('cst_csosn', 3);   
            $table->string('cst_pis', 3);   
            $table->string('cst_cofins', 3);   
            $table->string('cst_ipi', 3);   
            $table->decimal('perc_icms');   
            $table->decimal('perc_pis');   
            $table->decimal('perc_cofins');   
            $table->decimal('perc_ipi');  
            $table->decimal('pRedBC', 10, 4);

            $table->decimal('vBCSTRet', 8, 2)->default(0);
            $table->decimal('vFrete', 8, 2)->default(0);

            $table->integer('devolucao_id')->unsigned();
            $table->foreign('devolucao_id')->references('id')->on('devolucaos')
            ->onDelete('cascade');

            $table->decimal('modBCST', 8, 2);
            $table->decimal('vBCST', 8, 2);
            $table->decimal('pICMSST', 8, 2);
            $table->decimal('vICMSST', 8, 2);
            $table->decimal('pMVAST', 8, 2);

            $table->integer('orig');
            $table->decimal('pST', 10, 2);
            $table->decimal('vICMSSubstituto', 10, 2);
            $table->decimal('vICMSSTRet', 10, 2);

            $table->string('codigo_anp', 10)->default('');
            $table->string('descricao_anp', 95)->default('');
            $table->decimal('perc_glp', 5,2)->default(0);
            $table->decimal('perc_gnn', 5,2)->default(0);
            $table->decimal('perc_gni', 5,2)->default(0);
            $table->string('uf_cons', 2)->default('');
            $table->decimal('valor_partida', 10, 2)->default(0);
            $table->string('unidade_tributavel', 4)->default('');
            $table->decimal('quantidade_tributavel', 10, 2)->default(0);

            $table->decimal('qBCMonoRet', 10, 4)->default(0);
            $table->decimal('adRemICMSRet', 10, 3)->default(0);
            $table->decimal('vICMSMonoRet', 10, 3)->default(0);

            $table->decimal('vbc_manual', 10, 4);
            $table->decimal('vicms_manual', 10, 4);
            $table->decimal('vpis_manual', 10, 4);
            $table->decimal('vcofins_manual', 10, 4);
            $table->decimal('vipi_manual', 10, 4);
            $table->string('cest', 10)->nullable();
            $table->string('cBenef',10)->default('');
            
            // $table->string('cProdANP', 10)->nullable();
            // $table->string('cProdANP', 10)->nullable();
            // $table->string('descANP', 95)->nullable();
            // $table->decimal('pGLP', 5,2)->nullable();
            // $table->decimal('pGNn', 5,2)->nullable();
            // $table->decimal('pGNi', 5,2)->nullable();
            // $table->decimal('vPart', 10, 2)->nullable();
            // $table->string('UFCons', 2)->nullable();

            // alter table item_devolucaos add column cProdANP varchar(10) default null;
            // alter table item_devolucaos add column cBenef varchar(10) default null;
            // alter table item_devolucaos add column descANP varchar(95) default null;
            // alter table item_devolucaos add column pGLP decimal(5,2) default null;
            // alter table item_devolucaos add column pGNn decimal(5,2) default null;
            // alter table item_devolucaos add column pGNi decimal(5,2) default null;
            // alter table item_devolucaos add column vPart decimal(10,2) default null;
            // alter table item_devolucaos add column UFCons varchar(2) default null;


            // alter table item_devolucaos add column vBCSTRet decimal(8,2) default 0;
            // alter table item_devolucaos add column vFrete decimal(8,2) default 0;

            // alter table item_devolucaos add column modBCST decimal(8,2) default 0;
            // alter table item_devolucaos add column vBCST decimal(8,2) default 0;
            // alter table item_devolucaos add column pMVAST decimal(8,2) default 0;
            // alter table item_devolucaos add column pICMSST decimal(8,2) default 0;
            // alter table item_devolucaos add column vICMSST decimal(8,2) default 0;

            // alter table item_devolucaos add column orig integer default 0;
            // alter table item_devolucaos add column pST decimal(10,2) default 0;
            // alter table item_devolucaos add column vICMSSubstituto decimal(10,2) default 0;
            // alter table item_devolucaos add column vICMSSTRet decimal(10,2) default 0;

            // alter table item_devolucaos add column perc_glp decimal(5,2) default 0;
            // alter table item_devolucaos add column perc_gnn decimal(5,2) default 0;
            // alter table item_devolucaos add column perc_gni decimal(5,2) default 0;
            // alter table item_devolucaos add column codigo_anp varchar(10) default '';
            // alter table item_devolucaos add column descricao_anp varchar(95) default '';
            // alter table item_devolucaos add column uf_cons varchar(2) default '';
            // alter table item_devolucaos add column valor_partida decimal(10, 2) default 0;

            // alter table item_devolucaos add column unidade_tributavel varchar(4) default '';
            // alter table item_devolucaos add column quantidade_tributavel decimal(10, 2) default 0;

            // alter table item_devolucaos add column vbc_manual decimal(10, 4) default 0;
            // alter table item_devolucaos add column vicms_manual decimal(10, 4) default 0;
            // alter table item_devolucaos add column vpis_manual decimal(10, 4) default 0;
            // alter table item_devolucaos add column vcofins_manual decimal(10, 4) default 0;
            // alter table item_devolucaos add column vipi_manual decimal(10, 4) default 0;

            // alter table item_devolucaos add column cest varchar(10) default null;
            // alter table item_devolucaos add column qBCMonoRet decimal(10,4) default 0;
            // alter table item_devolucaos add column adRemICMSRet decimal(10,3) default 0;
            // alter table item_devolucaos add column vICMSMonoRet decimal(10,3) default 0;

            // alter table item_devolucaos modify column adRemICMSRet decimal(10,4) default 0;
            // alter table item_devolucaos add column sub_total decimal(18,7) default 0;

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
        Schema::dropIfExists('item_devolucaos');
    }
}
