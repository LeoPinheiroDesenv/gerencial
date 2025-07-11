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
        Schema::create('item_venda_balcaos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('venda_balcao_id')->unsigned();
            $table->foreign('venda_balcao_id')->references('id')->on('venda_balcaos')->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 16,7);
            $table->decimal('valor', 16,7);
            $table->decimal('sub_total', 16,7);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_venda_balcaos');
    }
};
