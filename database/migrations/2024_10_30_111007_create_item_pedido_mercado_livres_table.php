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
        Schema::create('item_pedido_mercado_livres', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pedido_id')->unsigned();
            $table->foreign('pedido_id')->references('id')
            ->on('pedido_mercado_livres')->onDelete('cascade');

            $table->integer('produto_id')->nullable()->unsigned();
            $table->foreign('produto_id')->references('id')
            ->on('produtos')->onDelete('cascade');

            $table->string('item_id', 20);
            $table->string('item_nome', 100);
            $table->string('condicao', 20);
            $table->string('variacao_id', 20)->nullable();

            $table->decimal('quantidade', 8,2);
            $table->decimal('valor_unitario', 12,2);
            $table->decimal('sub_total', 12,2);
            $table->decimal('taxa_venda', 12,2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pedido_mercado_livres');
    }
};
