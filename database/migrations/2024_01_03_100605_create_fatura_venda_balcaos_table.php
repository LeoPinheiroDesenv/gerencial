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
        Schema::create('fatura_venda_balcaos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('venda_balcao_id')->unsigned();
            $table->foreign('venda_balcao_id')->references('id')->on('venda_balcaos')->onDelete('cascade');

            $table->decimal('valor', 16, 7);
            $table->string('forma_pagamento', 20);
            $table->date('data_vencimento');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fatura_venda_balcaos');
    }
};
