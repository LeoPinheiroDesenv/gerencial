<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApuracaoSalarioEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apuracao_salario_eventos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('apuracao_id')->unsigned();
            $table->foreign('apuracao_id')->references('id')->on('apuracao_salarios')
            ->onDelete('cascade');

            $table->integer('evento_id')->unsigned();
            $table->foreign('evento_id')->references('id')->on('evento_salarios')
            ->onDelete('cascade');

            $table->decimal('valor');
            $table->enum('metodo', ['informado', 'fixo']);
            $table->enum('condicao', ['soma', 'diminui']);
            $table->string('nome', 100);

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
        Schema::dropIfExists('apuracao_salario_eventos');
    }
}
