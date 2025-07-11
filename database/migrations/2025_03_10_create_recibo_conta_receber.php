<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('recibo_conta_rec', function (Blueprint $table) {
            // Usando id() (que normalmente cria BIGINT, mas se você deseja INT,
            // pode usar: $table->increments('id');)
            $table->increments('id');
            $table->integer('conta_receber_id')->unsigned();
            $table->integer('empresa_id')->unsigned();
            $table->integer('filial_id')->unsigned()->nullable();
            $table->date('data_pagamento');
            $table->string('cliente');
            $table->string('documento');
            $table->string('endereco');
            $table->string('telefone')->nullable();
            $table->decimal('valor_pago', 15, 2);
            $table->string('valor_extenso');
            $table->string('forma_pagamento');
            $table->text('observacao')->nullable();
            $table->string('referencia');
            $table->timestamps();

            // Restrições de chave estrangeira
            $table->foreign('conta_receber_id')->references('id')->on('conta_recebers')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('recibo_conta_rec');
    }
};
