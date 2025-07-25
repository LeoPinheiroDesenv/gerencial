<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlteracaoEstoquesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alteracao_estoques', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->integer('produto_id')->nullable()->unsigned();
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');
            $table->integer('usuario_id')->nullable()->unsigned();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->decimal('quantidade', 15, 3);
            $table->string('observacao', 200);
            $table->string('tipo', 15);
            $table->string('motivo', 25)->nullable();

            // alter table alteracao_estoques add column motivo varchar(25) default null;
            // alter table alteracao_estoques modify column quantidade decimal(15,3);
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
        Schema::dropIfExists('alteracao_estoques');
    }
}
