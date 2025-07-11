<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuprimentoCaixasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suprimento_caixas', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('usuario_id')->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios');

            $table->string('observacao', 50);
            $table->decimal('valor', 10,2);
            $table->string('tipo', 2)->nullable();
            $table->integer('conta_id')->nullable();

            // alter table suprimento_caixas add column tipo varchar(2) default null;
            // alter table suprimento_caixas add column conta_id integer default null;
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suprimento_caixas');
    }
}
