<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubCategoriaContaFinanceirasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sub_categoria_conta_financeiras', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('categoria_id')->unsigned();
            $table->foreign('categoria_id')->references('id')
            ->on('categoria_conta_financeiras')->onDelete('cascade');

            $table->string('nome', 60);
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
        Schema::dropIfExists('sub_categoria_conta_financeiras');
    }
}
