<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnderecoDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('endereco_deliveries', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cliente_id')->unsigned();
            $table->foreign('cliente_id')->references('id')->on('cliente_deliveries')->onDelete('cascade');

            $table->integer('cidade_id')->unsigned()->nulable();
            $table->foreign('cidade_id')->references('id')->on('cidade_deliveries')->onDelete('cascade');

            $table->string('rua', 50);
            $table->string('bairro', 30);
            $table->integer('bairro_id');
            $table->string('numero', 10);
            $table->string('referencia', 30);
            $table->string('latitude', 10);
            $table->string('longitude', 10);
            $table->string('cep', 10);
            
            $table->enum('tipo', ['casa', 'trabalho']);
            $table->boolean('principal');

            $table->boolean('padrao')->default(0);
            $table->timestamps();

            // alter table endereco_deliveries add column cep varchar(10) default '';
            // alter table endereco_deliveries add column padrao boolean default 0;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('endereco_deliveries');
    }
}
