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
        Schema::create('item_uso_consumos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('uso_consumo_id')->unsigned();
            $table->foreign('uso_consumo_id')->references('id')->on('uso_consumos')->onDelete('cascade');

            $table->integer('produto_id')->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos');

            $table->decimal('quantidade', 10,3);
            $table->decimal('valor_unitario', 16,7);
            $table->decimal('sub_total', 16,7);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_uso_consumos');
    }
};
