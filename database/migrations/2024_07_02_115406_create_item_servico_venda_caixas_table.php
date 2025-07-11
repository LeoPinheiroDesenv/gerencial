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
        Schema::create('item_servico_venda_caixas', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('venda_caixa_id')->unsigned();
            $table->foreign('venda_caixa_id')->references('id')->on('venda_caixas')->onDelete('cascade');

            $table->integer('servico_id')->unsigned();
            $table->foreign('servico_id')->references('id')->on('servicos');

            $table->decimal('quantidade', 16,7);
            $table->decimal('valor', 16, 7);
            $table->decimal('sub_total', 16, 7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_servico_venda_caixas');
    }
};
