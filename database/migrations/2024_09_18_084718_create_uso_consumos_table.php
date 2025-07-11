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
        Schema::create('uso_consumos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')
            ->onDelete('cascade');

            $table->integer('funcionario_id')->nullable()->unsigned();
            $table->foreign('funcionario_id')->references('id')->on('funcionarios');

            $table->string('observacao', 255)->nullable();
            $table->decimal('valor_total', 12,2);
            $table->decimal('desconto', 12,2)->nullable();
            $table->decimal('acrescimo', 12,2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uso_consumos');
    }
};
