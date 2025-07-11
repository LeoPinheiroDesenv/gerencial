<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContaFinanceirasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conta_financeiras', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('empresa_id')->unsigned();
            $table->foreign('empresa_id')->references('id')
            ->on('empresas')->onDelete('cascade');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')
            ->on('categoria_conta_financeiras')->onDelete('cascade');

            $table->integer('sub_categoria_id')->unsigned()->nullable();
            $table->foreign('sub_categoria_id')->references('id')
            ->on('sub_categoria_conta_financeiras')->onDelete('cascade');

            $table->string('nome', 60);

            $table->decimal('saldo_inicial', 20,7);

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
        Schema::dropIfExists('conta_financeiras');
    }
}
