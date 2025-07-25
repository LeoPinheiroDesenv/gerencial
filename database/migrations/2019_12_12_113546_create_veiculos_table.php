<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVeiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->string('placa', 8);
            $table->string('uf', 2);
            $table->string('cor', 10);
            $table->string('marca', 20);
            $table->string('modelo', 20);
            $table->string('rntrc', 12);

            $table->string('taf', 15);
            $table->string('renavam', 12);
            $table->string('numero_registro_estadual', 30);

            $table->string('tipo', 2);
            $table->string('tipo_carroceira', 2);
            $table->string('tipo_rodado', 2);

            $table->string('tara', 10);
            $table->string('capacidade', 10);

            $table->string('proprietario_documento', 20);
            $table->string('proprietario_nome', 40);
            $table->string('proprietario_ie', 13);
            $table->string('proprietario_uf', 2);
            $table->integer('proprietario_tp');

            // alter table veiculos add column taf varchar(15) default '';
            // alter table veiculos add column renavam varchar(12) default '';
            // alter table veiculos add column numero_registro_estadual varchar(30) default '';
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
        Schema::dropIfExists('veiculos');
    }
}
