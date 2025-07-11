<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuncionarioEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('funcionario_eventos', function (Blueprint $table) {
            $table->id();

            $table->integer('evento_id')->unsigned();
            $table->foreign('evento_id')->references('id')->on('evento_salarios')
            ->onDelete('cascade');

            $table->integer('funcionario_id')->unsigned();
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')
            ->onDelete('cascade');

            $table->enum('condicao', ['soma', 'diminui']);
            $table->enum('metodo', ['informado', 'fixo']);

            $table->decimal('valor', 10, 2);
            $table->boolean('ativo')->default(1);

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
        Schema::dropIfExists('funcionario_eventos');
    }
}
