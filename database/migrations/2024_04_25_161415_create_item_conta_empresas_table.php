<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_conta_empresas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('conta_id')->unsigned();
            $table->foreign('conta_id')->references('id')->on('conta_empresas')
            ->onDelete('cascade');

            $table->string('descricao', 150)->nullable();
            $table->integer('caixa_id')->nullable();
            $table->string('tipo_pagamento', 2);
            $table->decimal('valor', 16, 2)->nullable();
            $table->decimal('saldo_atual', 16, 2)->nullable();
            $table->enum('tipo', ['entrada', 'saida']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_conta_empresas');
    }
};
