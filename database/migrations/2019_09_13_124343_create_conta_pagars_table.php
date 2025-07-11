<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContaPagarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conta_pagars', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('compra_id')->nullable()->unsigned();
            $table->foreign('compra_id')->references('id')->on('compras')
            ->onDelete('cascade');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')->on('categoria_contas')
            ->onDelete('cascade');

            $table->integer('fornecedor_id')->default(0);

            $table->string('referencia');
            $table->decimal('valor_integral', 16,7);
            $table->decimal('valor_pago', 16,7)->default(0);
            $table->timestamp('date_register')->useCurrent();
            $table->date('data_vencimento');
            $table->timestamp('data_pagamento')->nullable();
            $table->boolean('status')->default(false);
            $table->string('tipo_pagamento', 20)->deafault('');

            $table->integer('numero_nota_fiscal')->default(0);
            $table->timestamps();

            $table->integer('filial_id')->unsigned()->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->string('observacao', 100)->default('');
            $table->boolean('estorno')->default(false);
            $table->string('motivo_estorno', 100)->nullable();

            $table->decimal('valor_inss', 10,2)->nullable();
            $table->decimal('valor_iss', 10,2)->nullable();
            $table->decimal('valor_pis', 10,2)->nullable();
            $table->decimal('valor_cofins', 10,2)->nullable();
            $table->decimal('valor_ir', 10,2)->nullable();
            $table->decimal('outras_retencoes', 10,2)->nullable();

            $table->decimal('juros', 16,7)->default(0);
            $table->decimal('multa', 16,7)->default(0);

            $table->string('observacao_baixa', 255)->nullable();
            $table->string('arquivo', 30)->nullable();

            // alter table conta_pagars add column fornecedor_id integer default 0;
            // alter table conta_pagars add column tipo_pagamento varchar(20) default '';
            
            // alter table conta_pagars add column numero_nota_fiscal integer default 0;
            // alter table conta_pagars add column filial_id integer default null;
            // alter table conta_pagars add column observacao varchar(100) default '';

            // alter table conta_pagars add column estorno boolean default false;
            // alter table conta_pagars add column motivo_estorno varchar(100) default null;

            // alter table conta_pagars modify column data_pagamento timestamp;

            // alter table conta_pagars add column valor_inss decimal(10,2) default null;
            // alter table conta_pagars add column valor_iss decimal(10,2) default null;
            // alter table conta_pagars add column valor_pis decimal(10,2) default null;
            // alter table conta_pagars add column valor_cofins decimal(10,2) default null;
            // alter table conta_pagars add column valor_ir decimal(10,2) default null;
            // alter table conta_pagars add column outras_retencoes decimal(10,2) default null;
            // alter table conta_pagars add column juros decimal(10, 4) default 0;
            // alter table conta_pagars add column multa decimal(10, 4) default 0;
            // alter table conta_pagars add column observacao_baixa varchar(255) default null;
            // alter table conta_pagars add column arquivo varchar(30) default null;

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conta_pagars');
    }
}
